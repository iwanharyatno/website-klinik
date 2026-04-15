<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembelianObatSeeder extends Seeder
{
    public function run(): void
    {
        $supplierIds = DB::table('suppliers')->pluck('kode');

        for ($i = 0; $i < 120; $i++) {
            DB::table('pembelian_obats')->insert([
                'tanggal' => Carbon::create(
                    rand(2024, 2025),
                    rand(1, 12),
                    rand(1, 28)
                ),
                'supplier' => $supplierIds->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
