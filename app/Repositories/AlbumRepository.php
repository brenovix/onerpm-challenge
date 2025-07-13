<?php

namespace App\Repositories;

use App\Domain\Music\Album;
use App\Domain\Music\Artist;
use Illuminate\Support\Facades\DB;

class AlbumRepository extends Repository
{

    protected string $table = 'albums';

    public function save(Album $album)
    {
        return $this->insert($album);
    }

    public function addArtist(int $id, Artist $artist)
    {
        $data = [
            'album_id' => $id,
            'artist_id' => $artist->getId()
        ];
        return DB::table('artists_albums')->insert($data);
    }

    public function findByTitle(string $title, string $releaseDate)
    {
        return $this->query()
            ->where('title', $title)
            ->where('release_date', $releaseDate)
            ->first();
    }
}