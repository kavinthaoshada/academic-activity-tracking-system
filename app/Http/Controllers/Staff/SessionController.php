<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSessionRequest;
use App\Models\AcademicWeek;
use App\Models\Batch;
use App\Models\Course;
use App\Models\WeeklySession;
use App\Services\SessionPlanningService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(private SessionPlanningService $planningService) {}

    /**
     * List all session entries with batch/week filtering.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $batches = $user->isAdmin()
            ? Batch::with('programme')->where('is_active', true)->get()
            : Batch::whereHas('courses.assignments', fn ($q) => $q->where('user_id', $user->id))
                   ->with('programme')->get();

        // Weeks for the selected batch
        $weeks = collect();
        if ($request->filled('batch_id')) {
            $weeks = AcademicWeek::where('batch_id', $request->batch_id)
                ->orderBy('week_number')->get();
        }

        $sessions = WeeklySession::with([
                'course.batch.programme',
                'academicWeek',
                'user',
            ])
            ->when(!$user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($request->filled('batch_id'), fn ($q) =>
                $q->whereHas('course', fn ($c) => $c->where('batch_id', $request->batch_id))
            )
            ->when($request->filled('week_id'), fn ($q) =>
                $q->where('academic_week_id', $request->week_id)
            )
            ->latest()
            ->paginate(25);

        return view('sessions.index', compact('sessions', 'batches', 'weeks'));
    }

    /**
     * Show the session entry form for a specific batch + week.
     * Pre-populates planned sessions based on week-type business rules.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        $batches = $user->isAdmin()
            ? Batch::with('programme')->where('is_active', true)->get()
            : Batch::whereHas('courses.assignments', fn ($q) => $q->where('user_id', $user->id))
                   ->with('programme')->where('is_active', true)->get();

        $weeks   = collect();
        $courses = collect();
        $plannedDefaults = collect();
        $week    = null;

        if ($request->filled('batch_id')) {
            $weeks = AcademicWeek::where('batch_id', $request->batch_id)
                ->orderBy('week_number')->get();
        }

        if ($request->filled('batch_id') && $request->filled('week_id')) {
            $week = AcademicWeek::with('batch.programme')->findOrFail($request->week_id);

            $courses = $user->isAdmin()
                ? Course::where('batch_id', $request->batch_id)
                        ->where('is_active', true)
                        ->with(['assignments.user', 'weeklySessions' => fn ($q) => $q->where('academic_week_id', $week->id)])
                        ->get()
                : Course::where('batch_id', $request->batch_id)
                        ->where('is_active', true)
                        ->whereHas('assignments', fn ($q) => $q->where('user_id', $user->id))
                        ->with(['assignments.user', 'weeklySessions' => fn ($q) => $q->where('academic_week_id', $week->id)])
                        ->get();

            // Pre-calculate planned sessions based on week type
            $plannedDefaults = $courses->mapWithKeys(fn ($course) => [
                $course->id => $this->planningService->calculatePlannedSessions($course, $week),
            ]);
        }

        return view('sessions.create', compact('batches', 'weeks', 'courses', 'week', 'plannedDefaults'));
    }

    /**
     * Save/update session entries for all courses in the submitted form.
     */
    public function store(StoreSessionRequest $request)
    {
        foreach ($request->sessions as $courseId => $sessionData) {
            $this->planningService->saveSession([
                'course_id'        => $courseId,
                'academic_week_id' => $request->academic_week_id,
                'user_id'          => $request->user()->id,
                'planned_sessions' => $sessionData['planned'] ?? 0,
                'actual_sessions'  => $sessionData['actual']  ?? 0,
                'remarks'          => $sessionData['remarks']  ?? null,
            ]);
        }

        return redirect()->route('sessions.index')
            ->with('success', 'Session data saved successfully.');
    }

    /**
     * Show a single session entry detail.
     */
    public function show(WeeklySession $session)
    {
        $session->load(['course.batch.programme', 'academicWeek', 'user']);
        return view('sessions.show', compact('session'));
    }
}