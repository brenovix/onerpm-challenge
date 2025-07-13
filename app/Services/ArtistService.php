<?php

namespace App\Services;

use App\Domain\Music\Artist;
use App\Repositories\ArtistRepository;

class ArtistService
{
    protected ArtistRepository $artistRepository;

    public function __construct(ArtistRepository $artistRepository)
    {
        $this->artistRepository = $artistRepository;
    }

    public function create(Artist $artist): Artist
    {
        return Artist::fromArray($this->artistRepository->save($artist));
    }

    public function addToAlbum(int $albumId, Artist $artist): bool
    {
        return $this->artistRepository->addToAlbum($albumId, $artist);
    }

    public function addToTrack(int $trackId, Artist $artist): bool
    {
        return $this->artistRepository->addToTrack($trackId, $artist);
    }

    public function ensureExistent(Artist $artist): Artist
    {
        if (!empty($artist->getId())) {
            return $artist;
        }
        $existentArtist = $this->artistRepository->findByName($artist->getName());
        return $existentArtist ? Artist::fromArray(json_decode(json_encode($existentArtist), true)) : $this->create($artist);
    }
}