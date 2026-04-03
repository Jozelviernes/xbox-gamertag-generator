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
            'gender' => 'nullable|string|in:any,male,female,neutral',
            'theme' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'includeWords' => 'nullable|string',
            'otherRequests' => 'nullable|string',
            'characterLimit' => 'nullable|integer|min:3|max:12',
        ]);

        $gender = $validated['gender'] ?? 'any';
        $theme = $validated['theme'] ?? 'gaming';
        $language = $validated['language'] ?? 'english';
        $includeWords = $validated['includeWords'] ?? '';
        $characterLimit = $validated['characterLimit'] ?? 12;

        $baseWords = GeneratorWord::query()
            ->where('language', $language)
            ->where('theme', $theme)
            ->pluck('word')
            ->toArray();

        if (empty($baseWords)) {
            $baseWords = GeneratorWord::query()
                ->where('language', 'english')
                ->where('theme', 'gaming')
                ->pluck('word')
                ->toArray();
        }

        $customWords = collect(explode(',', $includeWords))
            ->map(fn ($word) => trim($word))
            ->filter(fn ($word) => $word !== '')
            ->values()
            ->toArray();

        $prefixes = [];
        $suffixGenderWords = [];

        if ($gender !== 'any') {
            $prefixes = GeneratorGenderWord::query()
                ->where('language', $language)
                ->where('gender', $gender)
                ->where('position', 'prefix')
                ->pluck('word')
                ->toArray();

            $suffixGenderWords = GeneratorGenderWord::query()
                ->where('language', $language)
                ->where('gender', $gender)
                ->where('position', 'suffix')
                ->pluck('word')
                ->toArray();

            if (empty($prefixes)) {
                $prefixes = GeneratorGenderWord::query()
                    ->where('language', 'english')
                    ->where('gender', $gender)
                    ->where('position', 'prefix')
                    ->pluck('word')
                    ->toArray();
            }

            if (empty($suffixGenderWords)) {
                $suffixGenderWords = GeneratorGenderWord::query()
                    ->where('language', 'english')
                    ->where('gender', $gender)
                    ->where('position', 'suffix')
                    ->pluck('word')
                    ->toArray();
            }
        }

        $languageSuffixes = GeneratorSuffix::query()
            ->where('language', $language)
            ->pluck('word')
            ->toArray();

        if (empty($languageSuffixes)) {
            $languageSuffixes = GeneratorSuffix::query()
                ->where('language', 'english')
                ->pluck('word')
                ->toArray();
        }

        $numbers = GeneratorNumber::query()
            ->pluck('value')
            ->toArray();

        if (empty($numbers)) {
            $numbers = ['01', '07', '13'];
        }

        $parsedRequests = $this->parseOtherRequests($validated['otherRequests'] ?? '');

        $generated = [];
        $usedCustomWords = [];
        $safety = 0;

        $customQuota = !empty($customWords) ? 1 : 0;
        $customUsed = 0;

        while (count($generated) < 6 && $safety < 120) {
            $forceCustom = $customUsed < $customQuota;

            $tag = $this->generateSingleTag(
                $baseWords,
                $prefixes,
                $suffixGenderWords,
                $languageSuffixes,
                $numbers,
                $gender,
                $characterLimit,
                $customWords,
                $parsedRequests,
                $forceCustom,
                $usedCustomWords
            );

            if (
                mb_strlen($tag) >= 3 &&
                !in_array($tag, $generated, true)
            ) {
                if ($this->containsCustomWord($tag, $customWords)) {
                    $matchedCustomWord = $this->findMatchedCustomWord($tag, $customWords);

                    if ($matchedCustomWord !== null) {
                        $usedCustomWords[] = mb_strtolower($matchedCustomWord);
                    }

                    if ($forceCustom) {
                        $customUsed++;
                    }
                }

                $generated[] = $tag;
            }

            $safety++;
        }

        return response()->json([
            'tags' => $generated,
        ]);
    }

    private function generateSingleTag(
        array $baseWords,
        array $prefixes,
        array $suffixGenderWords,
        array $languageSuffixes,
        array $numbers,
        string $gender,
        int $characterLimit,
        array $customWords = [],
        array $parsedRequests = [],
        bool $forceCustom = false,
        array $usedCustomWords = []
    ): string {
        if (empty($baseWords)) {
            return 'Player' . rand(10, 99);
        }

        $prefix = '';
        $mainWord = '';
        $genderSuffix = '';
        $extraSuffix = '';
        $number = '';

        if ($gender !== 'any' && !empty($prefixes) && mt_rand(1, 100) > 75) {
            $prefix = $prefixes[array_rand($prefixes)];
        }

        $allowNumbers = !in_array('no_numbers', $parsedRequests, true);
        $preferShort = in_array('short', $parsedRequests, true);

        $dbWord = $baseWords[array_rand($baseWords)];
        $availableCustomWords = $this->getAvailableCustomWords($customWords, $usedCustomWords);

        if ($forceCustom && !empty($availableCustomWords)) {
            $customWord = $availableCustomWords[array_rand($availableCustomWords)];

            if (mt_rand(0, 1) === 1) {
                $mainWord = $this->combineWords($customWord, $dbWord);
            } else {
                $mainWord = $this->combineWords($dbWord, $customWord);
            }
        } else {
            if (!empty($availableCustomWords) && mt_rand(1, 100) > 85) {
                $customWord = $availableCustomWords[array_rand($availableCustomWords)];

                if (mt_rand(0, 1) === 1) {
                    $mainWord = $this->combineWords($customWord, $dbWord);
                } else {
                    $mainWord = $this->combineWords($dbWord, $customWord);
                }
            } else {
                $mainWord = $dbWord;
            }
        }

        if (
            $gender !== 'any' &&
            !empty($suffixGenderWords) &&
            mt_rand(1, 100) > 85
        ) {
            $genderSuffix = $suffixGenderWords[array_rand($suffixGenderWords)];
        }

        if (!$preferShort && mt_rand(1, 100) > 40) {
            if (mt_rand(0, 1) === 1 && !empty($languageSuffixes)) {
                $extraSuffix = $languageSuffixes[array_rand($languageSuffixes)];
            } elseif ($allowNumbers && !empty($numbers)) {
                $number = $numbers[array_rand($numbers)];
            }
        }

        $candidates = [
            $prefix . $mainWord . $genderSuffix . $extraSuffix . $number,
            $prefix . $mainWord . $genderSuffix . $extraSuffix,
            $prefix . $mainWord . $genderSuffix . $number,
            $prefix . $mainWord . $extraSuffix,
            $prefix . $mainWord . $number,
            $mainWord . $genderSuffix . $extraSuffix,
            $mainWord . $extraSuffix,
            $mainWord . $number,
            $prefix . $mainWord,
            $mainWord,
        ];

        foreach ($candidates as $candidate) {
            if (mb_strlen($candidate) <= $characterLimit) {
                return $candidate;
            }
        }

        return $this->smartTrim($mainWord, $characterLimit);
    }

    private function parseOtherRequests(string $input): array
    {
        $text = mb_strtolower(trim($input));
        $flags = [];

        if ($text === '') {
            return $flags;
        }

        if (
            str_contains($text, 'short') ||
            str_contains($text, 'brief') ||
            str_contains($text, 'small')
        ) {
            $flags[] = 'short';
        }

        if (
            str_contains($text, 'no numbers') ||
            str_contains($text, 'without numbers') ||
            str_contains($text, 'no number')
        ) {
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

        $available = array_values(array_filter($customWords, function ($word) use ($used) {
            return !in_array(mb_strtolower($word), $used, true);
        }));

        return !empty($available) ? $available : $customWords;
    }

    private function combineWords(string $first, string $second): string
    {
        $first = trim($first);
        $second = trim($second);

        if ($first === '') {
            return $second;
        }

        if ($second === '') {
            return $first;
        }

        $first = ucfirst($first);
        $second = ucfirst($second);

        return $first . $second;
    }

    private function smartTrim(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $parts = preg_split('/(?=[A-Z])|[_\-\s]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (!empty($parts)) {
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
        }

        return mb_substr($text, 0, $limit);
    }
}