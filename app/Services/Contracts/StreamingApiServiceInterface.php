<?php

namespace App\Services\Contracts;

use App\Domain\Music\Track;

interface StreamingApiServiceInterface
{
    public function search(array $params): ?Track;

    public function searchByISRC(string $isrc): ?Track;
}