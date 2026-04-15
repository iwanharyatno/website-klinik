<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'nama' => 'PT Sehat Sentosa',
                'email' => 'sales@sehatsentosa.co.id',
                'telepon' => '081234567890',
                'alamat' => 'Jakarta',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'CV Farma Jaya',
                'email' => 'info@farmajaya.co.id',
                'telepon' => '081298765432',
                'alamat' => 'Bandung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'PT Medika Nusantara',
                'email' => 'contact@medikanusantara.co.id',
                'telepon' => '082112223333',
                'alamat' => 'Surabaya',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
