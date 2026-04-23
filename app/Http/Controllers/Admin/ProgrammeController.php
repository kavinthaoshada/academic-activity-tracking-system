<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    public function index()
    {
        $programmes = Programme::withCount('batches')->latest()->paginate(15);
        return view('admin.programmes.index', compact('programmes'));
    }

    public function create()
    {
        return view('admin.programmes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:20', 'unique:programmes,code'],
            'total_weeks' => ['required', 'integer', 'min:1', 'max:52'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Programme::create($validated);

        return redirect()->route('admin.programmes.index')
            ->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $programme->loadCount('batches');
        $programme->load(['batches.courses']);
        return view('admin.programmes.show', compact('programme'));
    }

    public function edit(Programme $programme)
    {
        return view('admin.programmes.edit', compact('programme'));
    }

    public function update(Request $request, Programme $programme)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:20', "unique:programmes,code,{$programme->id}"],
            'total_weeks' => ['required', 'integer', 'min:1', 'max:52'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $programme->update($validated);

        return redirect()->route('admin.programmes.index')
            ->with('success', 'Programme updated successfully.');
    }

    public function destroy(Programme $programme)
    {
        if ($programme->batches()->exists()) {
            return back()->with('error', 'Cannot delete a programme that has batches. Deactivate it instead.');
        }

        $programme->delete();

        return redirect()->route('admin.programmes.index')
            ->with('success', 'Programme deleted successfully.');
    }
}