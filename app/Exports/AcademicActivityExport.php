<?php

namespace App\Exports;

use App\Models\AcademicWeek;
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

class AcademicActivityExport implements FromCollection, WithTitle, WithEvents, WithMapping, WithCustomStartCell
{
    private int $srNo = 1;

    public function __construct(
        private Batch        $batch,
        private AcademicWeek $week,
    ) {}

    public function title(): string
    {
        return "Week {$this->week->week_number}";
    }

    public function collection()
    {
        return WeeklySession::where('academic_week_id', $this->week->id)
            ->whereHas('course', fn ($q) => $q->where('batch_id', $this->batch->id))
            ->with(['course.assignments.user'])
            ->get();
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function map($session): array
    {
        $course  = $session->course;
        $faculty = $course->assignments->first()?->user?->name ?? '-';

        return [
            $this->srNo++,
            $course->name,
            $faculty,
            ucfirst($course->type),
            $course->total_hours,
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

                $sheet->setCellValue('A1', 'SKIPS UNIVERSITY - SCHOOL OF COMPUTER SCIENCE');
                $sheet->setCellValue('A2', 'ACADEMIC ACTIVITY REPORT');
                $sheet->setCellValue('A3', $this->batch->full_label ?? 'Batch Report');
                $sheet->setCellValue('A4', "WEEK - {$this->week->week_number} [ {$this->week->start_date->format('jth F')} to {$this->week->end_date->format('jth F, Y')} ]");
                $sheet->setCellValue('L5', 'Date: ' . now()->format('d/m/Y'));

                $headers = [
                    'A6' => 'Sr. No.', 'B6' => 'Course', 'C6' => 'Faculty Name',
                    'D6' => 'Theory/ Practical', 'E6' => 'Total Sessions (in hours)',
                    'F6' => 'Planned for Week', 'G6' => 'Actual conducted',
                    'H6' => 'Weekly Variance (+/-)', 'I6' => 'Cumu. Target',
                    'J6' => 'Cumu. Planned', 'K6' => 'Cumu. Actual',
                    'L6' => 'Cumu. Variance (+/-)',
                ];

                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }

                $sheet->mergeCells('A1:L1'); $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3'); $sheet->mergeCells('A4:L4');

                $sheet->getStyle('A1:L4')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FF0F766E']],
                    'font' => ['color' => ['argb' => 'FFFFFFFF'], 'bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle("A6:L{$highestRow}")->getBorders()->getAllBorders()
                      ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                      ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFDDDDDD'));


                $highestRow = $sheet->getHighestRow();
                for ($row = 7; $row <= $highestRow; $row++) {
                    
                    $weeklyVar = (int) $sheet->getCell("H{$row}")->getValue();
                    if ($weeklyVar < 0) {
                        $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB('FFFF0000');
                    } elseif ($weeklyVar > 0) {
                        $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB('FF008000');
                    }

                    $cumuVar = (int) $sheet->getCell("L{$row}")->getValue();
                    if ($cumuVar < 0) {
                        $sheet->getStyle("L{$row}")->getFont()->getColor()->setARGB('FFFF0000');
                    } elseif ($cumuVar > 0) {
                        $sheet->getStyle("L{$row}")->getFont()->getColor()->setARGB('FF008000');
                    }
                }

                foreach (range('A', 'L') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
