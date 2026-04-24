<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('role')
            ->when($request->role,   fn ($q) => $q->whereHas('role', fn ($r) => $r->where('slug', $request->role)))
            ->when($request->search, fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('employee_id', 'like', "%{$request->search}%")
            ))
            ->latest()
            ->paginate(20);

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->load([
            'role',
            'courseAssignments.course.batch.programme',
            'weeklySessions' => fn ($q) => $q->latest()->take(10),
        ]);

        $totalPlanned = $user->weeklySessions()->sum('planned_sessions');
        $totalActual  = $user->weeklySessions()->sum('actual_sessions');
        $compliance   = $totalPlanned > 0 ? round(($totalActual / $totalPlanned) * 100, 1) : 0;

        $allCourses = \App\Models\Course::with('batch.programme')->where('is_active', true)->get();

        return view('admin.users.show', compact('user', 'totalPlanned', 'totalActual', 'compliance', 'allCourses'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'phone'       => ['nullable', 'string', 'max:20'],
            'department'  => ['nullable', 'string', 'max:100'],
            'role_id'     => ['required', 'exists:roles,id'],
            'is_active'   => ['boolean'],
            'password'    => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->id === $request->user()->id && $user->isAdmin()) {
            $adminRole = Role::where('slug', 'admin')->first();
            $validated['role_id'] = $adminRole->id;
        }

        $validated['is_active'] = $request->boolean('is_active');

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === request()->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User account {$status} successfully.");
    }

    public function destroy(User $user)
    {
        if ($user->id === request()->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->weeklySessions()->exists()) {
            return back()->with('error', 'Cannot delete a user who has session records. Deactivate the account instead.');
        }

        $user->courseAssignments()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function assignCourses(Request $request, User $user)
    {
        $request->validate([
            'course_ids'   => ['nullable', 'array'],
            'course_ids.*' => ['exists:courses,id'],
        ]);

        $user->courseAssignments()->delete();

        if ($request->has('course_ids')) {
            foreach ($request->course_ids as $id) {
                \App\Models\CourseAssignment::create([
                     'course_id' => $id,
                     'user_id'   => $user->id
                ]);
            }
        }

        return back()->with('success', 'Courses successfully assigned to user.');
    }
}