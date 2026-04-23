<?php

namespace Database\Seeders;

use App\Models\AcademicWeek;
use App\Models\Batch;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Programme;
use App\Models\Role;
use App\Models\User;
use App\Models\WeeklySession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);
        $staffRole = Role::firstOrCreate(['slug' => 'staff'], ['name' => 'Staff']);

        // 2. Admin User
        User::firstOrCreate(
            ['email' => 'kavintha.prof.acc@gmail.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );

        // 3. Staff Users
        $staffUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $staffUsers[] = User::firstOrCreate(
                ['email' => "staff{$i}@skips.edu.in"],
                [
                    'name' => "Faculty Member {$i}",
                    'password' => Hash::make('password123'),
                    'role_id' => $staffRole->id,
                    'is_active' => true,
                    'employee_id' => "EMP00{$i}",
                    'department' => 'Computer Science',
                ]
            );
        }

        // 4. Programmes
        $programmes = [
            ['name' => 'BCA (Hons.)', 'code' => 'BCA',    'total_weeks' => 15],
            ['name' => 'iMScIT',      'code' => 'IMSCIT',  'total_weeks' => 15],
            ['name' => 'BTech',       'code' => 'BTECH',   'total_weeks' => 15],
            ['name' => 'MScIT',       'code' => 'MSCIT',   'total_weeks' => 15],
        ];
        
        $createdProgrammes = [];
        foreach ($programmes as $p) {
            $createdProgrammes[] = Programme::firstOrCreate(['code' => $p['code']], $p + ['total_weeks' => 15]);
        }

        // 5. Batches
        $batches = [];
        foreach ($createdProgrammes as $prog) {
            $batches[] = Batch::firstOrCreate(
                ['programme_id' => $prog->id, 'semester' => 1, 'year_range' => '2025-29', 'division' => 'A'],
                [
                    'start_date' => Carbon::now()->subWeeks(5)->startOfWeek(),
                    'end_date' => Carbon::now()->addWeeks(10)->endOfWeek(),
                    'is_active' => true,
                ]
            );
        }

        // 6. Courses & Staff Assignments
        $courseData = [
            ['name' => 'Java Programming', 'code' => 'JAVA101', 'type' => 'theory', 'total_hours' => 45],
            ['name' => 'Database Management', 'code' => 'DBMS101', 'type' => 'theory', 'total_hours' => 45],
            ['name' => 'Web Technologies', 'code' => 'WEB101', 'type' => 'practical', 'total_hours' => 30],
        ];

        $courses = [];
        foreach ($batches as $batch) {
            foreach ($courseData as $index => $cData) {
                $course = Course::firstOrCreate(
                    ['batch_id' => $batch->id, 'code' => $cData['code'] . '-' . $batch->id],
                    [
                        'name' => $cData['name'],
                        'type' => $cData['type'],
                        'total_hours' => $cData['total_hours'],
                        'is_active' => true,
                    ]
                );
                $courses[] = $course;

                // Assign to a random staff member
                $staff = $staffUsers[$index % count($staffUsers)];
                CourseAssignment::firstOrCreate([
                    'course_id' => $course->id,
                    'user_id' => $staff->id,
                ]);
            }
        }

        // 7. Academic Weeks Logic
        // Week 1: Full, Week 2: Reduced, Week 3: Full, Week 4: Reduced, Week 5: Holiday, Week 6: Custom
        $weeks = [];
        foreach ($batches as $batch) {
            for ($w = 1; $w <= 6; $w++) {
                $start = Carbon::parse($batch->start_date)->addWeeks($w - 1)->startOfWeek();
                
                // Determine Week Rules
                if ($w === 5) {
                    $weekType = 'holiday';
                    $workingDays = 3; // e.g., Diwali break
                    $end = $start->copy()->addDays(2); 
                } elseif ($w === 6) {
                    $weekType = 'custom';
                    $workingDays = 4; // Admin special event
                    $end = $start->copy()->addDays(3);
                } else {
                    // Alternating 1st/3rd (Full) and 2nd/4th (Reduced)
                    $isFullWeek = ($w % 2 !== 0); 
                    $weekType = $isFullWeek ? 'full' : 'reduced';
                    $workingDays = $isFullWeek ? 6 : 5;
                    $end = $start->copy()->addDays($isFullWeek ? 5 : 4); 
                }
                
                $weeks[] = AcademicWeek::firstOrCreate(
                    ['batch_id' => $batch->id, 'week_number' => $w],
                    [
                        'start_date' => $start,
                        'end_date' => $end,
                        'working_days' => $workingDays,
                        'week_type' => $weekType,
                        'is_locked' => ($w <= 4), // Lock past weeks
                        'notes' => $weekType === 'holiday' ? 'Long weekend holiday' : null,
                    ]
                );
            }
        }

        // 8. Weekly Sessions Tracking Logs
        $cumulativeData = [];

        foreach ($weeks as $week) {
            // Only simulate tracked completions for past active weeks
            if ($week->week_number > 4) continue; 

            $batchCourses = Course::where('batch_id', $week->batch_id)->get();
            
            foreach ($batchCourses as $course) {
                $assignment = CourseAssignment::where('course_id', $course->id)->first();
                if (!$assignment) continue;

                // Set rules for Planned Sessions based on week type
                if ($week->week_type === 'holiday') {
                    $planned = 0; // 0 sessions for holidays
                } elseif ($week->week_type === 'reduced') {
                    $planned = 1; // 1 each for reduced weeks
                } else {
                    // Normal full week logic
                    $planned = ($course->type === 'theory') ? 4 : 2; 
                }

                $actual = rand(max(0, $planned - 1), $planned + 1); // Random flow, prevent negative nums

                // Track cumulative progress programmatic values
                $key = $course->id;
                if (!isset($cumulativeData[$key])) {
                    $cumulativeData[$key] = ['t' => 0, 'p' => 0, 'a' => 0];
                }
                
                $cumulativeData[$key]['t'] += $planned;
                $cumulativeData[$key]['p'] += $planned;
                $cumulativeData[$key]['a'] += $actual;

                WeeklySession::firstOrCreate(
                    ['course_id' => $course->id, 'academic_week_id' => $week->id],
                    [
                        'user_id' => $assignment->user_id,
                        'planned_sessions' => $planned,
                        'actual_sessions' => $actual,
                        'cumulative_target' => $cumulativeData[$key]['t'],
                        'cumulative_planned' => $cumulativeData[$key]['p'],
                        'cumulative_actual' => $cumulativeData[$key]['a'],
                        'remarks' => $actual < $planned ? 'Faculty on leave during lecture.' : ($actual > $planned ? 'Extra lecture taken to cover syllabus.' : 'Syllabus successfully on track.'),
                    ]
                );
            }
        }
    }
}
