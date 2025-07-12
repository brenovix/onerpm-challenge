<?php

namespace App\Domain\Music\ValueObject;

use InvalidArgumentException;

class ISRC
{
    private string $code;

    public function __construct(string $code)
    {
        if (!$this->isValid($code)) {
            throw new InvalidArgumentException('Invalid ISRC code');
        }
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function __serialize(): array
    {
        return ['isrc' => $this->code];
    }

    public function __unserialize(array $data): void
    {
        if (!array_key_exists('isrc', $data) || !$this->isValid($data['isrc'])) {
            throw new InvalidArgumentException('Invalid ISRC code during unserialization');
        }
        $this->code = $data['isrc'];
    }

    private function isValid(string $code): bool
    {
        return preg_match('/^[A-Z]{2}[0-9A-Z]{3}[0-9]{2}[0-9]{5}$/', $code) === 1;
    }
}