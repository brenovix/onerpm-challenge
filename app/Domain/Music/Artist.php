<?php

namespace App\Domain\Music;

use App\Domain\Entity;

class Artist extends Entity
{
    public function __construct(private string $name, ?int $id = null)
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
        ];
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            id: $data['id'] ?? null
        );
    }
}