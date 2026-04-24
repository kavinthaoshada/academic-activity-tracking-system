<?php

namespace App\Exports;

use App\Models\Batch;
use App\Models\WeeklySession;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class SemesterActivityExport implements FromCollection, WithTitle, WithEvents, WithMapping, WithCustomStartCell
{
    private int $srNo = 1;

    public function __construct(private int $semester, private int $weekNumber) {}

    public function title(): string
    {
        return "Semester {$this->semester} - Wk {$this->weekNumber}";
    }

    public function collection()
    {
        $batches = Batch::where('semester', $this->semester)->where('is_active', true)->get();
        $collection = collect();

        foreach ($batches as $batch) {
            $week = $batch->academicWeeks()->where('week_number', $this->weekNumber)->first();
            if (!$week) continue;

            $sessions = WeeklySession::where('academic_week_id', $week->id)
                ->whereHas('course', fn($q) => $q->where('batch_id', $batch->id))
                ->with('course.assignments.user')->get();

            if ($sessions->isEmpty()) continue;

            $divider = new \stdClass();
            $divider->is_divider = true;
            $divider->label = $batch->full_label;
            $collection->push($divider);

            foreach ($sessions as $s) {
                $s->is_divider = false;
                $collection->push($s);
            }
        }
        return $collection;
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function map($session): array
    {
        if ($session->is_divider === true) {
            $this->srNo = 1; 
            return [$session->label];
        }

        $course  = $session->course;
        $faculty = $course->assignments->first()?->user?->name ?? '-';

        return [
            $this->srNo++, $course->name, $faculty, ucfirst($course->type), $course->total_hours,
            $session->planned_sessions, $session->actual_sessions, $session->actual_sessions - $session->planned_sessions,
            $session->cumulative_target, $session->cumulative_planned, $session->cumulative_actual,
            $session->cumulative_actual - $session->cumulative_planned,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:L1'); $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3'); $sheet->mergeCells('A4:L4');

                $sheet->setCellValue('A1', 'SKIPS UNIVERSITY — SCHOOL OF COMPUTER SCIENCE');
                $sheet->setCellValue('A2', 'SEMESTER-WIDE ACTIVITY REPORT');
                $sheet->setCellValue('A3', "SEMESTER {$this->semester} (All Programmes)");
                $sheet->setCellValue('A4', "WEEK - {$this->weekNumber}");

                $sheet->getStyle('A1:L4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF0F766E']],
                    'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $headers = ['A6' => 'Sr.', 'B6' => 'Course', 'C6' => 'Faculty', 'D6' => 'Type', 'E6' => 'Total Hrs', 'F6' => 'Planned', 'G6' => 'Actual', 'H6' => 'Wk Var.', 'I6' => 'Cu. Target', 'J6' => 'Cu. Planned', 'K6' => 'Cu. Actual', 'L6' => 'Cu. Var.'];
                foreach ($headers as $cell => $val) $sheet->setCellValue($cell, $val);

                $sheet->getStyle('A6:L6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF004E6F']],
                    'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                ]);

                $highestRow = $sheet->getHighestRow();
                for ($row = 7; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                    
                    $valA = $sheet->getCell("A{$row}")->getValue();
                    if (!is_numeric($valA) && !empty($valA)) {
                        $sheet->mergeCells("A{$row}:L{$row}");
                        $sheet->getStyle("A{$row}:L{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFE2E8F0']],
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF1E293B']],
                        ]);
                        continue;
                    }

                    foreach (['H', 'L'] as $col) {
                        $val = (int) $sheet->getCell("{$col}{$row}")->getValue();
                        if ($val < 0) $sheet->getStyle("{$col}{$row}")->getFont()->getColor()->setARGB('FFFF0000');
                        elseif ($val > 0) $sheet->getStyle("{$col}{$row}")->getFont()->getColor()->setARGB('FF008000');
                    }
                }

                $sheet->getStyle("A6:L{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FFDDDDDD'));
                foreach (range('A', 'L') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
            },
        ];
    }
}
