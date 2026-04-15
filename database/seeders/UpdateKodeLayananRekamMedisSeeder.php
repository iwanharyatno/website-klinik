<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateKodeLayananRekamMedisSeeder extends Seeder
{
    public function run(): void
    {
        $layananIds = DB::table('layanans')->pluck('id');

        DB::table('rekam_medis')
            ->whereNull('kode_layanan')
            ->orderBy('kode') 
            ->chunk(500, function ($rekamMedis) use ($layananIds) {
                foreach ($rekamMedis as $rm) {
                    DB::table('rekam_medis')
                        ->where('kode', $rm->kode)
                        ->update([
                            'kode_layanan' => $layananIds->random(),
                        ]);
                }
            });
    }
}
