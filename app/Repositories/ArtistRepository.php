<?php

namespace App\Repositories;

use App\Domain\Music\Artist;
use Illuminate\Support\Facades\DB;

class ArtistRepository extends Repository
{
    protected string $table = 'artists';

    public function save(Artist $artist)
    {
        return $this->insert($artist);
    }

    public function findByName(string $name)
    {
        return $this->query()->where('name', $name)->first();
    }

    public function getAll()
    {
        return $this->query()->get();
    }

    public function addToAlbum(int $albumId, Artist $artist)
    {
        $data = [
            'album_id' => $albumId,
            'artist_id' => $artist->getId()
        ];
        return DB::table('artists_albums')->insert($data);
    }

    public function addToTrack(int $trackId, Artist $artist)
    {
        $data = [
            'track_id' => $trackId,
            'artist_id' => $artist->getId()
        ];
        return DB::table('artists_tracks')->insert($data);
    }
}