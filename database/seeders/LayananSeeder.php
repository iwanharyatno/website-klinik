<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('layanans')->insert([
            [
                'nama_layanan' => 'Pemeriksaan Umum',
                'harga' => 25000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_layanan' => 'Pemeriksaan Gigi',
                'harga' => 40000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_layanan' => 'Laboratorium',
                'harga' => 75000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_layanan' => 'Tindakan Medis Ringan',
                'harga' => 50000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_layanan' => 'Konsultasi Dokter Spesialis',
                'harga' => 100000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
