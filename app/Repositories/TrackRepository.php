<?php

namespace App\Repositories;

use App\Domain\Music\Artist;
use Illuminate\Support\Facades\DB;

class TrackRepository extends Repository
{
    protected string $table = 'tracks';

    public function getByIsrc(string $isrc)
    {
        return $this->query()
            ->join('artists_tracks as AT', 'AT.track_id', '=', 'T.id')
            ->join('artists as Ar', 'Ar.id', '=', 'AT.artist_id')
            ->join('albums as Al', 'Al.id', '=', 'T.album_id')
            ->where('isrc', $isrc)
            ->select([
                'T.*',
                'Al.title as album_title',
                'Al.cover',
                'Al.release_date',
                DB::raw("JSON_ARRAYAGG(Ar.name) as artists")
            ])
            ->groupBy('T.id')
            ->first();
    }

    public function list()
    {
        return $this->query()
            ->join('artists_tracks as AT', 'AT.track_id', '=', 'T.id')
            ->join('artists as Ar', 'Ar.id', '=', 'AT.artist_id')
            ->join('albums as Al', 'Al.id', '=', 'T.album_id')
            ->orderBy('T.title')
            ->select([
                'T.*',
                'Al.title as album_title',
                'Al.cover',
                'Al.release_date',
                DB::raw("JSON_ARRAYAGG(Ar.name) as artists")
            ])
            ->groupBy('T.id')->distinct()->get();
    }

    public function addArtist(int $id, Artist $artist)
    {
        $data = [
            'track_id' => $id,
            'artist_id' => $artist->getId()
        ];
        return DB::table('artists_tracks')->insert($data);
    }
}