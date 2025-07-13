<?php

namespace App\Services;

use App\Domain\Music\Album;
use App\Domain\Music\Artist;
use App\Domain\Music\Track;
use App\Domain\Music\ValueObject\Duration;
use App\Domain\Music\ValueObject\ISRC;
use App\Services\Contracts\StreamingApiServiceInterface;
use DateTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyAPIService implements StreamingApiServiceInterface
{
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id');
        $this->clientSecret = config('services.spotify.client_secret');
    }

    public function search(array $params): ?Track
    {
        $query = http_build_query($params);
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->getToken()
        ])->get(config('services.spotify.search_url') . '?' . $query);
        return $this->parseData($response->json('tracks'));
    }

    public function searchByISRC(string $isrc): ?Track
    {
        $params = [
            'type' => 'track',
            'q' => 'isrc:' . $isrc
        ];
        return $this->search($params);
    }

    private function parseData($data): ?Track
    {
        if (empty($data) || !array_key_exists('items', $data) || empty($data['items'])) {
            return null;
        }
        $item = $data['items'][0];
        $isrc = new ISRC($item['external_ids']['isrc']);
        $album = new Album(
            $item['album']['name'],
            $item['album']['images'][0]['url'],
            new DateTime($item['album']['release_date']),
            array_map(fn($artist) => $artist['name'], $item['album']['artists']),
            $item['album']['external_urls']['spotify']
        );
        $artists = array_map(fn($artist) => new Artist($artist['name']), $item['artists']);
        $duration_seconds = (int) floor($item['duration_ms'] / 1000);
        return new Track(
            $isrc,
            $item['name'],
            new Duration($duration_seconds),
            $artists,
            $album,
            $item['external_urls']['spotify'],
            in_array('BR', $item['available_markets']),
            $item['preview_url'] ?? null
        );
    }

    private function getToken()
    {
        return Cache::remember('SPOTIFY_TOKEN', config('services.spotify.token_time'), function () {
            $response = Http::asForm()->post(config('services.spotify.auth_url'), [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]);
            if ($response->failed()) $response->throw()->json();
            return $response->json('access_token');
        });
    }
}
