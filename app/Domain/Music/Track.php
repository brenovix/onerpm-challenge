<?php

namespace App\Domain\Music;

use App\Domain\Entity;
use App\Domain\Music\Artist;
use App\Domain\Music\ValueObject\Duration;
use App\Domain\Music\ValueObject\ISRC;
use DateTime;

class Track extends Entity
{

    private array $artists;

    public function __construct(
        private ISRC $isrc,
        private string $title,
        private Duration $duration,
        array $artists,
        private Album $album,
        private ?string $externalUrl = null,
        private bool $brEnabled = false,
        private ?string $previewUrl = null,
        ?int $id = null
    ) {
        $this->artists = array_map(fn($artist) => $artist instanceof Artist ? $artist : new Artist($artist), $artists);
        $this->id = $id;
    }

    public function getIsrc(): ISRC
    {
        return $this->isrc;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDuration(): Duration
    {
        return $this->duration;
    }

    public function getArtists(): array
    {
        return $this->artists;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function getAlbum(): Album
    {
        return $this->album;
    }

    public function isBrEnabled(): bool
    {
        return $this->brEnabled;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function __serialize(): array
    {
        return [
            'isrc' => $this->isrc->getCode(),
            'title' => $this->title,
            'duration' => $this->duration->getSeconds(),
            'artists' => array_map(fn($artist) => $artist->getName(), $this->artists),
            'album' => $this->album->__serialize(),
            'external_url' => $this->externalUrl,
            'br_enabled' => $this->brEnabled,
            'preview_url' => $this->previewUrl,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isrc: new ISRC($data['isrc']),
            title: $data['title'],
            duration: new Duration($data['duration']),
            artists: array_map(fn($artist) => $artist instanceof Artist ? $artist : new Artist($artist), $data['artists']),
            album: $data['album'] instanceof Album ? $data['album'] : new Album(
                $data['album']['title'],
                $data['album']['cover'],
                new DateTime($data['album']['release_date']),
                $data['album']['artists'],
                $data['album']['external_url'] ?? null
            ),
            externalUrl: $data['external_url'] ?? null,
            brEnabled: $data['br_enabled'] ?? false,
            previewUrl: $data['preview_url'] ?? null,
            id: $data['id'] ?? null
        );
    }
}