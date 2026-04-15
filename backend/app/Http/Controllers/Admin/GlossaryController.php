<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Glossary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GlossaryController extends Controller
{
    public function index()
    {
        $glossaries = Glossary::orderBy('sort_order')
            ->orderBy('term')
            ->paginate(10);

        return view('admin.glossaries.index', compact('glossaries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:255'],
            'definition' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['term']);

        if (Glossary::where('slug', $slug)->exists()) {
            return back()
                ->withErrors(['term' => 'The term already exists.'])
                ->withInput();
        }

        Glossary::create([
            'term' => $validated['term'],
            'slug' => $slug,
            'definition' => $validated['definition'],
            'category' => $validated['category'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.glossaries.index')
            ->with('success', 'Glossary term created successfully.');
    }

    public function update(Request $request, Glossary $glossary)
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:255'],
            'definition' => ['required', 'string'],
            'category' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['term']);

        if (
            Glossary::where('slug', $slug)
                ->where('id', '!=', $glossary->id)
                ->exists()
        ) {
            return back()
                ->withErrors(['term' => 'The term already exists.'])
                ->withInput();
        }

        $glossary->update([
            'term' => $validated['term'],
            'slug' => $slug,
            'definition' => $validated['definition'],
            'category' => $validated['category'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.glossaries.index')
            ->with('success', 'Glossary term updated successfully.');
    }

    public function destroy(Glossary $glossary)
    {
        $glossary->delete();

        return redirect()
            ->route('admin.glossaries.index')
            ->with('success', 'Glossary term deleted successfully.');
    }
}