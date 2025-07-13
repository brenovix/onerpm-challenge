<?php

namespace App\Actions;

use App\Actions\Traits\Responder;
use App\Services\TrackService;

class TrackList
{
    use Responder;

    public function __invoke(TrackService $trackService)
    {
        $tracks = $trackService->list();
        return $this->asJson($tracks, 200, 'Tracks retrieved successfully');
    }
}