<?php

namespace Database\Seeders;

use App\Models\Glossary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GlossarySeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
    [
        'term' => 'Achievement',
        'definition' => 'A digital reward earned by completing specific tasks or challenges in Xbox games. These showcase accomplishments on your profile.',
        'category' => 'Xbox Features',
    ],
    [
        'term' => 'AFK',
        'definition' => 'Away From Keyboard — indicates a player is temporarily inactive during gameplay.',
        'category' => 'Gaming Culture',
    ],
    [
        'term' => 'Avatar',
        'definition' => 'A customizable digital character that represents you on Xbox platforms and services.',
        'category' => 'Xbox Features',
    ],

    [
        'term' => 'Ban',
        'definition' => 'A restriction placed on accounts for violating Xbox policies, ranging from temporary suspensions to permanent bans.',
        'category' => 'Technical',
    ],
    [
        'term' => 'Beta',
        'definition' => 'A pre-release version of a game used for testing and feedback before official launch.',
        'category' => 'Technical',
    ],
    [
        'term' => 'Boosting',
        'definition' => 'When players help each other gain achievements, ranks, or stats in an unfair or artificial way.',
        'category' => 'Gaming Culture',
    ],

    [
        'term' => 'Clan',
        'definition' => 'A group of players who regularly play together under a shared name or identity.',
        'category' => 'Gaming Culture',
    ],
    [
        'term' => 'Cloud Gaming',
        'definition' => 'Streaming games directly from servers without needing to install them locally.',
        'category' => 'Xbox Features',
    ],
    [
        'term' => 'Cooldown',
        'definition' => 'A waiting period before an ability or action can be used again.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'DLC',
        'definition' => 'Downloadable Content — additional content added to a game after release.',
        'category' => 'Technical',
    ],
    [
        'term' => 'Dashboard',
        'definition' => 'The main interface of Xbox where users access games, apps, and settings.',
        'category' => 'Xbox Features',
    ],
    [
        'term' => 'Drop Rate',
        'definition' => 'The probability of receiving a specific item or reward in a game.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'ELO',
        'definition' => 'A ranking system used to measure player skill levels in competitive games.',
        'category' => 'Gaming Terms',
    ],
    [
        'term' => 'Emote',
        'definition' => 'A character animation or expression used to communicate in-game.',
        'category' => 'Gaming Culture',
    ],

    [
        'term' => 'FPS',
        'definition' => 'First-Person Shooter — a game genre played from the character’s viewpoint.',
        'category' => 'Gaming Terms',
    ],
    [
        'term' => 'Free-to-Play',
        'definition' => 'Games that can be played without purchase but may include in-game purchases.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Gamertag',
        'definition' => 'Your unique Xbox username used across Xbox Live and gaming platforms.',
        'category' => 'Naming',
    ],
    [
        'term' => 'Game Pass',
        'definition' => 'Microsoft’s subscription service offering access to a large library of games.',
        'category' => 'Xbox Features',
    ],
    [
        'term' => 'Grinding',
        'definition' => 'Repeating tasks in-game to gain XP, currency, or items.',
        'category' => 'Gaming Culture',
    ],

    [
        'term' => 'Hitbox',
        'definition' => 'The invisible area that determines whether a character or object is hit.',
        'category' => 'Technical',
    ],
    [
        'term' => 'HUD',
        'definition' => 'Heads-Up Display — the interface showing health, ammo, and other info.',
        'category' => 'Technical',
    ],

    [
        'term' => 'Input Lag',
        'definition' => 'Delay between player input and the game’s response.',
        'category' => 'Technical',
    ],
    [
        'term' => 'Inventory',
        'definition' => 'The collection of items a player carries in-game.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Joystick',
        'definition' => 'A control device used to interact with games on consoles.',
        'category' => 'Technical',
    ],

    [
        'term' => 'K/D Ratio',
        'definition' => 'Kill/Death ratio — a statistic showing player performance in combat games.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Lag',
        'definition' => 'Delay in gameplay caused by network or performance issues.',
        'category' => 'Technical',
    ],
    [
        'term' => 'Leaderboard',
        'definition' => 'A ranking system showing top players based on performance.',
        'category' => 'Xbox Features',
    ],

    [
        'term' => 'Multiplayer',
        'definition' => 'A game mode where multiple players play together online or locally.',
        'category' => 'Gaming Terms',
    ],
    [
        'term' => 'Microtransactions',
        'definition' => 'Small in-game purchases using real money.',
        'category' => 'Technical',
    ],

    [
        'term' => 'Noob',
        'definition' => 'A beginner or inexperienced player.',
        'category' => 'Gaming Culture',
    ],
    [
        'term' => 'NPC',
        'definition' => 'Non-Playable Character controlled by the game.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Open World',
        'definition' => 'A game design where players can freely explore a large environment.',
        'category' => 'Gaming Terms',
    ],
    [
        'term' => 'Online Multiplayer',
        'definition' => 'Playing games with others over the internet.',
        'category' => 'Xbox Features',
    ],

    [
        'term' => 'Ping',
        'definition' => 'A measure of internet latency affecting online gameplay responsiveness.',
        'category' => 'Technical',
    ],
    [
        'term' => 'Patch',
        'definition' => 'An update released to fix bugs or improve a game.',
        'category' => 'Technical',
    ],

    [
        'term' => 'Queue',
        'definition' => 'A waiting line system before joining a match or server.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Respawn',
        'definition' => 'The act of a player reappearing after being eliminated.',
        'category' => 'Gaming Terms',
    ],
    [
        'term' => 'RPG',
        'definition' => 'Role-Playing Game — a genre focused on character progression and story.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Server',
        'definition' => 'A system that hosts multiplayer games and connects players.',
        'category' => 'Technical',
    ],
    [
        'term' => 'Skin',
        'definition' => 'A cosmetic change to a character or item.',
        'category' => 'Gaming Culture',
    ],

    [
        'term' => 'Toxic',
        'definition' => 'Negative or harmful behavior in gaming communities.',
        'category' => 'Gaming Culture',
    ],
    [
        'term' => 'Tutorial',
        'definition' => 'A guided introduction to gameplay mechanics.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Update',
        'definition' => 'A release that improves or fixes a game.',
        'category' => 'Technical',
    ],

    [
        'term' => 'Voice Chat',
        'definition' => 'A feature allowing players to communicate using microphones.',
        'category' => 'Xbox Features',
    ],

    [
        'term' => 'Walkthrough',
        'definition' => 'A guide showing how to complete parts of a game.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Xbox Live',
        'definition' => 'Microsoft’s online gaming service enabling multiplayer, purchases, and social features.',
        'category' => 'Xbox Features',
    ],
    [
        'term' => 'XP',
        'definition' => 'Experience Points earned to level up and unlock rewards.',
        'category' => 'Gaming Culture',
    ],

    [
        'term' => 'Yield',
        'definition' => 'Resources or rewards gained from in-game actions.',
        'category' => 'Gaming Terms',
    ],

    [
        'term' => 'Zone',
        'definition' => 'A specific area or region within a game world.',
        'category' => 'Gaming Terms',
    ],
];

        foreach ($terms as $index => $item) {
            Glossary::updateOrCreate(
                ['slug' => Str::slug($item['term'])],
                [
                    'term' => $item['term'],
                    'definition' => $item['definition'],
                    'category' => $item['category'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}