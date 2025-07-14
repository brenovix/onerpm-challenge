<?php

namespace Tests\Unit\Domain\Music;

use App\Domain\Music\Album;
use App\Domain\Music\Artist;
use App\Domain\Music\Track;
use App\Domain\Music\ValueObject\Duration;
use App\Domain\Music\ValueObject\ISRC;
use Tests\TestCase;

class TrackTest extends TestCase
{
    private array $trackData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trackData = [
            'id' => 1,
            'title' => 'Secret Garden',
            'isrc' => 'QMRSZ2100658',
            'album' => [
                'id' => 1,
                'title' => 'Eternal Blue',
                'cover' => 'https://i.scdn.co/image/ab67616d0000b2733e234c82f96fa4ded8e5ca47',
                'release_date' => '2021-09-16',
                'artists' => ['Spiritbox'],
                'external_url' => 'https://open.spotify.com/album/0OzpSEZ5rwwAu1JC2zRAvb'
            ],
            'artists' => ['Spiritbox'],
            'release_date' => '2021-09-16',
            'cover' => 'https://i.scdn.co/image/ab67616d0000b2733e234c82f96fa4ded8e5ca47',
            'duration' => 219,
            'br_enabled' => true,
            'preview_url' => 'https://example.com/preview.mp3',
            'external_url' => 'https://open.spotify.com/track/0OzpSEZ5rwwAu1JC2zRAvb'
        ];
    }

    public function testCanBeInstantiatedWithBasicData(): void
    {
        $track = new Track(
            isrc: new ISRC($this->trackData['isrc']),
            title: $this->trackData['title'],
            duration: new Duration($this->trackData['duration']),
            artists: $this->trackData['artists'],
            album: new Album(
                title: $this->trackData['album']['title'],
                cover: $this->trackData['album']['cover'],
                releaseDate: new \DateTime($this->trackData['album']['release_date']),
                artists: $this->trackData['album']['artists'],
                externalUrl: $this->trackData['album']['external_url']
            ),
            externalUrl: $this->trackData['external_url'],
            brEnabled: $this->trackData['br_enabled'],
            previewUrl: $this->trackData['preview_url'],
            id: $this->trackData['id']
        );

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals($this->trackData['id'], $track->getId());
        $this->assertEquals($this->trackData['title'], $track->getTitle());
        $this->assertEquals($this->trackData['isrc'], $track->getIsrc()->getCode());
        $this->assertEquals($this->trackData['duration'], $track->getDuration()->getSeconds());
        $this->assertCount(1, $track->getArtists());
        $this->assertEquals('Spiritbox', $track->getArtists()[0]->getName());
        $this->assertInstanceOf(Album::class, $track->getAlbum());
        $this->assertEquals($this->trackData['album']['title'], $track->getAlbum()->getTitle());
        $this->assertEquals($this->trackData['album']['cover'], $track->getAlbum()->getCover());
        $this->assertTrue($track->isBrEnabled());
        $this->assertEquals($this->trackData['preview_url'], $track->getPreviewUrl());
        $this->assertEquals($this->trackData['external_url'], $track->getExternalUrl());
    }


    public function testCanBeInstantiatedWithArtistObjects(): void
    {
        $artists = array_map(fn($name) => new Artist($name), $this->trackData['artists']);
        $track = new Track(
            isrc: new ISRC($this->trackData['isrc']),
            title: $this->trackData['title'],
            duration: new Duration($this->trackData['duration']),
            artists: $artists,
            album: new Album(
                title: $this->trackData['album']['title'],
                cover: $this->trackData['album']['cover'],
                releaseDate: new \DateTime($this->trackData['album']['release_date']),
                artists: array_map(fn($name) => new Artist($name), $this->trackData['album']['artists']),
                externalUrl: $this->trackData['album']['external_url']
            ),
            externalUrl: $this->trackData['external_url'],
            brEnabled: $this->trackData['br_enabled'],
            previewUrl: $this->trackData['preview_url']
        );

        $this->assertCount(1, $track->getArtists());
        $this->assertSame($artists, $track->getArtists());
    }

    public function testCanBeInstantiatedFromArray(): void
    {
        $track = Track::fromArray($this->trackData);

        $this->assertInstanceOf(Track::class, $track);
        $this->assertEquals($this->trackData['id'], $track->getId());
        $this->assertEquals($this->trackData['title'], $track->getTitle());
        $this->assertEquals($this->trackData['isrc'], $track->getIsrc()->getCode());
        $this->assertEquals($this->trackData['duration'], $track->getDuration()->getSeconds());
        $this->assertCount(1, $track->getArtists());
        $this->assertEquals('Spiritbox', $track->getArtists()[0]->getName());
        $this->assertInstanceOf(Album::class, $track->getAlbum());
        $this->assertEquals($this->trackData['album']['title'], $track->getAlbum()->getTitle());
        $this->assertEquals($this->trackData['album']['cover'], $track->getAlbum()->getCover());
        $this->assertTrue($track->isBrEnabled());
        $this->assertEquals($this->trackData['preview_url'], $track->getPreviewUrl());
        $this->assertEquals($this->trackData['external_url'], $track->getExternalUrl());
    }

    public function testCanBeSerializedToArray(): void
    {
        $track = Track::fromArray($this->trackData);
        $serialized = $track->__serialize();

        $expected = [
            'isrc' => 'QMRSZ2100658',
            'title' => 'Secret Garden',
            'duration' => 219,
            'artists' => ['Spiritbox'],
            'album' => [
                'title' => 'Eternal Blue',
                'cover' => 'https://i.scdn.co/image/ab67616d0000b2733e234c82f96fa4ded8e5ca47',
                'release_date' => '2021-09-16',
                'artists' => array_map(fn ($artist) => new Artist($artist), ['Spiritbox']),
                'external_url' => 'https://open.spotify.com/album/0OzpSEZ5rwwAu1JC2zRAvb'
            ],
            'external_url' => 'https://open.spotify.com/track/0OzpSEZ5rwwAu1JC2zRAvb',
            'br_enabled' => true,
            'preview_url' => 'https://example.com/preview.mp3'
        ];

        $this->assertEquals($expected, $serialized);
    }
}