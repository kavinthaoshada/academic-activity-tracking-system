<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $batches = Batch::with('programme')->where('is_active', true)->get();

        $courses = Course::with(['batch.programme', 'assignments.user'])
            ->when($request->batch_id, fn ($q) => $q->where('batch_id', $request->batch_id))
            ->when($request->type,     fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20);

        return view('admin.courses.index', compact('courses', 'batches'));
    }

    public function create()
    {
        $batches = Batch::with('programme')->where('is_active', true)->get();
        $faculty = User::whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'staff']))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.courses.create', compact('batches', 'faculty'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'batch_id'     => ['required', 'exists:batches,id'],
            'name'         => ['required', 'string', 'max:255'],
            'code'         => ['nullable', 'string', 'max:50'],
            'type'         => ['required', 'in:theory,practical'],
            'total_hours'  => ['required', 'integer', 'min:1'],
            'credit_hours' => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['boolean'],
            'faculty_ids'  => ['nullable', 'array'],
            'faculty_ids.*'=> ['exists:users,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $facultyIds = $validated['faculty_ids'] ?? [];
        unset($validated['faculty_ids']);

        $course = Course::create($validated);

        foreach ($facultyIds as $userId) {
            $course->assignments()->firstOrCreate(['user_id' => $userId]);
        }

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created and faculty assigned successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['batch.programme', 'assignments.user', 'weeklySessions.academicWeek']);
        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $batches = Batch::with('programme')->where('is_active', true)->get();
        $faculty = User::whereHas('role', fn ($q) => $q->whereIn('slug', ['admin', 'staff']))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $assignedFacultyIds = $course->assignments->pluck('user_id')->toArray();

        return view('admin.courses.edit', compact('course', 'batches', 'faculty', 'assignedFacultyIds'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'batch_id'     => ['required', 'exists:batches,id'],
            'name'         => ['required', 'string', 'max:255'],
            'code'         => ['nullable', 'string', 'max:50'],
            'type'         => ['required', 'in:theory,practical'],
            'total_hours'  => ['required', 'integer', 'min:1'],
            'credit_hours' => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['boolean'],
            'faculty_ids'  => ['nullable', 'array'],
            'faculty_ids.*'=> ['exists:users,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $facultyIds = $validated['faculty_ids'] ?? [];
        unset($validated['faculty_ids']);

        $course->update($validated);

        $course->assignments()->whereNotIn('user_id', $facultyIds)->delete();
        foreach ($facultyIds as $userId) {
            $course->assignments()->firstOrCreate(['user_id' => $userId]);
        }

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        if ($course->weeklySessions()->exists()) {
            return back()->with('error', 'Cannot delete a course that has session records. Deactivate it instead.');
        }

        $course->assignments()->delete();
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}