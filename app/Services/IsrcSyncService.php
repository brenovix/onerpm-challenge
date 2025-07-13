<?php

namespace App\Services;

use App\Domain\Music\Track;
use App\Repositories\MissingIsrcRepository;
use App\Repositories\TrackRepository;
use App\Services\Contracts\StreamingApiServiceInterface;
use Illuminate\Support\Facades\DB;

class IsrcSyncService
{
    public function __construct(
        private TrackService $trackService,
        private MissingIsrcRepository $missingIsrcRepository,
        private StreamingApiServiceInterface $streamingApiService
    ) {
    }

    public function syncIsrc(string $isrc): ?Track
    {
        $track = $this->trackService->searchByIsrc($isrc);
        
        if ($track) {
            return $track;
        }
        return $this->streamingApiService->searchByISRC($isrc);
    }

    public function syncMissingIsrc(): void
    {
        DB::transaction(function () {
            $isrcsToProcess = $this->missingIsrcRepository->getAll()->map(fn ($item) => $item->code)->toArray();
            foreach ($isrcsToProcess as $isrc) {
                $this->processSingleIsrc($isrc);
            }
        });
    }

    private function processSingleIsrc(string $isrc): void
    {
        if ($this->trackService->searchByIsrc($isrc)) {
            $this->missingIsrcRepository->delete($isrc);
            return;
        }

        $trackData = $this->streamingApiService->searchByISRC($isrc);
        if ($trackData) {
            $this->storeTrackAndMarkAsProcessed($trackData, $isrc);
        }
    }

    private function storeTrackAndMarkAsProcessed(Track $track): void
    {
        $this->trackService->store($track);
        $this->missingIsrcRepository->delete($track->getIsrc());
    }
}