<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GamertagOptimizerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamertagOptimizerController extends Controller
{
    public function __construct(
        protected GamertagOptimizerService $optimizer
    ) {}

    public function optimize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gamertag'      => ['required', 'string', 'min:1', 'max:40'],
            'keep_numbers'  => ['nullable', 'boolean'],
            'keep_meaning'  => ['nullable', 'boolean'],
        ]);

        $gamertag = trim($validated['gamertag']);

        $result = $this->optimizer->optimize($gamertag, [
            'style'        => 'any',
            'max_length'   => 12,
            'keep_numbers' => (bool) ($validated['keep_numbers'] ?? false),
            'keep_meaning' => (bool) ($validated['keep_meaning'] ?? true),
        ]);

        return response()->json($result);
    }
}