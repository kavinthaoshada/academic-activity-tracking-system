<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicWeek;
use App\Models\Batch;
use App\Services\AcademicWeekService;
use Illuminate\Http\Request;

class AcademicWeekController extends Controller
{
    public function __construct(private AcademicWeekService $weekService) {}

    public function index(Request $request)
    {
        $batches = Batch::with('programme')->where('is_active', true)->get();

        $weeks = AcademicWeek::with('batch.programme')
            ->when($request->batch_id, fn ($q) => $q->where('batch_id', $request->batch_id))
            ->orderBy('batch_id')
            ->orderBy('week_number')
            ->paginate(30);

        return view('admin.academic-weeks.index', compact('weeks', 'batches'));
    }

    public function create()
    {
        $batches = Batch::with('programme')->where('is_active', true)->get();
        return view('admin.academic-weeks.create', compact('batches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id'     => ['required', 'exists:batches,id'],
            'week_number'  => ['required', 'integer', 'min:1'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
            'working_days' => ['required', 'integer', 'min:0', 'max:6'],
            'week_type'    => ['required', 'in:full,reduced,holiday,custom'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        AcademicWeek::create($validated);

        return redirect()->route('admin.academic-weeks.index', ['batch_id' => $validated['batch_id']])
            ->with('success', 'Academic week added successfully.');
    }

    public function show(AcademicWeek $academicWeek)
    {
        $academicWeek->load([
            'batch.programme',
            'weeklySessions.course',
            'weeklySessions.user',
        ]);

        return view('admin.academic-weeks.show', compact('academicWeek'));
    }

    public function edit(AcademicWeek $academicWeek)
    {
        abort_if($academicWeek->is_locked, 403, 'This week is locked and cannot be edited.');
        $academicWeek->load('batch.courses');
        $batches = Batch::with('programme')->where('is_active', true)->get();
        return view('admin.academic-weeks.edit', compact('academicWeek', 'batches'));
    }

    public function update(Request $request, AcademicWeek $academicWeek)
    {
        abort_if($academicWeek->is_locked, 403, 'This week is locked and cannot be edited.');

        $validated = $request->validate([
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
            'working_days' => ['required', 'integer', 'min:0', 'max:6'],
            'week_type'    => ['required', 'in:full,reduced,holiday,custom'],
            'notes'        => ['nullable', 'string', 'max:500'],
            'planned_session_overrides'   => ['nullable', 'array'],
            'planned_session_overrides.*' => ['nullable', 'integer', 'min:0'],
        ]);

        if (isset($validated['planned_session_overrides'])) {
            $validated['planned_session_overrides'] = array_filter(
                $validated['planned_session_overrides'], 
                fn($val) => $val !== null && $val !== ''
            );
        }

        $academicWeek->update($validated);

        app(\App\Services\SessionPlanningService::class)->syncAndRecalculate($academicWeek);

        return redirect()->route('admin.academic-weeks.index', ['batch_id' => $academicWeek->batch_id])
            ->with('success', 'Week updated successfully.');
    }

    public function destroy(AcademicWeek $academicWeek)
    {
        if ($academicWeek->weeklySessions()->exists()) {
            return back()->with('error', 'Cannot delete a week that has session records logged.');
        }

        $academicWeek->delete();

        return redirect()->route('admin.academic-weeks.index')
            ->with('success', 'Academic week deleted.');
    }

    public function lock(AcademicWeek $academicWeek)
    {
        $academicWeek->update(['is_locked' => true]);
        return back()->with('success', "Week {$academicWeek->week_number} has been locked.");
    }

    public function unlock(AcademicWeek $academicWeek)
    {
        $academicWeek->update(['is_locked' => false]);
        return back()->with('success', "Week {$academicWeek->week_number} has been unlocked.");
    }
}