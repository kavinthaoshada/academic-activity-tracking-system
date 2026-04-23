@extends('layouts.app')
@section('title', $user->name)
@section('page-title', 'Staff Profile')
@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}">Staff</a>
    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    <span>{{ $user->name }}</span>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

    {{-- Profile card --}}
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div style="padding:28px 24px;text-align:center;border-bottom:1px solid var(--gray-100)">
                <div class="avatar avatar-lg" style="margin:0 auto 14px">{{ $user->initials() }}</div>
                <h3 style="font-size:1.1rem;margin-bottom:5px">{{ $user->name }}</h3>
                <div style="font-size:.82rem;color:var(--gray-400);margin-bottom:10px">{{ $user->email }}</div>
                <div style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap">
                    <span class="badge {{ $user->role?->slug === 'admin' ? 'badge-danger' : 'badge-teal' }}">{{ $user->role?->name ?? 'No role' }}</span>
                    <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-gray' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>
            <div style="padding:16px 20px">
                <div class="info-row">
                    <span class="info-label">Employee ID</span>
                    <span class="info-value">{{ $user->employee_id ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Department</span>
                    <span class="info-value">{{ $user->department ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone</span>
                    <span class="info-value">{{ $user->phone ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Login</span>
                    <span class="info-value">{{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Never' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Member Since</span>
                    <span class="info-value">{{ $user->created_at->format('d M Y') }}</span>
                </div>
                <div class="info-row" style="border-bottom:none">
                    <span class="info-label">2FA</span>
                    <span class="info-value">
                        <span class="badge {{ $user->two_factor_confirmed_at ? 'badge-success' : 'badge-gray' }}">
                            {{ $user->two_factor_confirmed_at ? 'Enabled' : 'Disabled' }}
                        </span>
                    </span>
                </div>
            </div>
            <div style="padding:14px 20px;border-top:1px solid var(--gray-100);display:flex;gap:8px">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-teal btn-sm" style="flex:1;justify-content:center">Edit</a>
                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" style="flex:1">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-danger' : 'btn-outline' }}" style="width:100%;justify-content:center">
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Performance stats --}}
        <div class="card">
            <div class="card-header"><h3>Performance</h3></div>
            <div style="padding:16px 20px">
                <div class="info-row">
                    <span class="info-label">Total Planned</span>
                    <span class="info-value" style="font-weight:600">{{ $totalPlanned }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Actual</span>
                    <span class="info-value" style="font-weight:600">{{ $totalActual }}</span>
                </div>
                <div class="info-row" style="border-bottom:none">
                    <span class="info-label">Compliance Rate</span>
                    <span class="info-value" style="font-weight:700;color:{{ $compliance >= 85 ? 'var(--success)' : ($compliance >= 60 ? 'var(--warning)' : 'var(--danger)') }}">
                        {{ $compliance }}%
                    </span>
                </div>
                <div style="margin-top:10px">
                    <div class="progress-wrap">
                        <div class="progress-bar {{ $compliance >= 85 ? 'green' : ($compliance >= 60 ? 'gold' : 'danger') }}"
                             style="width:{{ min(100,$compliance) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: courses + recent sessions --}}
    <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Course assignments --}}
        <div class="card">
            <div class="card-header">
                <h3>Course Assignments</h3>
                <span class="badge badge-teal">{{ $user->courseAssignments->count() }} courses</span>
            </div>

            {{-- Quick Assign Courses Form --}}
            <div class="card" style="margin-bottom:16px;">
                <div class="card-header" style="padding:14px 20px">
                    <h3>Assign Courses</h3>
                    <span class="badge badge-teal" id="selected-count-badge">
                        {{ $user->courseAssignments->count() }} selected
                    </span>
                </div>

                <div style="padding:14px 20px 0">
                    <div style="position:relative;margin-bottom:10px">
                        <input
                            type="text"
                            id="course-search"
                            placeholder="Search courses..."
                            class="form-control"
                            style="padding-left:32px"
                            autocomplete="off"
                        >
                        <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-400);pointer-events:none"
                            width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.users.assign-courses', $user) }}" id="assign-courses-form">
                    @csrf

                    <div id="course-list"
                        style="max-height:260px;overflow-y:auto;border-top:1px solid var(--gray-100);border-bottom:1px solid var(--gray-100)">
                        @forelse($allCourses as $c)
                            @php $isChecked = $user->courseAssignments->contains('course_id', $c->id); @endphp
                            <label
                                class="course-item {{ $isChecked ? 'course-item-selected' : '' }}"
                                data-name="{{ strtolower($c->name) }}"
                                data-batch="{{ strtolower($c->batch->full_label) }}"
                            >
                                <input
                                    type="checkbox"
                                    name="course_ids[]"
                                    value="{{ $c->id }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                    onchange="updateCourseUI(this)"
                                >
                                <div style="flex:1;min-width:0">
                                    <div style="font-size:.85rem;font-weight:500;color:var(--gray-800)">{{ $c->name }}</div>
                                    <div style="font-size:.73rem;color:var(--gray-400);margin-top:1px">
                                        {{ $c->batch->full_label }}
                                        <span class="badge {{ $c->type === 'theory' ? 'badge-teal' : 'badge-gold' }}"
                                            style="font-size:.65rem;padding:1px 5px;margin-left:4px">
                                            {{ ucfirst($c->type) }}
                                        </span>
                                    </div>
                                </div>
                                <svg class="check-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;color:var(--teal)">
                                    <polyline points="20 6 9 12 4 10"/>
                                </svg>
                            </label>
                        @empty
                            <div style="padding:20px;text-align:center;color:var(--gray-400);font-size:.85rem">No courses available.</div>
                        @endforelse
                    </div>

                    <div style="padding:12px 20px;display:flex;align-items:center;justify-content:space-between;gap:10px">
                        <button type="button" onclick="clearAll()" class="btn btn-sm btn-outline" style="font-size:.78rem">
                            Clear all
                        </button>
                        <button type="submit" class="btn btn-teal btn-sm">Save Assignments</button>
                    </div>
                </form>
            </div>

            @if($user->courseAssignments->count() > 0)
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Batch</th>
                            <th>Type</th>
                            <th>Total Hrs</th>
                            <th>Weekly Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->courseAssignments as $assignment)
                        @php $c = $assignment->course; @endphp
                        <tr>
                            <td>
                                <div style="font-weight:500;color:var(--gray-800)">{{ $c->name }}</div>
                                @if($c->code)<div style="font-size:.72rem;color:var(--gray-400)">{{ $c->code }}</div>@endif
                            </td>
                            <td style="font-size:.82rem;color:var(--gray-500)">{{ $c->batch->full_label }}</td>
                            <td><span class="badge {{ $c->type === 'theory' ? 'badge-teal' : 'badge-gold' }}">{{ ucfirst($c->type) }}</span></td>
                            <td>{{ $c->total_hours }}</td>
                            <td style="font-weight:600;color:var(--teal)">{{ $c->weekly_target }}/wk</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state" style="padding:32px">
                <p>No courses assigned to this staff member yet.</p>
            </div>
            @endif
        </div>

        {{-- Recent session entries --}}
        <div class="card">
            <div class="card-header">
                <h3>Recent Session Entries</h3>
                <span style="font-size:.78rem;color:var(--gray-400)">Last 10 entries</span>
            </div>
            @if($user->weeklySessions->count() > 0)
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Week</th>
                            <th>Planned</th>
                            <th>Actual</th>
                            <th>Variance</th>
                            <th>Logged</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->weeklySessions as $s)
                        @php $v = $s->actual_sessions - $s->planned_sessions; @endphp
                        <tr>
                            <td style="font-size:.84rem;font-weight:500">{{ Str::limit($s->course->name, 28) }}</td>
                            <td>Wk {{ $s->academicWeek->week_number }}</td>
                            <td>{{ $s->planned_sessions }}</td>
                            <td>{{ $s->actual_sessions }}</td>
                            <td><span class="{{ $v > 0 ? 'variance-pos' : ($v < 0 ? 'variance-neg' : 'variance-zero') }}">{{ $v > 0 ? '+' : '' }}{{ $v }}</span></td>
                            <td style="font-size:.78rem;color:var(--gray-400)">{{ $s->created_at->format('d M') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state" style="padding:32px">
                <p>No session entries recorded yet.</p>
            </div>
            @endif
        </div>

    </div>
</div>

<style>
.course-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 20px;
    cursor: pointer;
    transition: background .12s;
    border-bottom: 1px solid var(--gray-100);
    user-select: none;
}
.course-item:last-child { border-bottom: none; }
.course-item:hover { background: var(--gray-50, #f9fafb); }
.course-item-selected { background: color-mix(in srgb, var(--teal) 7%, transparent); }
.course-item-selected:hover { background: color-mix(in srgb, var(--teal) 12%, transparent); }
.course-item input[type="checkbox"] { accent-color: var(--teal); width:15px; height:15px; flex-shrink:0; }
.course-item .check-icon { opacity: 0; transition: opacity .15s; }
.course-item-selected .check-icon { opacity: 1; }
.course-item-hidden { display: none; }
</style>
@endsection
