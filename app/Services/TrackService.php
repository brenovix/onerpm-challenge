<?php

namespace App\Services;

use App\Domain\Music\Artist;
use App\Domain\Music\Track;
use App\Repositories\TrackRepository;
use App\Services\Contracts\StreamingApiServiceInterface;
use Illuminate\Support\Facades\DB;

class TrackService
{
    public function __construct(
        private TrackRepository $trackRepository,
        private StreamingApiServiceInterface $streamingApiService, 
        private ArtistService $artistService,
        private AlbumService $albumService,
        private IsrcSyncService $isrcSyncService
    ) {
    }

    public function list()
    {
        return $this->trackRepository->list();
    }

    public function getDataFromStreamingService(string $isrc): ?Track
    {
        return $this->streamingApiService->searchByISRC($isrc);
    }

    public function store(Track|array $data): Track
    {
        $track = $data instanceof Track ? $data : Track::fromArray($data);
        $persistedData = $this->persist($track);
        return Track::fromArray($persistedData);
    }

    public function syncMissingIsrc(): void
    {
        $this->isrcSyncService->syncMissingIsrc();
    }

    private function persist(Track $track): array
    {
        return DB::transaction(function () use ($track) {
            $data = $track->__serialize();
            $album = $this->albumService->ensureExistent($track->getAlbum());
            $artists = array_map(function ($artist) {
                return $this->artistService->ensureExistent($artist);
            }, $track->getArtists());
            $data['album_id'] = $album->getId();
            $newTrack = $this->trackRepository->insert($data);
            foreach ($artists as $artist) {
                $this->addArtist($newTrack['id'], $artist);
            }
            $newTrack['album'] = $album;
            $newTrack['artists'] = $artists;
            return $newTrack;
        });
    }

    public function addArtist(int $trackId, Artist $artist): bool
    {
        return $this->trackRepository->addArtist($trackId, $artist);
    }
}