<?php

namespace App\Domain\Music\ValueObject;

use InvalidArgumentException;

class Duration
{
    private int $seconds;

    public function __construct(int $seconds)
    {
        if ($seconds < 0) {
            throw new InvalidArgumentException('Duration cannot be negative.');
        }
        $this->seconds = $seconds;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function __toString(): string
    {
        $minutes = floor($this->seconds / 60);
        $remainingSeconds = $this->seconds % 60;
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}