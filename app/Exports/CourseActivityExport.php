<?php

namespace App\Exports;

use App\Models\Course;
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

class CourseActivityExport implements FromCollection, WithTitle, WithEvents, WithMapping, WithCustomStartCell
{
    private int $srNo = 1;

    public function __construct(private Course $course) {}

    public function title(): string
    {
        return substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $this->course->name), 0, 31);
    }

    public function collection()
    {
        return WeeklySession::where('course_id', $this->course->id)
            ->with(['academicWeek'])
            ->get()
            ->sortBy(fn($s) => $s->academicWeek->week_number);
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function map($session): array
    {
        $week = $session->academicWeek;
        return [
            $this->srNo++,
            "Week {$week->week_number}",
            $week->start_date->format('d M') . ' - ' . $week->end_date->format('d M Y'),
            $week->working_days,
            $session->planned_sessions,
            $session->actual_sessions,
            $session->actual_sessions - $session->planned_sessions,
            $session->cumulative_target,
            $session->cumulative_planned,
            $session->cumulative_actual,
            $session->cumulative_actual - $session->cumulative_planned,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $faculty = $this->course->assignments->first()?->user?->name ?? 'Unassigned';

                // Merge and style the exact UI Header
                $sheet->mergeCells('A1:K1'); $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3'); $sheet->mergeCells('A4:K4');

                $sheet->setCellValue('A1', 'SKIPS UNIVERSITY — SCHOOL OF COMPUTER SCIENCE');
                $sheet->setCellValue('A2', 'COURSE ACTIVITY REPORT');
                $sheet->setCellValue('A3', "{$this->course->name} (" . ucfirst($this->course->type) . " - {$this->course->total_hours} hrs)");
                $sheet->setCellValue('A4', "FACULTY: {$faculty} | BATCH: {$this->course->batch->full_label}");

                $sheet->getStyle('A1:K4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF0F766E']], // UI Teal
                    'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Grid Headers
                $headers = [
                    'A6' => 'Sr. No.', 'B6' => 'Academic Week', 'C6' => 'Dates', 'D6' => 'Working Days',
                    'E6' => 'Planned', 'F6' => 'Actual', 'G6' => 'Weekly Var.',
                    'H6' => 'Cu. Target', 'I6' => 'Cu. Planned', 'J6' => 'Cu. Actual', 'K6' => 'Cu. Var.'
                ];
                foreach ($headers as $cell => $val) $sheet->setCellValue($cell, $val);

                $sheet->getStyle('A6:K6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF004E6F']], // Darker Teal
                    'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                ]);

                $highestRow = $sheet->getHighestRow();
                for ($row = 7; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22); // UI Padding
                    foreach (['G', 'K'] as $col) {
                        $val = (int) $sheet->getCell("{$col}{$row}")->getValue();
                        if ($val < 0) $sheet->getStyle("{$col}{$row}")->getFont()->getColor()->setARGB('FFFF0000');
                        elseif ($val > 0) $sheet->getStyle("{$col}{$row}")->getFont()->getColor()->setARGB('FF008000');
                    }
                }

                $sheet->getStyle("A6:K{$highestRow}")->getBorders()->getAllBorders()
                      ->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FFDDDDDD'));
                foreach (range('A', 'K') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
            },
        ];
    }
}
