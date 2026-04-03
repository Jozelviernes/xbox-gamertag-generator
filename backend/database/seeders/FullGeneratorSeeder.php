<?php

namespace Database\Seeders;

use App\Models\GeneratorGenderWord;
use App\Models\GeneratorNumber;
use App\Models\GeneratorSuffix;
use App\Models\GeneratorWord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FullGeneratorSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/generator-data.json');

        if (!File::exists($path)) {
            throw new \RuntimeException("generator-data.json not found at: {$path}");
        }

        $data = json_decode(File::get($path), true);

        if (!is_array($data)) {
            throw new \RuntimeException("Invalid generator-data.json format.");
        }

        DB::transaction(function () use ($data) {
            GeneratorWord::query()->delete();
            GeneratorGenderWord::query()->delete();
            GeneratorSuffix::query()->delete();
            GeneratorNumber::query()->delete();

            $wordRows = [];
            $genderRows = [];
            $suffixRows = [];
            $numberRows = [];

            $now = now();

            foreach (($data['languageWordSets'] ?? []) as $language => $themes) {
                foreach ($themes as $theme => $words) {
                    foreach ($words as $word) {
                        $wordRows[] = [
                            'word' => $word,
                            'language' => $language,
                            'theme' => $theme,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            foreach (($data['languageGenderWords'] ?? []) as $language => $genderSets) {
                foreach ($genderSets as $gender => $positions) {
                    foreach (($positions['prefixes'] ?? []) as $word) {
                        $genderRows[] = [
                            'word' => $word,
                            'language' => $language,
                            'gender' => $gender,
                            'position' => 'prefix',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    foreach (($positions['suffixes'] ?? []) as $word) {
                        $genderRows[] = [
                            'word' => $word,
                            'language' => $language,
                            'gender' => $gender,
                            'position' => 'suffix',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            foreach (($data['languageSuffixes'] ?? []) as $language => $suffixes) {
                foreach ($suffixes as $word) {
                    $suffixRows[] = [
                        'word' => $word,
                        'language' => $language,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (($data['numbers'] ?? []) as $value) {
                $numberRows[] = [
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($wordRows, 1000) as $chunk) {
                GeneratorWord::insert($chunk);
            }

            foreach (array_chunk($genderRows, 1000) as $chunk) {
                GeneratorGenderWord::insert($chunk);
            }

            foreach (array_chunk($suffixRows, 1000) as $chunk) {
                GeneratorSuffix::insert($chunk);
            }

            foreach (array_chunk($numberRows, 1000) as $chunk) {
                GeneratorNumber::insert($chunk);
            }
        });
    }
}