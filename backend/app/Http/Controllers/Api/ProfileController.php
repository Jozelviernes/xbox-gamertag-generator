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
        $validated = $request->validate([
            'gamertag' => ['required', 'string', 'min:1', 'max:15'],
        ]);

        $gamertag = trim($validated['gamertag']);
        $people   = $this->xbl->searchByGamertag($gamertag);

        if (isset($people['error'])) {
            if (($people['status'] ?? 0) === 404) {
                return response()->json([
                    'found'   => false,
                    'message' => 'Gamertag not found.',
                ]);
            }

            if (in_array(($people['code'] ?? null), ['RATE_LIMIT', 'LOCAL_RATE_LIMIT'], true)) {
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

        $results = array_is_list($people) ? $people : ($people['people'] ?? []);

        if (! is_array($results) || empty($results)) {
            return response()->json([
                'found'   => false,
                'message' => 'Gamertag not found.',
            ]);
        }

        $normalizedInput = $this->normalizeGamertag($gamertag);

        $match = collect($results)->first(function ($person) use ($normalizedInput) {
            if (! is_array($person)) {
                return false;
            }

            return $this->normalizeGamertag($person['gamertag'] ?? '') === $normalizedInput;
        });

        if (! $match) {
            return response()->json([
                'found'   => false,
                'message' => 'Gamertag not found.',
            ]);
        }

        $detail = is_array($match['detail'] ?? null) ? $match['detail'] : [];

        $profile = [
            'xuid'         => $match['xuid'] ?? null,
            'gamertag'     => $match['gamertag'] ?? $gamertag,
            'gamerscore'   => isset($match['gamerScore']) ? (int) $match['gamerScore'] : 0,
            'avatar'       => $match['displayPicRaw'] ?? null,
            'real_name'    => $match['realName'] ?? null,
            'bio'          => $detail['bio'] ?? null,
            'location'     => $detail['location'] ?? null,
            'account_tier' => $detail['accountTier'] ?? null,
            'reputation'   => $match['xboxOneRep'] ?? null,
            'followers'    => array_key_exists('followerCount', $detail) ? (int) $detail['followerCount'] : null,
            'following'    => array_key_exists('followingCount', $detail) ? (int) $detail['followingCount'] : null,
        ];

        if (($detail['isVerified'] ?? false) === true) {
            $profile['verified'] = true;
        }

        if (($detail['hasGamePass'] ?? false) === true) {
            $profile['has_game_pass'] = true;
        }

        $profile = array_filter($profile, function ($value) {
            if ($value === null) {
                return false;
            }

            if (is_string($value) && trim($value) === '') {
                return false;
            }

            return true;
        });

        return response()->json([
            'found'   => true,
            'profile' => $profile,
        ]);
    }

    private function normalizeGamertag(?string $gamertag): string
    {
        $gamertag = trim((string) $gamertag);
        $gamertag = preg_replace('/\s+/', ' ', $gamertag) ?? $gamertag;

        return mb_strtolower($gamertag);
    }
}