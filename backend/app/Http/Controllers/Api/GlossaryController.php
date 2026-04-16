<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Glossary;
use Illuminate\Support\Str;

class GlossaryController extends Controller
{
    public function index()
    {
        $terms = Glossary::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('term')
            ->get();

        return response()->json(
            $terms->map(function ($item) {
                return [
                    'term' => $item->term,
                    'definition' => $item->definition,
                    'category' => $item->category,
                    'letter' => Str::upper(Str::substr($item->term, 0, 1)),
                ];
            })
        );
    }
}