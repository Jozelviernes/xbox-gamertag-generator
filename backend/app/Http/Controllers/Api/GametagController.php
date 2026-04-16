<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\XblApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GametagController extends Controller
{
    public function __construct(protected XblApiService $xbl) {}

    public function check(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'gamertag' => ['required', 'string', 'min:1', 'max:15'],
            ]);

            $gamertag = trim($request->input('gamertag'));
            $people   = $this->xbl->searchByGamertag($gamertag);

            if (isset($people['error'])) {
                if (($people['status'] ?? 0) === 404) {
                    return response()->json([
                        'gamertag'  => $gamertag,
                        'available' => true,
                        'status'    => 'likely_available',
                        'message'   => 'No profile found — this gamertag appears to be available.',
                    ]);
                }

                if (in_array(($people['code'] ?? null), ['RATE_LIMIT', 'LOCAL_RATE_LIMIT'])) {
                    return response()->json([
                        'error'   => true,
                        'message' => $people['message'] ?? 'Tool is temporarily busy. Please try again next hour.',
                    ], 503);
                }

                return response()->json([
                    'error'   => true,
                    'message' => $people['message'] ?? 'Could not reach Xbox API. Please try again.',
                ], 503);
            }

            $taken = collect($people)->contains(function ($person) use ($gamertag) {
                return strtolower(trim($person['gamertag'] ?? '')) === strtolower($gamertag);
            });

            return response()->json([
                'gamertag'  => $gamertag,
                'available' => ! $taken,
                'status'    => $taken ? 'taken' : 'likely_available',
                'message'   => $taken
                    ? 'This gamertag is already taken.'
                    : 'This gamertag appears to be available.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong while checking the gamertag.',
            ], 500);
        }
    }
}