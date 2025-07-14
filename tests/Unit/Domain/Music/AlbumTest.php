<?php

namespace Tests\Unit\Domain\Music;

use App\Domain\Music\Album;
use App\Domain\Music\Artist;
use DateTime;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    private array $albumData;
    private DateTime $releaseDate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->releaseDate = new DateTime('2023-10-27');
        $this->albumData = [
            'id' => 1,
            'title' => 'Test Album',
            'cover' => 'http://example.com/cover.jpg',
            'release_date' => $this->releaseDate->format('Y-m-d'),
            'artists' => ['Artist One', 'Artist Two'],
            'external_url' => 'http://example.com/album',
        ];
    }

    public function testCanBeInstantiatedWithBasicData(): void
    {
        $album = new Album(
            title: 'Midnights',
            cover: 'cover.jpg',
            releaseDate: $this->releaseDate,
            artists: ['Taylor Swift'],
            externalUrl: 'spotify:album:1',
            id: 123
        );

        $this->assertInstanceOf(Album::class, $album);
        $this->assertEquals(123, $album->getId());
        $this->assertEquals('Midnights', $album->getTitle());
        $this->assertEquals('cover.jpg', $album->getCover());
        $this->assertEquals($this->releaseDate, $album->getReleaseDate());
        $this->assertEquals('spotify:album:1', $album->getExternalUrl());
        $this->assertCount(1, $album->getArtists());
        $this->assertInstanceOf(Artist::class, $album->getArtists()[0]);
        $this->assertEquals('Taylor Swift', $album->getArtists()[0]->getName());
    }

    public function testCanBeInstantiatedWithArtistObjects(): void
    {
        $artist = new Artist('Lana Del Rey', 1);
        $album = new Album(
            title: 'Norman F***ing Rockwell!',
            cover: 'nfr.jpg',
            releaseDate: new DateTime(),
            artists: [$artist],
            externalUrl: null
        );

        $this->assertCount(1, $album->getArtists());
        $this->assertSame($artist, $album->getArtists()[0]);
    }

    public function testCanBeSerializedToArray(): void
    {
        $artists = [new Artist('Artist One'), new Artist('Artist Two')];
        $album = new Album(
            title: $this->albumData['title'],
            cover: $this->albumData['cover'],
            releaseDate: $this->releaseDate,
            artists: $artists,
            externalUrl: $this->albumData['external_url'],
            id: $this->albumData['id']
        );

        $serialized = $album->__serialize();

        $expected = [
            'title' => 'Test Album',
            'cover' => 'http://example.com/cover.jpg',
            'release_date' => '2023-10-27',
            'external_url' => 'http://example.com/album',
            'artists' => $artists,
        ];

        $this->assertEquals($expected, $serialized);
    }

    public function testCanBeCreatedFromArray(): void
    {
        $album = Album::fromArray($this->albumData);

        $this->assertInstanceOf(Album::class, $album);
        $this->assertEquals($this->albumData['id'], $album->getId());
        $this->assertEquals($this->albumData['title'], $album->getTitle());
        $this->assertEquals($this->albumData['cover'], $album->getCover());
        $this->assertEquals($this->albumData['release_date'], $album->getReleaseDate()->format('Y-m-d'));
        $this->assertEquals($this->albumData['external_url'], $album->getExternalUrl());
        
        $artists = $album->getArtists();
        $this->assertCount(2, $artists);
        $this->assertInstanceOf(Artist::class, $artists[0]);
        $this->assertEquals('Artist One', $artists[0]->getName());
        $this->assertInstanceOf(Artist::class, $artists[1]);
        $this->assertEquals('Artist Two', $artists[1]->getName());
    }

    public function testFromArrayHandlesOptionalFields(): void
    {
        $data = ['title' => 'Test Album', 'cover' => 'cover.jpg', 'release_date' => '2023-10-27', 'artists' => ['An Artist']];
        $album = Album::fromArray($data);
        $this->assertNull($album->getId());
        $this->assertNull($album->getExternalUrl());
    }
}