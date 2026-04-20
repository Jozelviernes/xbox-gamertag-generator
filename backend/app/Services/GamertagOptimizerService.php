<?php

namespace App\Services;

use App\Models\GeneratorNumber;
use App\Models\GeneratorWord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GamertagOptimizerService
{
    protected array $config;
    protected array $tierCache = [];

    public function __construct()
    {
        $this->config = config('gamertag-optimizer', []);
    }

    public function optimize(string $input, array $options = []): array
    {
        $options = $this->normalizeOptions($options);

        $cleanedInput = $this->cleanRawInput($input);
        [$words, $numbers] = $this->extractTokens($cleanedInput);

        if (empty($words) && !empty($cleanedInput)) {
            $fallback = preg_replace('/[^A-Za-z]/', '', $cleanedInput);

            if ($fallback !== '') {
                $words = [Str::lower($fallback)];
            }
        }

        if (empty($words)) {
            $words = ['player'];
        }

        $normalized = $this->formatWords($words, true);
        $analysis = $this->analyzeGamertag($input, $normalized, $words, $numbers);

        $cleaned = [];
        $shortened = [];
        $rewritten = [];

        if (!$analysis['is_good']) {
            $cleaned = array_slice(
                $this->generateCleanedVersions($words, $numbers, $options),
                0,
                3
            );

            $shortened = array_slice(
                $this->generateShortenedVersions($words, $numbers, $options),
                0,
                3
            );

            $rewritten = array_slice(
                $this->generateRewrittenVersions($words, $numbers, $options),
                0,
                3
            );
        }

        return [
            'input'      => $input,
            'normalized' => $normalized,
            'tier'       => $analysis['tier'],
            'is_good'    => $analysis['is_good'],
            'message'    => $analysis['message'],
            'issues'     => $analysis['issues'],
            'shortened'  => $shortened,
            'rewritten'  => $rewritten,
            'cleaned'    => $cleaned,
        ];
    }

    protected function normalizeOptions(array $options): array
    {
        $limits = $this->config['limits'] ?? [];

        return [
            'style'        => $options['style'] ?? 'any',
            'max_length'   => max(
                4,
                min((int) ($options['max_length'] ?? ($limits['preferred_max'] ?? 12)), 12)
            ),
            'keep_numbers' => (bool) ($options['keep_numbers'] ?? false),
            'keep_meaning' => (bool) ($options['keep_meaning'] ?? true),
            'result_max'   => 3,
        ];
    }

    protected function analyzeGamertag(string $input, string $normalized, array $words, array $numbers): array
    {
        $issues = [];
        $plainInput = trim($input);
        $plainNormalized = str_replace(' ', '', $normalized);
        $tier = $this->gamertagValueTier($plainNormalized);

        if (preg_match('/^(xX|Xx|xx|XX)|((xX|Xx|xx|XX))$/', $plainInput)) {
            $issues[] = 'Has old-style wrapper formatting';
        }

        if (preg_match('/[_\-.]/', $plainInput)) {
            $issues[] = 'Has separators or symbols';
        }

        if (preg_match('/[^A-Za-z0-9_\-.\s]/', $plainInput)) {
            $issues[] = 'Has special characters';
        }

        if (preg_match('/(.)\1{2,}/i', $plainInput)) {
            $issues[] = 'Has repeated spammy characters';
        }

        if ($this->hasRepeatedParts($plainNormalized)) {
            $issues[] = 'Has repeated word parts';
        }

        if (count($numbers) > 0 && strlen(implode('', $numbers)) >= 2) {
            $issues[] = 'Has numbers';
        }

        if (mb_strlen($plainNormalized) > 12) {
            $issues[] = 'Too long for a clean Xbox-style tag';
        }

        if ($tier === 'low_value') {
            $issues[] = 'Matches a low-value style';
        }

        $isCleanFormat =
            !preg_match('/^(xX|Xx|xx|XX)|((xX|Xx|xx|XX))$/', $plainInput) &&
            !preg_match('/[_\-.]/', $plainInput) &&
            !preg_match('/[^A-Za-z0-9\s]/', $plainInput) &&
            !preg_match('/(.)\1{2,}/i', $plainInput) &&
            !$this->hasRepeatedParts($plainNormalized) &&
            mb_strlen($plainNormalized) <= 12 &&
            strlen(implode('', $numbers)) <= 1;

        $isGoodTier = in_array($tier, ['legendary', 'rare', 'uncommon', 'common'], true);

        $isGood = false;

        if ($tier === 'low_value') {
            $isGood = false;
        } elseif ($isGoodTier && $isCleanFormat) {
            $isGood = true;
        } elseif ($tier === null && $isCleanFormat && mb_strlen($plainNormalized) >= 3) {
            $isGood = true;
        }

        $message = $isGood
            ? 'This gamertag already looks clean and readable.'
            : 'This gamertag can be improved.';

        return [
            'tier'    => $tier,
            'is_good' => $isGood,
            'message' => $message,
            'issues'  => $issues,
        ];
    }

    protected function hasRepeatedParts(string $value): bool
    {
        $value = Str::lower($value);

        if (strlen($value) < 6) {
            return false;
        }

        $length = strlen($value);

        for ($size = 2; $size <= intdiv($length, 2); $size++) {
            $first = substr($value, 0, $size);
            $second = substr($value, $size, $size);

            if ($first !== '' && $first === $second) {
                return true;
            }
        }

        return false;
    }

    protected function cleanRawInput(string $value): string
    {
        $value = trim($value);

        $value = preg_replace('/^(?:x+x*|_+|-+|\.+)+/i', '', $value);
        $value = preg_replace('/(?:x+x*|_+|-+|\.+)+$/i', '', $value);

        $value = preg_replace('/^(xx|xX|Xx|XX)+/i', '', $value);
        $value = preg_replace('/(xx|xX|Xx|XX)+$/i', '', $value);

        $safeReplace = [
            '@' => 'a',
            '$' => 's',
            '!' => 'i',
        ];

        foreach ($safeReplace as $from => $to) {
            $value = str_replace($from, $to, $value);
        }

        foreach (($this->config['cleanup']['separators'] ?? []) as $separator) {
            $value = str_replace($separator, ' ', $value);
        }

        foreach (($this->config['cleanup']['strip_symbols'] ?? []) as $symbol) {
            $value = str_replace($symbol, '', $value);
        }

        $value = preg_replace('/(?<=\p{Ll})(?=\p{Lu})/u', ' ', $value);
        $value = preg_replace('/(?<=\D)(?=\d)|(?<=\d)(?=\D)/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);
        $value = trim($value);

        $value = preg_replace('/^(xx|xX|Xx|XX)\s*/i', '', $value);
        $value = preg_replace('/\s*(xx|xX|Xx|XX)$/i', '', $value);

        return trim($value);
    }

    protected function extractTokens(string $value): array
    {
        $parts = preg_split('/\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY);

        $words = [];
        $numbers = [];

        foreach ($parts as $part) {
            $part = preg_replace('/[^A-Za-z0-9]/', '', $part);

            if ($part === '') {
                continue;
            }

            if (ctype_digit($part)) {
                $numbers[] = $part;
                continue;
            }

            $lower = Str::lower($part);

            if (in_array($lower, $this->config['noise_tokens'] ?? [], true)) {
                continue;
            }

            $words[] = $lower;
        }

        return [array_values($words), array_values($numbers)];
    }

    protected function strongestWords(array $words): array
    {
        $strong = array_values(array_filter(
            $words,
            fn ($word) => !in_array($word, $this->config['weak_words'] ?? [], true)
        ));

        return !empty($strong) ? $strong : $words;
    }

    protected function generateCleanedVersions(array $words, array $numbers, array $options): array
    {
        $variants = [];
        $max = $options['max_length'];
        $titleWords = $this->titleWords($words);

        $joined = implode('', $titleWords);
        $spaced = implode(' ', $titleWords);

        $variants[] = $joined;

        if (count($titleWords) >= 2) {
            $variants[] = $spaced;
            $variants[] = $titleWords[0] . $titleWords[1];
        }

        if ($options['keep_numbers'] && !empty($numbers)) {
            $variants[] = $this->fitSuffix($joined, $this->bestNumber($numbers), $max);
        }

        return $this->finalizeCandidates($variants, $max, true, 3);
    }

    protected function generateShortenedVersions(array $words, array $numbers, array $options): array
    {
        $variants = [];
        $max = $options['max_length'];
        $strong = $this->strongestWords($words);
        $titleWords = $this->titleWords($strong);

        if (count($titleWords) === 1) {
            $word = $titleWords[0];

            if (mb_strlen($word) > 4) {
                $variants[] = mb_substr($word, 0, min(4, $max));
                $variants[] = mb_substr($word, 0, min(5, $max));
                $variants[] = mb_substr($word, 0, min(6, $max));
            }

            if ($options['keep_numbers'] && !empty($numbers)) {
                $variants[] = $this->fitSuffix(
                    mb_substr($word, 0, max(1, $max - mb_strlen($this->bestNumber($numbers)))),
                    $this->bestNumber($numbers),
                    $max
                );
            }
        }

        if (count($titleWords) >= 2) {
            $a = $titleWords[0];
            $b = $titleWords[1];

            $variants[] = mb_substr($a, 0, min(4, $max)) . mb_substr($b, 0, min(3, max(1, $max - 4)));
            $variants[] = mb_substr($a, 0, 1) . mb_substr($b, 0, min(4, max(1, $max - 1)));
            $variants[] = mb_substr($b, 0, min(3, $max)) . mb_substr($a, 0, min(4, max(1, $max - 3)));
        }

        return $this->finalizeCandidates($variants, $max, false, 3);
    }

    protected function generateRewrittenVersions(array $words, array $numbers, array $options): array
    {
        $variants = [];
        $max = $options['max_length'];
        $strong = $this->strongestWords($words);
        $seedWords = array_slice($strong, 0, 2);
        $original = Str::lower($this->formatWords($words, false));

        if (!$options['keep_meaning'] || empty($seedWords)) {
            return [];
        }

        $groups = [];

        foreach ($seedWords as $word) {
            $groups[] = array_slice($this->synonymsFor($word), 0, 6);
        }

        if (count($groups) >= 2) {
            foreach ($groups[0] as $first) {
                foreach ($groups[1] as $second) {
                    $candidate = $this->formatWords([$first, $second], false);

                    if (
                        mb_strlen($candidate) <= $max &&
                        Str::lower($candidate) !== $original
                    ) {
                        $variants[] = $candidate;
                    }

                    if (count($variants) >= 6) {
                        break 2;
                    }
                }
            }
        } elseif (count($groups) === 1) {
            foreach ($groups[0] as $first) {
                $candidate = $this->formatWords([$first], false);

                if (
                    mb_strlen($candidate) <= $max &&
                    Str::lower($candidate) !== $original
                ) {
                    $variants[] = $candidate;
                }
            }

            if (count($variants) < 3) {
                $dbCores = $this->dbCoreWords(6);
                $baseWords = array_slice($groups[0], 0, 3);

                foreach ($baseWords as $first) {
                    $firstWord = $this->formatWord($first);

                    foreach ($dbCores as $core) {
                        if (Str::lower($firstWord) === Str::lower($core)) {
                            continue;
                        }

                        $candidate = $firstWord . $core;

                        if (
                            mb_strlen($candidate) <= $max &&
                            Str::lower($candidate) !== $original
                        ) {
                            $variants[] = $candidate;
                        }

                        if (count($variants) >= 6) {
                            break 2;
                        }
                    }
                }
            }
        }

        if ($options['keep_numbers'] && !empty($variants)) {
            $dbNumbers = $this->dbNumbers(3);

            foreach (array_slice($variants, 0, 2) as $base) {
                foreach ($dbNumbers as $number) {
                    $variants[] = $this->fitSuffix($base, $number, $max);
                }
            }
        }

        return $this->finalizeCandidates($variants, $max, false, 3);
    }

    protected function synonymsFor(string $word): array
    {
        $map = $this->config['word_map'] ?? [];
        $word = Str::lower($word);

        if (isset($map[$word]) && is_array($map[$word])) {
            return array_values(array_unique(array_merge([$word], $map[$word])));
        }

        return [$word];
    }

    protected function bestNumber(array $numbers): string
    {
        $safe = $this->config['safe_numbers'] ?? [];

        foreach ($numbers as $number) {
            if (in_array($number, $safe, true)) {
                return $number;
            }
        }

        return $numbers[0] ?? '';
    }

    protected function titleWords(array $words): array
    {
        return array_values(array_map(fn ($word) => $this->formatWord($word), $words));
    }

    protected function formatWords(array $words, bool $withSpaces = false): string
    {
        $formatted = array_values(array_filter(array_map(
            fn ($word) => $this->formatWord($word),
            $words
        )));

        return $withSpaces ? implode(' ', $formatted) : implode('', $formatted);
    }

    protected function formatWord(string $word): string
    {
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $word);

        if ($clean === '') {
            return '';
        }

        if ($clean !== strtolower($clean) && $clean !== strtoupper($clean)) {
            return ucfirst($clean);
        }

        return Str::studly($clean);
    }

    protected function fitSuffix(string $base, string $suffix, int $max): string
    {
        $base = preg_replace('/\s+/', '', $base);
        $suffix = preg_replace('/\s+/', '', $suffix);

        if ($suffix === '') {
            return mb_substr($base, 0, $max);
        }

        $allowedBaseLength = max(1, $max - mb_strlen($suffix));
        $base = mb_substr($base, 0, $allowedBaseLength);

        return $base . $suffix;
    }

    protected function finalizeCandidates(array $variants, int $maxLength, bool $allowSpaces, int $limit = 3): array
    {
        $unique = [];

        foreach ($variants as $variant) {
            if (!is_string($variant)) {
                continue;
            }

            $candidate = trim($variant);

            if ($candidate === '') {
                continue;
            }

            if (!$allowSpaces) {
                $candidate = preg_replace('/\s+/', '', $candidate);
            } else {
                $candidate = preg_replace('/\s+/', ' ', $candidate);
            }

            if (!$this->isValidCandidate($candidate, $maxLength, $allowSpaces)) {
                continue;
            }

            $key = $allowSpaces
                ? Str::lower($candidate)
                : Str::lower(str_replace(' ', '', $candidate));

            $unique[$key] = $candidate;
        }

        $items = array_values($unique);

        usort($items, function (string $a, string $b): int {
            $scoreA = $this->scoreCandidate($a);
            $scoreB = $this->scoreCandidate($b);

            if ($scoreA === $scoreB) {
                return mb_strlen($a) <=> mb_strlen($b);
            }

            return $scoreB <=> $scoreA;
        });

        return array_slice($items, 0, $limit);
    }

    protected function isValidCandidate(string $candidate, int $maxLength, bool $allowSpaces): bool
    {
        if ($candidate === '') {
            return false;
        }

        if (!$allowSpaces && str_contains($candidate, ' ')) {
            return false;
        }

        if (!preg_match($allowSpaces ? '/^[A-Za-z0-9 ]+$/' : '/^[A-Za-z0-9]+$/', $candidate)) {
            return false;
        }

        if (mb_strlen($candidate) > $maxLength) {
            return false;
        }

        if (mb_strlen(str_replace(' ', '', $candidate)) < 3) {
            return false;
        }

        foreach (($this->config['quality']['bad_sequences'] ?? []) as $bad) {
            if ($bad !== '' && stripos($candidate, $bad) !== false) {
                return false;
            }
        }

        $tier = $this->gamertagValueTier(str_replace(' ', '', $candidate));

        if ($tier === 'low_value') {
            return false;
        }

        return true;
    }

    protected function scoreCandidate(string $candidate): int
    {
        $score = 100;
        $plain = str_replace(' ', '', $candidate);
        $length = mb_strlen($plain);

        $score -= abs($length - 8) * 4;

        if (preg_match('/\d{4,}/', $candidate)) {
            $score -= 25;
        }

        if (preg_match('/\d{3}$/', $candidate)) {
            $score -= 10;
        }

        if (!str_contains($candidate, ' ') && preg_match('/^[A-Za-z]+$/', $plain) && mb_strlen($plain) <= 8) {
            $score += 8;
        }

        $tier = $this->gamertagValueTier($plain);

        if ($tier === 'legendary') {
            $score += 40;
        } elseif ($tier === 'rare') {
            $score += 25;
        } elseif ($tier === 'uncommon') {
            $score += 10;
        } elseif ($tier === 'common') {
            $score += 2;
        } elseif ($tier === 'low_value') {
            $score -= 50;
        }

        return $score;
    }

    protected function gamertagValueTier(string $candidate): ?string
    {
        $key = Str::lower($candidate);

        if (array_key_exists($key, $this->tierCache)) {
            return $this->tierCache[$key];
        }

        return $this->tierCache[$key] = DB::table('gamertag_values')
            ->whereRaw('LOWER(gamertag) = ?', [$key])
            ->value('tier');
    }

    protected function dbCoreWords(int $limit = 10): array
    {
        return GeneratorWord::query()
            ->select('word')
            ->whereRaw('CHAR_LENGTH(word) BETWEEN 3 AND 8')
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('word')
            ->filter(fn ($word) => is_string($word) && preg_match('/^[A-Za-z0-9]+$/', $word))
            ->map(fn ($word) => $this->formatWord($word))
            ->unique()
            ->values()
            ->all();
    }

    protected function dbNumbers(int $limit = 3): array
    {
        return GeneratorNumber::query()
            ->select('value')
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('value')
            ->map(fn ($value) => (string) $value)
            ->filter(fn ($value) => preg_match('/^\d{1,3}$/', $value))
            ->unique()
            ->values()
            ->all();
    }
}