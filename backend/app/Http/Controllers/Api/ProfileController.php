<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\XblApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected XblApiService $xbl) {}

    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'gamertag' => ['required', 'string', 'min:1', 'max:15'],
        ]);

        $gamertag = trim($request->input('gamertag'));
        $people   = $this->xbl->searchByGamertag($gamertag);

        if (isset($people['error'])) {
            if (($people['status'] ?? 0) === 404) {
                return response()->json([
                    'found'   => false,
                    'message' => 'Gamertag not found.',
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

        $match = collect($people)->first(function ($person) use ($gamertag) {
            return strtolower(trim($person['gamertag'] ?? '')) === strtolower($gamertag);
        });

        if (! $match) {
            return response()->json([
                'found'   => false,
                'message' => 'Gamertag not found.',
            ]);
        }

        $detail = $match['detail'] ?? [];

        $profile = [
            'xuid'         => $match['xuid'] ?? null,
            'gamertag'     => $match['gamertag'] ?? $gamertag,
            'gamerscore'   => (int) ($match['gamerScore'] ?? 0),
            'avatar'       => $match['displayPicRaw'] ?? null,
            'account_tier' => $detail['accountTier'] ?? null,
            'bio'          => $detail['bio'] ?? null,
            'tenure'       => $detail['tenure'] ?? null,
            'followers'    => (int) ($detail['followerCount'] ?? 0),
            'following'    => (int) ($detail['followingCount'] ?? 0),
            'real_name'    => $detail['realName'] ?? null,
            'location'     => $detail['location'] ?? null,
        ];

        return response()->json([
            'found'   => true,
            'profile' => $profile,
        ]);
    }
}