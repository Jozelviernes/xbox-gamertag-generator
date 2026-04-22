<?php

namespace App\Services;

use Illuminate\Support\Arr;
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

    public function getTitleHistoryByXuid(string $xuid): array
    {
        $cacheKey = 'xbl_title_history_' . $xuid;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($xuid) {
            $data = $this->get("/player/titleHistory/{$xuid}");

            if (isset($data['error'])) {
                return $data;
            }

            return $this->extractTitleHistoryItems($data);
        });
    }

    public function getPlayerAchievementsByXuid(string $xuid): array
    {
        $cacheKey = 'xbl_player_achievements_' . $xuid;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($xuid) {
            $data = $this->get("/achievements/player/{$xuid}");

            if (isset($data['error'])) {
                return $data;
            }

            return $this->extractAchievementItems($data);
        });
    }

    public function getTitleAchievementsByXuid(string $xuid, string $titleId): array
    {
        $cacheKey = 'xbl_title_achievements_' . $xuid . '_' . $titleId;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($xuid, $titleId) {
            return $this->get("/achievements/player/{$xuid}/title/{$titleId}");
        });
    }

  public function getGameHistoryByXuid(string $xuid, int $limit = 10): array
{
    $cacheKey = 'xbl_game_history_' . $xuid . '_' . $limit;

    return Cache::remember($cacheKey, $this->cacheTtl, function () use ($xuid, $limit) {
        $titleHistory = $this->getTitleHistoryByXuid($xuid);

        if (isset($titleHistory['error'])) {
            return $titleHistory;
        }

        return collect($titleHistory)
            ->map(fn (array $title) => $this->formatGameHistoryItem($title))
            ->filter(fn (array $item) => ! empty($item['title_id']) || ! empty($item['name']))
            ->sortByDesc(fn (array $item) => $item['last_played'] ?? '')
            ->take(max(1, $limit))
            ->values()
            ->all();
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

    protected function extractTitleHistoryItems(array $data): array
    {
        foreach (['titles', 'items', 'history', 'content.titles', 'content.items'] as $path) {
            $items = data_get($data, $path);

            if (is_array($items)) {
                return array_values(array_filter($items, 'is_array'));
            }
        }

        if ($this->isListOfArrays($data)) {
            return $data;
        }

        return [];
    }

    protected function extractAchievementItems(array $data): array
    {
        foreach ([
            'titles',
            'items',
            'achievements',
            'data',
            'content.titles',
            'content.items',
            'content.achievements',
            'achievements.items',
        ] as $path) {
            $items = data_get($data, $path);

            if (is_array($items)) {
                return array_values(array_filter($items, 'is_array'));
            }
        }

        if ($this->isListOfArrays($data)) {
            return $data;
        }

        return [];
    }

   protected function formatGameHistoryItem(array $title, array $achievement = []): array
{
    $titleAchievement = is_array($title['achievement'] ?? null) ? $title['achievement'] : [];
    $achievementData  = ! empty($achievement) ? $achievement : $titleAchievement;

    $titleId = (string) (
        $title['titleId']
        ?? $title['modernTitleId']
        ?? $achievementData['titleId']
        ?? $achievementData['modernTitleId']
        ?? ''
    );

    $currentGamerscore = $this->toInt(
        data_get($achievementData, 'currentGamerscore')
        ?? data_get($achievementData, 'achievement.currentGamerscore')
    );

    $totalGamerscore = $this->toInt(
        data_get($achievementData, 'totalGamerscore')
        ?? data_get($achievementData, 'achievement.totalGamerscore')
    );

    $unlockedAchievements = $this->toInt(
        data_get($achievementData, 'currentAchievements')
        ?? data_get($achievementData, 'achievement.currentAchievements')
    );

    $totalAchievements = $this->toInt(
        data_get($achievementData, 'totalAchievements')
        ?? data_get($achievementData, 'achievement.totalAchievements')
    );

    $completionPercent = $this->toInt(
        data_get($achievementData, 'progressPercentage')
        ?? data_get($achievementData, 'achievement.progressPercentage')
    );

    if ($completionPercent <= 0) {
        $completionPercent = $this->calculateCompletionPercent(
            $unlockedAchievements,
            $totalAchievements,
            $currentGamerscore,
            $totalGamerscore
        );
    }

    return [
        'title_id' => $titleId,
        'name' => $title['name']
            ?? $title['titleName']
            ?? $achievementData['name']
            ?? 'Unknown Game',
        'display_image' => $title['displayImage']
            ?? $title['image']
            ?? null,
        'last_played' => $title['titleHistory']['lastTimePlayed']
            ?? $title['lastPlayed']
            ?? $title['titleHistory']['lastPlayed']
            ?? $title['stats']['lastPlayed']
            ?? null,
        'platforms' => array_values(array_filter(
            Arr::wrap($title['devices'] ?? $title['platforms'] ?? []),
            fn ($value) => is_string($value) && $value !== ''
        )),
        'gamerscore' => [
            'current' => $currentGamerscore,
            'total'   => $totalGamerscore,
        ],
        'achievements' => [
            'unlocked' => $unlockedAchievements,
            'total'    => $totalAchievements,
        ],
        'completion_percent' => (float) $completionPercent,
    ];
}
    protected function calculateCompletionPercent(
        int $unlockedAchievements,
        int $totalAchievements,
        int $currentGamerscore,
        int $totalGamerscore
    ): float {
        if ($totalAchievements > 0) {
            return round(($unlockedAchievements / max(1, $totalAchievements)) * 100, 1);
        }

        if ($totalGamerscore > 0) {
            return round(($currentGamerscore / max(1, $totalGamerscore)) * 100, 1);
        }

        return 0.0;
    }

    protected function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    protected function isListOfArrays(array $items): bool
    {
        if (! array_is_list($items) || $items === []) {
            return false;
        }

        return collect($items)->every(fn ($item) => is_array($item));
    }
}