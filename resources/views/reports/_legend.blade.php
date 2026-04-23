{{-- resources/views/reports/_legend.blade.php --}}
<div class="card">
    <div class="card-header"><h3>Report Columns</h3></div>
    <div style="padding:12px 16px">
        @foreach([
            ['Planned for Week',  'Sessions planned based on week type rules'],
            ['Actual Conducted',  'Sessions actually delivered'],
            ['Weekly Variance',   'Actual − Planned (this week)'],
            ['Cumu. Target',      'Running sum of targets'],
            ['Cumu. Planned',     'Running sum of planned'],
            ['Cumu. Actual',      'Running sum of actual'],
            ['Cumu. Variance',    'Cumu. Actual − Cumu. Planned'],
        ] as [$col, $desc])
        <div style="padding:7px 0;border-bottom:1px solid var(--gray-50)">
            <div style="font-size:.78rem;font-weight:600;color:var(--teal-dark)">{{ $col }}</div>
            <div style="font-size:.72rem;color:var(--gray-400)">{{ $desc }}</div>
        </div>
        @endforeach
    </div>
</div>