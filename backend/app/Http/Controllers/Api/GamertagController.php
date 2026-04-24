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
            'gender'          => 'nullable|string|in:any,male,female,neutral',
            'theme'           => 'nullable|string|max:50',
            'language'        => 'nullable|string|max:50',
            'includeWords'    => 'nullable|string',
            'namePreference'  => 'nullable|string|in:default,short,no_numbers,short_no_numbers',

            // Keep this for backward compatibility if your frontend still sends otherRequests.
            'otherRequests'   => 'nullable|string',

            'characterLimit'  => 'nullable|integer|min:3|max:12',
        ]);

        $targetCount     = 6;
        $gender          = $validated['gender'] ?? 'any';
        $theme           = $validated['theme'] ?? 'gaming';
        $language        = $validated['language'] ?? 'english';
        $characterLimit  = $validated['characterLimit'] ?? 12;
        $namePreference  = $validated['namePreference'] ?? 'default';

        $customWords = collect(explode(',', $validated['includeWords'] ?? ''))
            ->map(fn ($word) => trim($word))
            ->filter(fn ($word) => $word !== '')
            ->unique(fn ($word) => mb_strtolower($word))
            ->values()
            ->toArray();

        $parsedRequests = $this->parseNamePreference(
            $namePreference,
            $validated['otherRequests'] ?? ''
        );

        $baseWords        = $this->fetchWords($language, $theme);
        $prefixes         = $gender !== 'any' ? $this->fetchGenderWords($language, $gender, 'prefix') : [];
        $suffixGender     = $gender !== 'any' ? $this->fetchGenderWords($language, $gender, 'suffix') : [];
        $languageSuffixes = $this->fetchSuffixes($language);
        $numbers          = GeneratorNumber::pluck('value')->toArray() ?: ['01', '07', '13', '99', '47'];

        $generated       = [];
        $usedCustomWords = [];
        $usedPatterns    = [];

        // If user adds include words, 4 out of 6 results will include the custom word.
        $customQuota = !empty($customWords) ? min(4, $targetCount) : 0;
        $customUsed  = 0;
        $safety      = 0;

        while (count($generated) < $targetCount && $safety < 250) {
            $forceCustom = $customUsed < $customQuota;

            // Alternate include word position:
            // Result 1 = custom word first
            // Result 2 = custom word last
            // Result 3 = custom word first
            // Result 4 = custom word last
            $customPosition = $customUsed % 2 === 0 ? 'first' : 'last';

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
                $customPosition,
                $usedCustomWords,
                $usedPatterns
            );

            $tagLower = mb_strtolower($tag);

            if (
                mb_strlen($tag) >= 3 &&
                !in_array($tagLower, array_map('mb_strtolower', $generated), true)
            ) {
                if ($forceCustom && $this->containsCustomWord($tag, $customWords)) {
                    $matched = $this->findMatchedCustomWord($tag, $customWords);

                    if ($matched !== null) {
                        $usedCustomWords[] = mb_strtolower($matched);
                    }

                    $customUsed++;
                }

                $generated[]    = $tag;
                $usedPatterns[] = $this->extractPattern($tag);
            }

            $safety++;
        }

        return response()->json([
            'tags' => $generated,
        ]);
    }

 public function options(): JsonResponse
{
    $themes = GeneratorWord::query()
        ->whereNotNull('theme')
        ->select('theme')
        ->distinct()
        ->orderBy('theme')
        ->pluck('theme')
        ->map(fn ($theme) => [
            'value' => $theme,
            'label' => $this->formatLabel($theme),
        ])
        ->unique('value')
        ->values();

    $themes->prepend([
        'value' => 'any',
        'label' => 'Any',
    ]);

    $languages = GeneratorWord::query()
        ->whereNotNull('language')
        ->select('language')
        ->distinct()
        ->orderBy('language')
        ->pluck('language')
        ->map(fn ($language) => [
            'value' => $language,
            'label' => $this->formatLanguageLabel($language),
        ])
        ->unique('value')
        ->values();

    return response()->json([
        'themes' => $themes,
        'languages' => $languages,
    ]);
}
  private function fetchWords(string $language, string $theme): array
{
    $query = GeneratorWord::query()
        ->where('language', $language);

    if ($theme !== 'any') {
        $query->where('theme', $theme);
    }

    $words = $query->pluck('word')->toArray();

    if (empty($words)) {
        $fallback = GeneratorWord::query()
            ->where('language', 'english');

        if ($theme !== 'any') {
            $fallback->where('theme', $theme);
        }

        $words = $fallback->pluck('word')->toArray();
    }

    return array_values(array_unique($words));
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

        return array_values(array_unique($words));
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

        return array_values(array_unique($suffixes));
    }

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
        string $customPosition,
        array $usedCustomWords,
        array $usedPatterns
    ): string {
        if (empty($baseWords)) {
            return 'Player' . rand(10, 99);
        }

        $allowNumbers = !in_array('no_numbers', $parsedRequests, true);
        $preferShort  = in_array('short', $parsedRequests, true);

        $dbWord = $baseWords[array_rand($baseWords)];

        if ($forceCustom && !empty($customWords)) {
            $availableCustomWords = $this->getAvailableCustomWords($customWords, $usedCustomWords);

            return $this->buildIncludedWordTag(
                $availableCustomWords[array_rand($availableCustomWords)],
                $dbWord,
                $characterLimit,
                $customPosition
            );
        }

        $mainWord = $dbWord;

        $prefix = ($gender !== 'any' && !empty($prefixes) && mt_rand(1, 100) > 75)
            ? $prefixes[array_rand($prefixes)]
            : '';

        $genderSuffix = ($gender !== 'any' && !empty($suffixGenderWords) && mt_rand(1, 100) > 85)
            ? $suffixGenderWords[array_rand($suffixGenderWords)]
            : '';

        $extraSuffix = '';
        $number      = '';

        if (!$preferShort && mt_rand(1, 100) > 40) {
            $useNumber = $allowNumbers && !empty($numbers) && mt_rand(0, 1) === 1;
            $useSuffix = !empty($languageSuffixes) && mt_rand(0, 1) === 1;

            $pattern = ($useNumber ? 'N' : '') . ($useSuffix ? 'S' : '');

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

    private function buildIncludedWordTag(
        string $customWord,
        string $dbWord,
        int $characterLimit,
        string $position
    ): string {
        $customWord = ucfirst(trim($customWord));
        $dbWord     = ucfirst(trim($dbWord));

        if ($customWord === '') {
            return $this->smartTrim($dbWord, $characterLimit);
        }

        if (mb_strlen($customWord) >= $characterLimit) {
            return mb_substr($customWord, 0, $characterLimit);
        }

        $remainingLength = $characterLimit - mb_strlen($customWord);
        $trimmedDbWord   = mb_substr($dbWord, 0, $remainingLength);

        if ($position === 'last') {
            return $customWord === ''
                ? $this->smartTrim($dbWord, $characterLimit)
                : $trimmedDbWord . $customWord;
        }

        return $customWord . $trimmedDbWord;
    }

    private function parseNamePreference(string $namePreference, string $legacyOtherRequests = ''): array
    {
        $flags = [];

        if (in_array($namePreference, ['short', 'short_no_numbers'], true)) {
            $flags[] = 'short';
        }

        if (in_array($namePreference, ['no_numbers', 'short_no_numbers'], true)) {
            $flags[] = 'no_numbers';
        }

        // Backward compatibility for old otherRequests textarea.
        $legacyText = mb_strtolower(trim($legacyOtherRequests));

        if ($legacyText !== '') {
            if (str_contains($legacyText, 'short') || str_contains($legacyText, 'brief') || str_contains($legacyText, 'small')) {
                $flags[] = 'short';
            }

            if (str_contains($legacyText, 'no number') || str_contains($legacyText, 'without number')) {
                $flags[] = 'no_numbers';
            }
        }

        return array_values(array_unique($flags));
    }

    private function extractPattern(string $tag): string
    {
        $hasNumber = preg_match('/\d/', $tag);
        $wordCount = preg_match_all('/[A-Z][a-z]+/', $tag);

        return ($hasNumber ? 'N' : '') . ($wordCount > 1 ? 'M' : 'S');
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

        $available = array_values(array_filter(
            $customWords,
            fn ($word) => !in_array(mb_strtolower($word), $used, true)
        ));

        return !empty($available) ? $available : $customWords;
    }

    private function smartTrim(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $parts = preg_split('/(?=[A-Z])|[_\-\s]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

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

    private function formatLabel(string $value): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $value));
    }

    private function formatLanguageLabel(string $language): string
    {
        return match ($language) {
            'english' => 'English',
            'spanish' => 'Spanish',
            'portuguese' => 'Portuguese',
            'french' => 'French',
            'german' => 'German',
            'italian' => 'Italian',
            'russian' => 'Russian',
            'japanese' => 'Japanese',
            'korean' => 'Korean',
            'hindi' => 'Hindi',
            'arabic' => 'Arabic',
            'turkish' => 'Turkish',
            'chinese' => 'Chinese',
            'polish' => 'Polish',
            'indonesian' => 'Indonesian',
            default => $this->formatLabel($language),
        };
    }
}