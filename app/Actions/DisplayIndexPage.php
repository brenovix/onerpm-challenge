<?php

namespace App\Actions;

use App\Actions\Traits\Responder;
use App\Services\IsrcSyncService;
use App\Services\TrackService;

class DisplayIndexPage
{
    use Responder;

    public function __invoke(IsrcSyncService $isrcSyncService)
    {
        $isrcSyncService->syncMissingIsrc();
        return $this->asHtml('Home/Index', []);
    }
}