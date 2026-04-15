<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneratorGenderWord;
use App\Models\GeneratorNumber;
use App\Models\GeneratorSuffix;
use App\Models\GeneratorWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamertagController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gender'         => 'nullable|string|in:any,male,female,neutral',
            'theme'          => 'nullable|string|max:50',
            'language'       => 'nullable|string|max:50',
            'includeWords'   => 'nullable|string',
            'otherRequests'  => 'nullable|string',
            'characterLimit' => 'nullable|integer|min:3|max:12',
        ]);

        $gender         = $validated['gender'] ?? 'any';
        $theme          = $validated['theme'] ?? 'gaming';
        $language       = $validated['language'] ?? 'english';
        $characterLimit = $validated['characterLimit'] ?? 12;

        $customWords = collect(explode(',', $validated['includeWords'] ?? ''))
            ->map(fn($w) => trim($w))
            ->filter(fn($w) => $w !== '')
            ->values()
            ->toArray();

        $parsedRequests = $this->parseOtherRequests($validated['otherRequests'] ?? '');

        // Load data with fallback helper
        $baseWords       = $this->fetchWords($language, $theme);
        $prefixes        = $gender !== 'any' ? $this->fetchGenderWords($language, $gender, 'prefix') : [];
        $suffixGender    = $gender !== 'any' ? $this->fetchGenderWords($language, $gender, 'suffix') : [];
        $languageSuffixes = $this->fetchSuffixes($language);
        $numbers         = GeneratorNumber::pluck('value')->toArray() ?: ['01', '07', '13', '99', '47'];

        $generated       = [];
        $usedCustomWords = [];
        $usedPatterns    = [];
        $customQuota     = !empty($customWords) ? 1 : 0;
        $customUsed      = 0;
        $safety          = 0;

        while (count($generated) < 6 && $safety < 150) {
            $forceCustom = $customUsed < $customQuota;

            $tag = $this->generateSingleTag(
                $baseWords,
                $prefixes,
                $suffixGender,
                $languageSuffixes,
                $numbers,
                $gender,
                $characterLimit,
                $customWords,
                $parsedRequests,
                $forceCustom,
                $usedCustomWords,
                $usedPatterns
            );

            $tagLower = mb_strtolower($tag);

            if (mb_strlen($tag) >= 3 && !in_array($tagLower, array_map('mb_strtolower', $generated), true)) {
                if ($this->containsCustomWord($tag, $customWords)) {
                    $matched = $this->findMatchedCustomWord($tag, $customWords);
                    if ($matched !== null) {
                        $usedCustomWords[] = mb_strtolower($matched);
                    }
                    if ($forceCustom) {
                        $customUsed++;
                    }
                }

                $generated[]    = $tag;
                $usedPatterns[] = $this->extractPattern($tag);
            }

            $safety++;
        }

    return response()->json([
    'db_host' => config('database.connections.mysql.host'),
    'db_port' => config('database.connections.mysql.port'),
    'db_name' => config('database.connections.mysql.database'),
    'db_probe_exists' => \App\Models\GeneratorWord::where('word', 'JozelProbe')->exists(),
    'total_generator_words' => \App\Models\GeneratorWord::count(),
    'sample_words' => array_slice($baseWords, 0, 5),
]);
    }

    // ─── Data Fetchers ────────────────────────────────────────────────────────

    private function fetchWords(string $language, string $theme): array
    {
        $words = GeneratorWord::where('language', $language)
            ->where('theme', $theme)
            ->pluck('word')
            ->toArray();

        if (empty($words)) {
            $words = GeneratorWord::where('language', 'english')
                ->where('theme', 'gaming')
                ->pluck('word')
                ->toArray();
        }

        return $words;
    }

    private function fetchGenderWords(string $language, string $gender, string $position): array
    {
        $words = GeneratorGenderWord::where('language', $language)
            ->where('gender', $gender)
            ->where('position', $position)
            ->pluck('word')
            ->toArray();

        if (empty($words)) {
            $words = GeneratorGenderWord::where('language', 'english')
                ->where('gender', $gender)
                ->where('position', $position)
                ->pluck('word')
                ->toArray();
        }

        return $words;
    }

    private function fetchSuffixes(string $language): array
    {
        $suffixes = GeneratorSuffix::where('language', $language)
            ->pluck('word')
            ->toArray();

        if (empty($suffixes)) {
            $suffixes = GeneratorSuffix::where('language', 'english')
                ->pluck('word')
                ->toArray();
        }

        return $suffixes;
    }

    // ─── Tag Generator ────────────────────────────────────────────────────────

    private function generateSingleTag(
        array $baseWords,
        array $prefixes,
        array $suffixGenderWords,
        array $languageSuffixes,
        array $numbers,
        string $gender,
        int $characterLimit,
        array $customWords,
        array $parsedRequests,
        bool $forceCustom,
        array $usedCustomWords,
        array $usedPatterns
    ): string {
        if (empty($baseWords)) {
            return 'Player' . rand(10, 99);
        }

        $allowNumbers  = !in_array('no_numbers', $parsedRequests, true);
        $preferShort   = in_array('short', $parsedRequests, true);

        // Pick a base word — shuffle to reduce repetition
        $shuffled = $baseWords;
        shuffle($shuffled);
        $dbWord = $shuffled[0];

        // Build main word
        $availableCustomWords = $this->getAvailableCustomWords($customWords, $usedCustomWords);
        $mainWord = $this->buildMainWord($dbWord, $availableCustomWords, $forceCustom);

        // Pick optional parts — randomize independently
        $prefix      = ($gender !== 'any' && !empty($prefixes) && mt_rand(1, 100) > 75)
                        ? $prefixes[array_rand($prefixes)]
                        : '';

        $genderSuffix = ($gender !== 'any' && !empty($suffixGenderWords) && mt_rand(1, 100) > 85)
                        ? $suffixGenderWords[array_rand($suffixGenderWords)]
                        : '';

        $extraSuffix = '';
        $number      = '';

        if (!$preferShort && mt_rand(1, 100) > 40) {
            // Vary the pattern: avoid using the same suffix/number pattern repeatedly
            $useNumber = $allowNumbers && !empty($numbers) && mt_rand(0, 1) === 1;
            $useSuffix = !empty($languageSuffixes) && mt_rand(0, 1) === 1;

            $pattern = ($useNumber ? 'N' : '') . ($useSuffix ? 'S' : '');

            // If this pattern was recently used, flip the choice
            if (in_array($pattern, array_slice($usedPatterns, -3), true)) {
                $useNumber = !$useNumber;
                $useSuffix = !$useSuffix;
            }

            if ($useSuffix) {
                $extraSuffix = $languageSuffixes[array_rand($languageSuffixes)];
            }

            if ($useNumber) {
                $number = $numbers[array_rand($numbers)];
            }
        }

        $candidates = [
            $prefix . $mainWord . $genderSuffix . $extraSuffix . $number,
            $prefix . $mainWord . $genderSuffix . $extraSuffix,
            $prefix . $mainWord . $genderSuffix . $number,
            $prefix . $mainWord . $extraSuffix . $number,
            $prefix . $mainWord . $extraSuffix,
            $prefix . $mainWord . $number,
            $mainWord . $genderSuffix . $extraSuffix,
            $mainWord . $extraSuffix,
            $mainWord . $number,
            $prefix . $mainWord,
            $mainWord,
        ];

        foreach ($candidates as $candidate) {
            if (mb_strlen($candidate) <= $characterLimit && mb_strlen($candidate) >= 3) {
                return $candidate;
            }
        }

        return $this->smartTrim($mainWord, $characterLimit);
    }

    private function buildMainWord(string $dbWord, array $availableCustomWords, bool $forceCustom): string
    {
        if (empty($availableCustomWords)) {
            return $dbWord;
        }

        $shouldUseCustom = $forceCustom || mt_rand(1, 100) > 85;

        if (!$shouldUseCustom) {
            return $dbWord;
        }

        $customWord = $availableCustomWords[array_rand($availableCustomWords)];

        return mt_rand(0, 1) === 1
            ? $this->combineWords($customWord, $dbWord)
            : $this->combineWords($dbWord, $customWord);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function extractPattern(string $tag): string
    {
        $hasNumber = preg_match('/\d/', $tag);
        $wordCount = preg_match_all('/[A-Z][a-z]+/', $tag);

        return ($hasNumber ? 'N' : '') . ($wordCount > 1 ? 'M' : 'S');
    }

    private function parseOtherRequests(string $input): array
    {
        $text  = mb_strtolower(trim($input));
        $flags = [];

        if ($text === '') {
            return $flags;
        }

        if (str_contains($text, 'short') || str_contains($text, 'brief') || str_contains($text, 'small')) {
            $flags[] = 'short';
        }

        if (str_contains($text, 'no number') || str_contains($text, 'without number')) {
            $flags[] = 'no_numbers';
        }

        return $flags;
    }

    private function containsCustomWord(string $tag, array $customWords): bool
    {
        foreach ($customWords as $word) {
            if ($word !== '' && stripos($tag, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    private function findMatchedCustomWord(string $tag, array $customWords): ?string
    {
        foreach ($customWords as $word) {
            if ($word !== '' && stripos($tag, $word) !== false) {
                return $word;
            }
        }

        return null;
    }

    private function getAvailableCustomWords(array $customWords, array $usedCustomWords): array
    {
        if (empty($customWords)) {
            return [];
        }

        $used = array_map('mb_strtolower', $usedCustomWords);

        $available = array_values(array_filter($customWords, fn($w) => !in_array(mb_strtolower($w), $used, true)));

        return !empty($available) ? $available : $customWords;
    }

    private function combineWords(string $first, string $second): string
    {
        $first  = ucfirst(trim($first));
        $second = ucfirst(trim($second));

        if ($first === '')  return $second;
        if ($second === '') return $first;

        return $first . $second;
    }

    private function smartTrim(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $parts   = preg_split('/(?=[A-Z])|[_\-\s]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $rebuilt = '';

        foreach ($parts as $part) {
            if (mb_strlen($rebuilt . $part) <= $limit) {
                $rebuilt .= $part;
            } else {
                break;
            }
        }

        if ($rebuilt !== '' && mb_strlen($rebuilt) >= 3) {
            return $rebuilt;
        }

        return mb_substr($text, 0, $limit);
    }
}