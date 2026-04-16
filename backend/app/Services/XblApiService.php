<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XblApiService
{
    protected string $baseUrl = 'https://xbl.io/api/v2';
    protected string $apiKey;
    protected int $cacheTtl;

    private const HOURLY_LIMIT = 140;
    private const HOURLY_KEY = 'xbl_hourly_request_count';
    private const HOURLY_WINDOW_SECONDS = 3600;

    public function __construct()
    {
        $this->apiKey   = config('services.xbl.key');
        $this->cacheTtl = (int) config('services.xbl.cache_ttl', 300);
    }

    protected function get(string $endpoint, array $query = []): array
    {
        if (! $this->hasRemainingBudget()) {
            return [
                'error'   => true,
                'status'  => 429,
                'code'    => 'LOCAL_RATE_LIMIT',
                'message' => 'Tool is temporarily busy. Please try again next hour.',
            ];
        }

        $url = $this->baseUrl . $endpoint;

        $http = Http::withHeaders([
            'X-Authorization' => $this->apiKey,
            'Accept'          => 'application/json',
        ])
        ->connectTimeout(5)
        ->timeout(10);

        if (app()->isLocal()) {
            $http = $http->withoutVerifying();
        }

        $response = empty($query)
            ? $http->get($url)
            : $http->get($url, $query);

        if (! $response->successful()) {
            $status = $response->status();
            $body   = $response->body();

            Log::warning('XBL API error', [
                'endpoint' => $endpoint,
                'status'   => $status,
                'body'     => $body,
            ]);

            if ($status === 429) {
                return [
                    'error'   => true,
                    'status'  => 429,
                    'code'    => 'RATE_LIMIT',
                    'message' => 'Tool is temporarily busy. Please try again next hour.',
                    'body'    => $body,
                ];
            }

            return [
                'error'   => true,
                'status'  => $status,
                'message' => 'Could not reach Xbox API. Please try again.',
                'body'    => $body,
            ];
        }

        $this->incrementBudget();

        return $response->json() ?? [];
    }

    protected function hasRemainingBudget(): bool
    {
        return (int) Cache::get(self::HOURLY_KEY, 0) < self::HOURLY_LIMIT;
    }

    protected function incrementBudget(): void
    {
        $count = (int) Cache::get(self::HOURLY_KEY, 0);

        if ($count === 0) {
            Cache::put(self::HOURLY_KEY, 1, self::HOURLY_WINDOW_SECONDS);
            return;
        }

        Cache::increment(self::HOURLY_KEY);
    }

    public function getBudgetStatus(): array
    {
        $used = (int) Cache::get(self::HOURLY_KEY, 0);

        return [
            'used'      => $used,
            'remaining' => max(0, self::HOURLY_LIMIT - $used),
            'limit'     => self::HOURLY_LIMIT,
        ];
    }

    public function searchByGamertag(string $gamertag): array
    {
        $gamertag = trim($gamertag);
        $cacheKey = 'xbl_search_' . strtolower($gamertag);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($gamertag) {
            $data = $this->get('/search/' . rawurlencode($gamertag));

            if (isset($data['error'])) {
                return $data;
            }

            if (isset($data['people']) && is_array($data['people'])) {
                return $data['people'];
            }

            if (isset($data['content']['people']) && is_array($data['content']['people'])) {
                return $data['content']['people'];
            }

            return [];
        });
    }

    public function getProfileByXuid(string $xuid): array
    {
        $cacheKey = 'xbl_profile_' . $xuid;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($xuid) {
            $data = $this->get("/account/{$xuid}");

            if (isset($data['error'])) {
                return $data;
            }

            $user = $data['profileUsers'][0] ?? null;

            if (! $user) {
                return [
                    'error'   => true,
                    'status'  => 404,
                    'message' => 'Profile not found or is private.',
                ];
            }

            return $this->mapProfileSettings($user);
        });
    }

    protected function mapProfileSettings(array $user): array
    {
        $settings = collect($user['settings'] ?? [])
            ->keyBy('id')
            ->map(fn ($s) => $s['value'] ?? null);

        return [
            'xuid'         => $user['id'] ?? null,
            'gamertag'     => $settings->get('Gamertag'),
            'gamerscore'   => (int) ($settings->get('Gamerscore') ?? 0),
            'avatar'       => $settings->get('GameDisplayPicRaw'),
            'account_tier' => $settings->get('AccountTier'),
            'bio'          => $settings->get('Bio'),
            'tenure'       => $settings->get('Tenure'),
            'followers'    => (int) ($settings->get('FollowerCount') ?? 0),
            'following'    => (int) ($settings->get('FollowingCount') ?? 0),
            'real_name'    => $settings->get('RealName'),
            'location'     => $settings->get('Location'),
        ];
    }
}