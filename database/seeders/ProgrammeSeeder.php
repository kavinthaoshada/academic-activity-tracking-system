<?php

namespace Database\Seeders;

use App\Models\Programme;
use Illuminate\Database\Seeder;

class ProgrammeSeeder extends Seeder
{
    public function run(): void
    {
        $programmes = [
            ['name' => 'BCA (Hons.)', 'code' => 'BCA',    'total_weeks' => 15],
            ['name' => 'iMScIT',      'code' => 'IMSCIT',  'total_weeks' => 15],
            ['name' => 'BTech',       'code' => 'BTECH',   'total_weeks' => 15],
            ['name' => 'MScIT',       'code' => 'MSCIT',   'total_weeks' => 15],
        ];

        foreach ($programmes as $p) {
            Programme::firstOrCreate(['code' => $p['code']], $p);
        }
    }
}