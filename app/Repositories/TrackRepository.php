<?php

namespace App\Repositories;

use App\Domain\Music\Album;
use App\Domain\Music\Artist;
use App\Domain\Music\Track;
use Illuminate\Support\Facades\DB;

class TrackRepository extends Repository
{
    protected string $table = 'tracks';

    public function getByIsrc(string $isrc)
    {
        return $this->query()
            ->where('isrc', $isrc)
            ->first();
    }

    public function list()
    {
        return $this->query()
            ->join('tracks_artists as TA', 'TA.track_id', '=', 'T.id')
            ->join('artists as Ar', 'Ar.id', '=', 'TA.artist_id')
            ->join('albums as Al', 'Al.id', '=', 'T.album_id')
            ->orderBy('T.title')
            ->select([
                'T.*',
                'Al.title as album_title',
                'Al.cover',
                'Al.release_date',
                DB::raw("GROUP_CONCAT(Ar.name) as artists")
            ])
            ->groupBy('T.id')->distinct()->get();
    }

    public function addArtist(int $id, Artist $artist)
    {
        $data = [
            'track_id' => $id,
            'artist_id' => $artist->getId()
        ];
        return DB::table('tracks_artists')->insert($data);
    }
}