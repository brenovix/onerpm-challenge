<?php

namespace App\Domain;

abstract class Entity
{
    protected int|string|null $id = null;

    public function getId(): int|string|null
    {
        return $this->id;
    }

    abstract public function __serialize(): array;

    abstract public static function fromArray(array $data): self;
}