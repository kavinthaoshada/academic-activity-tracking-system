{{-- resources/views/reports/_preview_panel.blade.php --}}
{{-- $type: 'weekly' | 'course' | 'semester' --}}

<div class="card" style="overflow:hidden">

    @if(isset($previewData) && $previewData->count() > 0 && $activeTab === $type)

        {{-- ── Card header (differs per type) ── --}}
        @if($type === 'weekly')
            @php
                $batch = \App\Models\Batch::find(request('batch_id'));
                $week  = \App\Models\AcademicWeek::find(request('week_id'));
            @endphp
            <div class="card-header">
                <div>
                    <h3>Preview — {{ $batch->full_label }}</h3>
                    <div style="font-size:.78rem;color:var(--gray-400);margin-top:2px">{{ $week->label }}</div>
                </div>
                <span class="badge badge-success">{{ $previewData->flatten(1)->flatten()->count() }} rows</span>
            </div>
            {{-- Report banner --}}
            <div style="background:var(--teal);color:white;padding:14px 24px;text-align:center">
                <div style="font-family:var(--font-display);font-size:1rem;letter-spacing:.04em">
                    SKIPS UNIVERSITY — SCHOOL OF COMPUTER SCIENCE
                </div>
                <div style="font-size:.84rem;margin-top:4px;opacity:.85">ACADEMIC ACTIVITY REPORT</div>
                <div style="font-size:.8rem;margin-top:2px;opacity:.7">{{ $batch->full_label }}</div>
                <div style="font-size:.78rem;margin-top:2px;opacity:.65">
                    WEEK — {{ $week->week_number }}
                    [ {{ $week->start_date->format('jth F') }} to {{ $week->end_date->format('jth F, Y') }} ]
                </div>
            </div>

        @elseif($type === 'course')
            <div class="card-header">
                <div>
                    <h3>Preview — {{ $previewMeta['course']->name }}</h3>
                    <div style="font-size:.78rem;color:var(--gray-400);margin-top:2px">
                        {{ $previewMeta['course']->batch->full_label }} · Full Course History
                    </div>
                </div>
                <span class="badge badge-success">{{ $previewData->flatten()->count() }} rows</span>
            </div>

        @elseif($type === 'semester')
            <div class="card-header">
                <div>
                    <h3>Preview — Semester {{ $previewMeta['semester'] }}, Week {{ $previewMeta['week_number'] }}</h3>
                    <div style="font-size:.78rem;color:var(--gray-400);margin-top:2px">
                        {{ $previewData->count() }} batches
                    </div>
                </div>
                <span class="badge badge-success">
                    {{ $previewData->sum(fn($group) => $group->sum(fn($sessions) => $sessions->count())) }} rows
                </span>
            </div>
        @endif

        {{-- ── Data table (shared for all types) ── --}}
        <div class="table-wrap">
            <table class="table report-table" style="min-width:850px">
                <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Course</th>
                        <th>Faculty</th>
                        <th>Type</th>
                        <th>Total Hrs</th>
                        <th>Planned</th>
                        <th>Actual</th>
                        <th>Wk Var</th>
                        <th>Cu. Target</th>
                        <th>Cu. Planned</th>
                        <th>Cu. Actual</th>
                        <th>Cu. Var</th>
                    </tr>
                </thead>
                <tbody>
                    @if($type === 'semester')
                        {{-- Two-level: batch → courses --}}
                        @foreach($previewData as $batchLabel => $courseGroups)
                            {{-- Batch divider row --}}
                            <tr>
                                <td colspan="12" style="
                                    background:#E2E8F0;
                                    font-weight:700;
                                    font-size:.8rem;
                                    color:#1E293B;
                                    padding:8px 12px;
                                    letter-spacing:.03em;
                                ">{{ $batchLabel }}</td>
                            </tr>
                            {{-- Course rows under this batch --}}
                            @foreach($courseGroups as $courseId => $sessions)
                                @foreach($sessions as $s)
                                    @php
                                        $wv = $s->actual_sessions - $s->planned_sessions;
                                        $cv = $s->cumulative_actual - $s->cumulative_planned;
                                    @endphp
                                    <tr>
                                        <td style="color:var(--gray-400);font-size:.8rem">{{ $loop->parent->parent->iteration }}</td>
                                        <td style="font-weight:500;font-size:.84rem">{{ $s->course->name }}</td>
                                        <td style="font-size:.8rem;color:var(--gray-500)">{{ $s->course->assignments->first()?->user?->name ?? '—' }}</td>
                                        <td><span class="badge {{ $s->course->type === 'theory' ? 'badge-teal' : 'badge-gold' }}">{{ ucfirst($s->course->type) }}</span></td>
                                        <td>{{ $s->course->total_hours }}</td>
                                        <td>{{ $s->planned_sessions }}</td>
                                        <td>{{ $s->actual_sessions }}</td>
                                        <td><span class="{{ $wv > 0 ? 'variance-pos' : ($wv < 0 ? 'variance-neg' : 'variance-zero') }}">{{ $wv > 0 ? '+' : '' }}{{ $wv }}</span></td>
                                        <td>{{ $s->cumulative_target }}</td>
                                        <td>{{ $s->cumulative_planned }}</td>
                                        <td>{{ $s->cumulative_actual }}</td>
                                        <td><span class="{{ $cv > 0 ? 'variance-pos' : ($cv < 0 ? 'variance-neg' : 'variance-zero') }}">{{ $cv > 0 ? '+' : '' }}{{ $cv }}</span></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach

                    @else
                        {{-- Weekly & Course: flat groupBy('course_id') --}}
                        @foreach($previewData as $courseId => $sessions)
                            @foreach($sessions as $s)
                                @php
                                    $wv = $s->actual_sessions - $s->planned_sessions;
                                    $cv = $s->cumulative_actual - $s->cumulative_planned;
                                @endphp
                                <tr>
                                    <td style="color:var(--gray-400);font-size:.8rem">{{ $loop->parent->iteration }}</td>
                                    <td style="font-weight:500;font-size:.84rem">{{ $s->course->name }}</td>
                                    <td style="font-size:.8rem;color:var(--gray-500)">{{ $s->course->assignments->first()?->user?->name ?? '—' }}</td>
                                    <td><span class="badge {{ $s->course->type === 'theory' ? 'badge-teal' : 'badge-gold' }}">{{ ucfirst($s->course->type) }}</span></td>
                                    <td>{{ $s->course->total_hours }}</td>
                                    <td>{{ $s->planned_sessions }}</td>
                                    <td>{{ $s->actual_sessions }}</td>
                                    <td><span class="{{ $wv > 0 ? 'variance-pos' : ($wv < 0 ? 'variance-neg' : 'variance-zero') }}">{{ $wv > 0 ? '+' : '' }}{{ $wv }}</span></td>
                                    <td>{{ $s->cumulative_target }}</td>
                                    <td>{{ $s->cumulative_planned }}</td>
                                    <td>{{ $s->cumulative_actual }}</td>
                                    <td><span class="{{ $cv > 0 ? 'variance-pos' : ($cv < 0 ? 'variance-neg' : 'variance-zero') }}">{{ $cv > 0 ? '+' : '' }}{{ $cv }}</span></td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

    @else

        {{-- Empty state --}}
        <div class="empty-state">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
            </div>
            @if($type === 'weekly')
                <h3>Select a batch and week</h3>
                <p>Choose a batch and academic week on the left to preview the report.</p>
            @elseif($type === 'course')
                <h3>Select a course</h3>
                <p>Choose a course on the left to preview its full session history.</p>
            @elseif($type === 'semester')
                <h3>Select semester and week</h3>
                <p>Choose a semester and week number to preview the semester-wide report.</p>
            @endif
        </div>

    @endif
</div>