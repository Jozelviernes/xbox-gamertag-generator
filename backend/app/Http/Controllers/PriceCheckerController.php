<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceCheckerController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'gamertag' => 'required|string|min:1|max:12',
        ]);

        $gamertag  = trim($request->input('gamertag'));
        $lower     = strtolower($gamertag);
        $length    = strlen($gamertag);
        $score     = 0;
        $breakdown = [];

        // ── 1. EXACT MATCH IN gamertag_values TABLE ───────────────
        $exactMatch = DB::table('gamertag_values')
            ->whereRaw('LOWER(gamertag) = ?', [$lower])
            ->first();

        if ($exactMatch) {
            return response()->json(
                $this->buildFromTier($gamertag, $exactMatch->tier, 'Exact match found in database')
            );
        }

        // ── 2. NO EXACT MATCH — score dynamically ─────────────────

        // LENGTH
        if ($length <= 3) {
            $score += 35;
            $breakdown[] = ['label' => 'Length (1–3 chars)',  'points' => 35, 'note' => 'Extremely rare'];
        } elseif ($length <= 5) {
            $score += 25;
            $breakdown[] = ['label' => 'Length (4–5 chars)',  'points' => 25, 'note' => 'Very short'];
        } elseif ($length <= 8) {
            $score += 12;
            $breakdown[] = ['label' => 'Length (6–8 chars)',  'points' => 12, 'note' => 'Average length'];
        } else {
            $score += 3;
            $breakdown[] = ['label' => 'Length (9–12 chars)', 'points' => 3,  'note' => 'Common length'];
        }

        // CHARACTER TYPE
        if (preg_match('/^[a-zA-Z]+$/', $gamertag)) {
            $score += 15;
            $breakdown[] = ['label' => 'Letters only',        'points' => 15, 'note' => 'Clean, no numbers'];
        } elseif (preg_match('/^[a-zA-Z0-9 ]+$/', $gamertag)) {
            $score += 7;
            $breakdown[] = ['label' => 'Letters + numbers',   'points' => 7,  'note' => 'Slightly reduces value'];
        } else {
            $score += 1;
            $breakdown[] = ['label' => 'Special characters',  'points' => 1,  'note' => 'Reduces value'];
        }

        // STYLE PENALTIES
        if (preg_match('/^xX|Xx$|_{2,}/i', $gamertag)) {
            $score -= 15;
            $breakdown[] = ['label' => 'Old-style pattern',      'points' => -15, 'note' => 'xX or __ style — outdated'];
        }

        if (preg_match('/(.)\1{2,}/', $gamertag)) {
            $score -= 8;
            $breakdown[] = ['label' => 'Repeated characters',    'points' => -8,  'note' => 'e.g. "aaa"'];
        }

        if (preg_match('/\d{3,}/', $gamertag)) {
            $score -= 8;
            $breakdown[] = ['label' => '3+ consecutive numbers', 'points' => -8,  'note' => 'e.g. "123" or "999"'];
        }

        // ── 3. SIMILAR TIER MATCH ─────────────────────────────────
        $containedInLegendary = $length <= 4 && DB::table('gamertag_values')
            ->where('tier', 'legendary')
            ->whereRaw('LOWER(gamertag) LIKE CONCAT("%", ?, "%")', [$lower])
            ->exists();

        $startsRareTag = DB::table('gamertag_values')
            ->where('tier', 'rare')
            ->whereRaw('LOWER(gamertag) LIKE CONCAT(?, "%")', [$lower])
            ->exists();

        if ($containedInLegendary) {
            $score += 15;
            $breakdown[] = ['label' => 'Core of a legendary tag', 'points' => 15, 'note' => 'Short word found inside legendary names'];
        } elseif ($startsRareTag && $length <= 6) {
            $score += 8;
            $breakdown[] = ['label' => 'Starts a rare tag',       'points' => 8,  'note' => 'Found at the start of rare-tier names'];
        }

        // ── 4. GENERATOR WORDS CHECK ──────────────────────────────
        // If word is in generator_words = commonly mass-generated
        // = less unique = lower market value = PENALTY
        $wordMatch = DB::table('generator_words')
            ->whereRaw('LOWER(word) = ?', [$lower])
            ->first();

        if ($wordMatch) {
            $theme = strtolower($wordMatch->theme);
            $score -= 10;
            $breakdown[] = [
                'label'  => 'Common generator word',
                'points' => -10,
                'note'   => ucfirst($theme) . ' theme — used in generator, reduces uniqueness',
            ];
        } else {
            // Not in generator = more unique = small bonus
            $score += 5;
            $breakdown[] = [
                'label'  => 'Unique word',
                'points' => 5,
                'note'   => 'Not a common generator word — adds uniqueness',
            ];
        }

        // ── 5. SINGLE CLEAN WORD BONUS ────────────────────────────
        if (!str_contains($gamertag, ' ') && preg_match('/^[a-zA-Z]+$/', $gamertag)) {
            $score += 5;
            $breakdown[] = ['label' => 'Single clean word', 'points' => 5, 'note' => 'No spaces or numbers'];
        }

        // ── 6. CLAMP 0–100 ────────────────────────────────────────
        $score = max(0, min(100, $score));

        // ── 7. MAP SCORE TO TIER ──────────────────────────────────
        if ($score >= 75) {
            $tier = 'legendary';
        } elseif ($score >= 55) {
            $tier = 'rare';
        } elseif ($score >= 38) {
            $tier = 'uncommon';
        } elseif ($score >= 22) {
            $tier = 'common';
        } else {
            $tier = 'low_value';
        }

        $result              = $this->buildFromTier($gamertag, $tier);
        $result['score']     = $score;
        $result['breakdown'] = $breakdown;

        return response()->json($result);
    }

    // ── Tier → price range + emoji ────────────────────────────────
    private function buildFromTier(string $gamertag, string $tier, string $note = ''): array
    {
        $map = [
            'legendary' => [
                'label'      => 'Legendary',
                'priceRange' => '$50 – $200+',
                'emoji'      => '👑',
                'score'      => 90,
            ],
            'rare' => [
                'label'      => 'Rare',
                'priceRange' => '$10 – $49',
                'emoji'      => '💎',
                'score'      => 65,
            ],
            'uncommon' => [
                'label'      => 'Uncommon',
                'priceRange' => '$3 – $9',
                'emoji'      => '🔷',
                'score'      => 45,
            ],
            'common' => [
                'label'      => 'Common',
                'priceRange' => '$1 – $2',
                'emoji'      => '🟩',
                'score'      => 28,
            ],
            'low_value' => [
                'label'      => 'Low Value',
                'priceRange' => 'No market value',
                'emoji'      => '⬜',
                'score'      => 10,
            ],
        ];

        $data = $map[$tier] ?? $map['low_value'];

        return [
            'gamertag'   => $gamertag,
            'score'      => $data['score'],
            'tier'       => $data['label'],
            'emoji'      => $data['emoji'],
            'priceRange' => $data['priceRange'],
            'breakdown'  => $note
                ? [['label' => '✅ Database match', 'points' => $data['score'], 'note' => $note]]
                : [],
        ];
    }
}