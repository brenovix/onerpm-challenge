<?php

namespace App\Services;

use App\Domain\Music\Album;
use App\Domain\Music\Artist;
use App\Repositories\AlbumRepository;
use Illuminate\Support\Facades\DB;

class AlbumService
{
    public function __construct(private AlbumRepository $albumRepository, private ArtistService $artistService)
    {
        $this->albumRepository = $albumRepository;
    }

    public function ensureExistent(Album $album): Album
    {
        if ($this->isExistingAlbum($album)) {
            return $album;
        }

        $existingAlbum = $this->findExistingAlbum($album);
        return $existingAlbum ?? $this->createNewAlbum($album);
    }

    public function addArtist(int $albumId, Artist $artist): bool
    {
        return $this->albumRepository->addArtist($albumId, $artist);
    }

    private function isExistingAlbum(Album $album): bool
    {
        return !empty($album->getId());
    }

    private function findExistingAlbum(Album $album): ?Album
    {
        $existingAlbumData = $this->albumRepository->findByTitle(
            $album->getTitle(),
            $album->getReleaseDate()->format('Y-m-d')
        );

        if ($existingAlbumData) {
            $albumArray = json_decode(json_encode($existingAlbumData), true);
            return Album::fromArray($albumArray);
        }
        return null;
    }

    private function createNewAlbum(Album $album): Album
    {
        return DB::transaction(function () use ($album) {
            $artists = array_map(fn (Artist $artist) => $this->artistService->ensureExistent($artist), $album->getArtists());
            $savedAlbumData = $this->albumRepository->save($album);
            foreach ($artists as $artist) {
                $this->albumRepository->addArtist($savedAlbumData['id'], $artist);
            }
            $savedAlbumData['artists'] = $artists;
            return Album::fromArray($savedAlbumData);
        });
    }
}
