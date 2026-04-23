<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateWeeklyReport;
use App\Models\AcademicWeek;
use App\Models\Batch;
use App\Models\WeeklySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // public function index(Request $request)
    // {
    //     $user = $request->user();

    //     $batches = $user->isAdmin()
    //         ? Batch::with('programme')->where('is_active', true)->get()
    //         : Batch::whereHas('courses.assignments', fn ($q) => $q->where('user_id', $user->id))
    //                ->with('programme')
    //                ->get();

    //     $weeks = collect();
    //     $previewData = collect();

    //     if ($request->filled('batch_id')) {
    //         $weeks = AcademicWeek::where('batch_id', $request->batch_id)
    //             ->orderBy('week_number')
    //             ->get();
    //     }

    //     if ($request->filled('batch_id') && $request->filled('week_id')) {
    //         $previewData = $this->getPreviewData(
    //             Batch::findOrFail($request->batch_id),
    //             AcademicWeek::findOrFail($request->week_id),
    //             $user
    //         );
    //     }

    //     return view('reports.index', compact('batches', 'weeks', 'previewData'));
    // }

    public function index(Request $request)
    {
        $user = $request->user();
        $batches = $user->isAdmin()
            ? Batch::with('programme')->where('is_active', true)->get()
            : Batch::whereHas('courses.assignments', fn ($q) => $q->where('user_id', $user->id))->with('programme')->get();

        $allCourses = \App\Models\Course::with('batch.programme')
            ->whereHas('batch', fn($q) => $q->where('is_active', true))->get();
        $semesters = \App\Models\Batch::where('is_active', true)->distinct()->pluck('semester');

        $weeks = collect();
        $previewData = collect();
        $previewType = $request->input('report_type', 'weekly');
        $previewMeta = [];

        if ($request->filled('batch_id')) {
            $weeks = AcademicWeek::where('batch_id', $request->batch_id)
                ->orderBy('week_number')->get();
        }

        // Weekly preview (existing)
        if ($previewType === 'weekly' && $request->filled('batch_id') && $request->filled('week_id')) {
            $previewData = $this->getPreviewData(
                Batch::findOrFail($request->batch_id),
                AcademicWeek::findOrFail($request->week_id),
                $user
            );
        }

        // Course history preview (new)
        if ($previewType === 'course' && $request->filled('course_id')) {
            $course = \App\Models\Course::with('batch.programme')->findOrFail($request->course_id);
            $previewData = $this->getCoursePreviewData($course, $user);
            $previewMeta['course'] = $course;
        }

        // Semester preview (new)
        if ($previewType === 'semester' && $request->filled('semester') && $request->filled('week_number')) {
            $previewData = $this->getSemesterPreviewData($request->semester, $request->week_number, $user);
            $previewMeta['semester'] = $request->semester;
            $previewMeta['week_number'] = $request->week_number;
        }

        return view('reports.index', compact(
            'batches', 'weeks', 'previewData', 'allCourses',
            'semesters', 'previewType', 'previewMeta'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'week_id'  => ['required', 'exists:academic_weeks,id'],
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        $week  = AcademicWeek::findOrFail($request->week_id);

        if (!$request->user()->isAdmin()) {
            $isAssigned = $batch->courses()
                ->whereHas('assignments', fn ($q) => $q->where('user_id', $request->user()->id))
                ->exists();

            abort_unless($isAssigned, 403, 'You are not assigned to any courses in this batch.');
        }

        GenerateWeeklyReport::dispatch($batch, $week, $request->user());

        return back()->with('success', 'Report is being generated. You will receive an email with the download link shortly.');
    }

    public function download(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = base64_decode($request->path);

        abort_unless(
            str_starts_with($path, 'reports/') && !str_contains($path, '..'),
            403,
            'Invalid report path.'
        );

        abort_unless(Storage::disk('local')->exists($path), 404, 'Report file not found or has expired.');

        return Storage::disk('local')->download($path);
    }

    public function downloadNow(Request $request)
    {
        $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'week_id'  => ['required', 'exists:academic_weeks,id'],
        ]);

        $batch = Batch::findOrFail($request->batch_id);
        $week  = AcademicWeek::findOrFail($request->week_id);

        if (!$request->user()->isAdmin()) {
            $isAssigned = $batch->courses()
                ->whereHas('assignments', fn ($q) => $q->where('user_id', $request->user()->id))
                ->exists();
            abort_unless($isAssigned, 403, 'You are not assigned to any courses in this batch.');
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AcademicActivityExport($batch, $week),
            "academic-activity-{$batch->id}-week-{$week->week_number}-" . now()->format('Ymd') . ".xlsx"
        );
    }

    private function getPreviewData(Batch $batch, AcademicWeek $week, $user): \Illuminate\Support\Collection
    {
        return WeeklySession::where('academic_week_id', $week->id)
            ->whereHas('course', fn ($q) => $q->where('batch_id', $batch->id))
            ->when(!$user->isAdmin(), fn ($q) =>
                $q->whereHas('course.assignments', fn ($a) => $a->where('user_id', $user->id))
            )
            ->with([
                'course.assignments.user',
                'course.batch.programme',
            ])
            ->get()
            ->groupBy('course_id');
    }

    private function getCoursePreviewData(\App\Models\Course $course, $user): \Illuminate\Support\Collection
    {
        // Check authorization for non-admins
        if (!$user->isAdmin()) {
            $isAssigned = $course->assignments()->where('user_id', $user->id)->exists();
            abort_unless($isAssigned, 403, 'You are not assigned to this course.');
        }

        return WeeklySession::where('course_id', $course->id)
            ->with(['course.assignments.user', 'course.batch.programme', 'academicWeek'])
            ->orderBy('academic_week_id')
            ->get()
            ->groupBy('course_id');
    }

    private function getSemesterPreviewData(int $semester, int $weekNumber, $user): \Illuminate\Support\Collection
{
    $batches = \App\Models\Batch::where('semester', $semester)
        ->where('is_active', true)->get();

    $result = collect(); // keyed by batch label

    foreach ($batches as $batch) {
        $week = $batch->academicWeeks()->where('week_number', $weekNumber)->first();
        if (!$week) continue;

        $sessions = WeeklySession::where('academic_week_id', $week->id)
            ->whereHas('course', fn($q) => $q->where('batch_id', $batch->id))
            ->when(!$user->isAdmin(), fn($q) =>
                $q->whereHas('course.assignments', fn($a) => $a->where('user_id', $user->id))
            )
            ->with(['course.assignments.user', 'course.batch.programme'])
            ->get();

        if ($sessions->isEmpty()) continue;

        // Store as [ 'BCA Sem 1 Div A' => [ course_id => [sessions] ] ]
        $result->put($batch->full_label, $sessions->groupBy('course_id'));
    }

    return $result;
}

    public function downloadCourseReport(Request $request)
    {
        $request->validate(['course_id' => 'required|exists:courses,id']);
        $course = \App\Models\Course::findOrFail($request->course_id);
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CourseActivityExport($course),
            "course-activity-" . \Illuminate\Support\Str::slug($course->name) . "-" . now()->format('Ymd') . ".xlsx"
        );
    }

    public function downloadSemesterReport(Request $request)
    {
        $request->validate(['semester' => 'required|integer', 'week_number' => 'required|integer']);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SemesterActivityExport($request->semester, $request->week_number),
            "semester-{$request->semester}-week-{$request->week_number}-" . now()->format('Ymd') . ".xlsx"
        );
    }

}