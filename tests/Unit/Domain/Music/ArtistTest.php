<?php

namespace Tests\Unit\Domain\Music;

use App\Domain\Music\Artist;
use Tests\TestCase;

class ArtistTest extends TestCase
{
    private array $artistData;
    
    public function testCanBeInstantiatedWithBasicData(): void
    {
        $data = [
            'name' => 'Corey Taylor',
            'id' => 1
        ];
        $artist = new Artist($data['name'], $data['id']);

        $this->assertInstanceOf(Artist::class, $artist);
        $this->assertEquals(1, $artist->getId());
        $this->assertEquals('Corey Taylor', $artist->getName());
    }

    public function testCanBeSerialized(): void
    {
        $artist = new Artist('Corey Taylor', 1);
        $serialized = $artist->__serialize();

        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('name', $serialized);
        $this->assertEquals('Corey Taylor', $serialized['name']);
    }
}