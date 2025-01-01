<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportRegions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('tbl_regions.sql');

        echo "Import SQL from sql file ".$path."\n";
        DB::unprepared(file_get_contents($path));
        echo "Import SQL Success!\n\n";
    }
}
