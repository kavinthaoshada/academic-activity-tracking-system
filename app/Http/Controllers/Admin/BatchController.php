<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Programme;
use App\Services\AcademicWeekService;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function __construct(private AcademicWeekService $weekService) {}

    public function index()
    {
        $batches = Batch::with('programme')
            ->withCount('courses')
            ->latest()
            ->paginate(15);

        return view('admin.batches.index', compact('batches'));
    }

    public function create()
    {
        $programmes = Programme::where('is_active', true)->get();
        return view('admin.batches.create', compact('programmes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'programme_id' => ['required', 'exists:programmes,id'],
            'semester'     => ['required', 'integer', 'min:1', 'max:10'],
            'year_range'   => ['required', 'string', 'max:20'],
            'division'     => ['nullable', 'string', 'max:5'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['nullable', 'date', 'after:start_date'],
            'is_active'    => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $batch = Batch::create($validated);

        $this->weekService->generateWeeksForBatch($batch);

        return redirect()->route('admin.batches.show', $batch)
            ->with('success', "Batch created and {$batch->programme->total_weeks} academic weeks auto-generated.");
    }

    public function show(Batch $batch)
    {
        $batch->load([
            'programme',
            'courses.assignments.user',
            'academicWeeks' => fn ($q) => $q->orderBy('week_number'),
        ]);

        return view('admin.batches.show', compact('batch'));
    }

    public function edit(Batch $batch)
    {
        $programmes = Programme::where('is_active', true)->get();
        return view('admin.batches.edit', compact('batch', 'programmes'));
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'programme_id' => ['required', 'exists:programmes,id'],
            'semester'     => ['required', 'integer', 'min:1', 'max:10'],
            'year_range'   => ['required', 'string', 'max:20'],
            'division'     => ['nullable', 'string', 'max:5'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['nullable', 'date', 'after:start_date'],
            'is_active'    => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $batch->update($validated);

        return redirect()->route('admin.batches.index')
            ->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        if ($batch->courses()->exists()) {
            return back()->with('error', 'Cannot delete a batch that has courses assigned. Deactivate it instead.');
        }

        $batch->academicWeeks()->delete();
        $batch->delete();

        return redirect()->route('admin.batches.index')
            ->with('success', 'Batch deleted successfully.');
    }
}