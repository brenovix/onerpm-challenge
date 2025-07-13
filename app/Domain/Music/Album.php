<?php

namespace App\Domain\Music;

use App\Domain\Entity;
use App\Domain\Music\Artist;
use DateTime;

class Album extends Entity
{
    private array $artists;

    public function __construct(
        private string $title,
        private string $cover,
        private DateTime $releaseDate,
        array $artists,
        private ?string $externalUrl,
        ?int $id = null
    ) {
        $this->artists = array_map(fn($artist) => $artist instanceof Artist ? $artist : new Artist($artist), $artists);
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCover(): string
    {
        return $this->cover;
    }

    public function getReleaseDate(): DateTime
    {
        return $this->releaseDate;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function getArtists(): array
    {
        return $this->artists;
    }

    public function __serialize(): array
    {
        return [
            'title' => $this->title,
            'cover' => $this->cover,
            'release_date' => $this->releaseDate->format('Y-m-d'),
            'external_url' => $this->externalUrl,
            'artists' => $this->artists,
        ];
    }
    
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            cover: $data['cover'],
            releaseDate: new DateTime($data['release_date']),
            artists: array_map(fn($artist) => $artist instanceof Artist ? $artist : new Artist($artist), $data['artists']),
            externalUrl: $data['external_url'] ?? null,
            id: $data['id'] ?? null
        );
    }
}