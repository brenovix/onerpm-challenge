<?php

namespace App\Actions\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;

trait Responder
{
    protected function asJson($data = null, int $status = 200, string $message = ''): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $status);
    }

    protected function asHtml(string $view, array $data, int $status = 200)
    {
        return Inertia::render($view, $data);
    }
}
