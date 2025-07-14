<?php

namespace Tests\Unit\Services;

use App\Domain\Music\Album;
use App\Domain\Music\Artist;
use App\Domain\Music\Track;
use App\Repositories\TrackRepository;
use App\Services\AlbumService;
use App\Services\ArtistService;
use App\Services\Contracts\StreamingApiServiceInterface;
use App\Services\TrackService;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class TrackServiceTest extends TestCase
{
    private $trackRepository;
    private $streamingApiService;
    private $artistService;
    private $albumService;
    private $trackService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trackRepository = $this->createMock(TrackRepository::class);
        $this->streamingApiService = $this->createMock(StreamingApiServiceInterface::class);
        $this->artistService = $this->createMock(ArtistService::class);
        $this->albumService = $this->createMock(AlbumService::class);

        $this->trackService = new TrackService(
            $this->trackRepository,
            $this->streamingApiService,
            $this->artistService,
            $this->albumService
        );
    }

    public function testList()
    {
        $mockTracks = new Collection([
            (object)[
                'id' => 1,
                'title' => 'Test Track 1',
                'isrc' => 'ABC123456789',
                'br_enabled' => 1,
                'artists' => '[{"id": 101, "name": "Artist A"}]',
                'album_id' => 1001,
                'album_title' => 'Album X',
                'cover' => 'cover1.jpg',
                'release_date' => '2023-01-01',
            ],
            (object)[
                'id' => 2,
                'title' => 'Test Track 2',
                'isrc' => 'DEF987654321',
                'br_enabled' => 0,
                'artists' => '[{"id": 102, "name": "Artist B"}]',
                'album_id' => 1002,
                'album_title' => 'Album Y',
                'cover' => 'cover2.jpg',
                'release_date' => '2023-02-01',
            ],
        ]);

        $this->trackRepository->expects($this->once())
            ->method('list')
            ->willReturn($mockTracks);

        $result = $this->trackService->list();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('Test Track 1', $result[0]->title);
        $this->assertTrue($result[0]->br_enabled);
        $this->assertIsArray($result[0]->artists);
        $this->assertEquals('Artist A', $result[0]->artists[0]->name);

        $this->assertEquals(2, $result[1]->id);
        $this->assertEquals('Test Track 2', $result[1]->title);
        $this->assertFalse($result[1]->br_enabled);
        $this->assertIsArray($result[1]->artists);
        $this->assertEquals('Artist B', $result[1]->artists[0]->name);
    }

    public function testGetDataFromStreamingServiceFound()
    {
        $isrc = 'ABC123456789';
        $mockTrack = Track::fromArray([
            'id' => null,
            'title' => 'Streaming Track',
            'isrc' => $isrc,
            'duration' => 180,
            'br_enabled' => true,
            'artists' => [
                new Artist('Streaming Artist', 1)
            ],
            'album' => new Album('Streaming Album', 'https://example.com/streaming-album/cover.jpg', new DateTime('2024-01-01'), ['Streaming Artist'], 'https://example.com/streaming-album'),
        ]);

        $this->streamingApiService->expects($this->once())
            ->method('searchByISRC')
            ->with($isrc)
            ->willReturn($mockTrack);

        $result = $this->trackService->getDataFromStreamingService($isrc);

        $this->assertInstanceOf(Track::class, $result);
        $this->assertEquals('Streaming Track', $result->getTitle());
        $this->assertEquals($isrc, $result->getIsrc());
    }

    public function testGetDataFromStreamingServiceNotFound()
    {
        $isrc = 'QMRSZ2100658';

        $this->streamingApiService->expects($this->once())
            ->method('searchByISRC')
            ->with($isrc)
            ->willReturn(null);

        $result = $this->trackService->getDataFromStreamingService($isrc);

        $this->assertNull($result);
    }

    public function testStoreWithTrackObject()
    {
        $album = new Album('Eternal Blue', 'https://i.scdn.co/image/ab67616d0000b2733e234c82f96fa4ded8e5ca47', new DateTime('2021-09-16'), ['Spiritbox'], 'https://open.spotify.com/album/0OzpSEZ5rwwAu1JC2zRAvb');
        $artist = new Artist('Spiritbox', 1);

        $trackData = [
            'title' => 'Secret Garden',
            'isrc' => 'QMRSZ2100658',
            'duration' => 219,
            'br_enabled' => true,
            'artists' => [$artist],
            'album' => $album,
        ];
        $track = Track::fromArray($trackData);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->albumService->expects($this->once())
            ->method('ensureExistent')
            ->with($album)
            ->willReturn($album);

        $this->artistService->expects($this->once())
            ->method('ensureExistent')
            ->with($artist)
            ->willReturn($artist);

        $this->trackRepository->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($arg) {
                $this->assertEquals('Secret Garden', $arg['title']);
                return true;
            }))
            ->willReturn(['id' => 1, 'title' => 'Secret Garden', 'isrc' => 'QMRSZ2100658', 'duration' => 219, 'br_enabled' => true, 'album_id' => 1]);

        $this->trackRepository->expects($this->once())
            ->method('addArtist')
            ->with(1, $artist)
            ->willReturn(true);

        $result = $this->trackService->store($track);

        $this->assertInstanceOf(Track::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('Secret Garden', $result->getTitle());
        $this->assertEquals('QMRSZ2100658', $result->getIsrc());
        $this->assertEquals($album->getTitle(), $result->getAlbum()->getTitle());
        $this->assertCount(1, $result->getArtists());
        $this->assertEquals($artist->getName(), $result->getArtists()[0]->getName());
    }


    public function testStoreWithArrayData()
    {
        $album = new Album('Eternal Blue', 'https://i.scdn.co/image/ab67616d0000b2733e234c82f96fa4ded8e5ca47', new DateTime('2021-09-16'), ['Spiritbox'], 'https://open.spotify.com/album/0OzpSEZ5rwwAu1JC2zRAvb');
        $artist = new Artist('Spiritbox', 1);

        $trackDataArray = [
            'title' => 'Constance',
            'isrc' => 'QMRSZ2002759',
            'duration' => 274,
            'br_enabled' => false,
            'artists' => ['Spiritbox'],
            'album' => ['id' => 1, 'title' => 'Eternal Blue', 'cover' => 'https://i.scdn.co/image/ab67616d0000b2733e234c82f96fa4ded8e5ca47', 'release_date' => '2024-01-01', 'artists' => ['Spiritbox'], 'external_url' => 'http://example.com/album'],
        ];

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $this->albumService->expects($this->once())
            ->method('ensureExistent')
            ->with($this->callback(function ($arg) use ($album) {
                $this->assertInstanceOf(Album::class, $arg);
                $this->assertEquals($album->getTitle(), $arg->getTitle());
                return true;
            }))
            ->willReturn($album);

        $this->artistService->expects($this->once())
            ->method('ensureExistent')
            ->with($this->callback(function ($arg) use ($artist) {
                $this->assertInstanceOf(Artist::class, $arg);
                $this->assertEquals($artist->getName(), $arg->getName());
                return true;
            }))
            ->willReturn($artist);

        $this->trackRepository->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($arg) {
                $this->assertEquals('Constance', $arg['title']);
                return true;
            }))
            ->willReturn(['id' => 2, 'title' => 'Constance', 'isrc' => 'QMRSZ2002759', 'duration' => 274, 'br_enabled' => false, 'album_id' => 1]);

        $this->trackRepository->expects($this->once())
            ->method('addArtist')
            ->with(2, $artist)
            ->willReturn(true);

        $result = $this->trackService->store($trackDataArray);

        $this->assertInstanceOf(Track::class, $result);
        $this->assertEquals(2, $result->getId());
        $this->assertEquals('Constance', $result->getTitle());
        $this->assertEquals('QMRSZ2002759', $result->getIsrc());
        $this->assertEquals($album->getTitle(), $result->getAlbum()->getTitle());
        $this->assertCount(1, $result->getArtists());
        $this->assertEquals($artist->getName(), $result->getArtists()[0]->getName());
    }

    public function testSearchByIsrcFound()
    {
        $isrc = 'FOUND9990008';
        $mockDbTrack = (object)[
            'id' => 1,
            'title' => 'Found Track',
            'isrc' => $isrc,
            'duration' => 250,
            'br_enabled' => 1,
            'artists' => json_encode(['Artist X']),
            'album' => [
                'id' => 2001,
                'title' => 'Album Z',
                'cover' => 'coverZ.jpg',
                'release_date' => '2023-03-01',
                'artists' => "['Artist Y']",
                'external_url' => 'http://example.com/album-z'
            ],
            'album_id' => 2001,
            'album_title' => 'Album Z',
            'cover' => 'coverZ.jpg',
            'release_date' => '2023-03-01',
        ];

        $this->trackRepository->expects($this->once())
            ->method('getByIsrc')
            ->with($isrc)
            ->willReturn($mockDbTrack);

        $result = $this->trackService->searchByIsrc($isrc);

        $this->assertInstanceOf(Track::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals('Found Track', $result->getTitle());
        $this->assertEquals($isrc, $result->getIsrc());
        $this->assertIsArray($result->getArtists());
        $this->assertEquals('Artist X', $result->getArtists()[0]->__toString()); 
        $this->assertInstanceOf(Album::class, $result->getAlbum());
        $this->assertEquals('Album Z', $result->getAlbum()->getTitle());
        $this->assertEquals('coverZ.jpg', $result->getAlbum()->getCover());
    }

    public function testSearchByIsrcNotFound()
    {
        $isrc = 'NOTFOUNDISRC';

        $this->trackRepository->expects($this->once())
            ->method('getByIsrc')
            ->with($isrc)
            ->willReturn(null);

        $result = $this->trackService->searchByIsrc($isrc);
        $this->assertNull($result);
    }

    public function testAddArtist()
    {
        $trackId = 123;
        $artist = new Artist('New Artist', 301);

        $this->trackRepository->expects($this->once())
            ->method('addArtist')
            ->with($trackId, $artist)
            ->willReturn(true);

        $result = $this->trackService->addArtist($trackId, $artist);

        $this->assertTrue($result);
    }
}
