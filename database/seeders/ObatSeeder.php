<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipeObat;

class ObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipeObat::create(['nama' => 'Tablet']);
        TipeObat::create(['nama' => 'Kapsul']);
        TipeObat::create(['nama' => 'Sirup']);
        TipeObat::create(['nama' => 'Salep']);
        TipeObat::create(['nama' => 'Injeksi']);

        $tipeObats = TipeObat::all();

        $obatNames = [
            'Tablet' => ['Paracetamol', 'Ibuprofen', 'Aspirin'],
            'Kapsul' => ['Amoxicillin', 'Doxycycline', 'Cephalexin'],
            'Sirup' => ['Cough Syrup', 'Antihistamine Syrup', 'Vitamin Syrup'],
            'Salep' => ['Hydrocortisone', 'Neomycin', 'Mupirocin'],
            'Injeksi' => ['Insulin', 'Morphine', 'Epinephrine']
        ];

        \App\Models\SatuanObat::create(['nama' => 'mg']);
        \App\Models\SatuanObat::create(['nama' => 'ml']);
        \App\Models\SatuanObat::create(['nama' => 'g']);
        \App\Models\SatuanObat::create(['nama' => 'IU']);
        \App\Models\SatuanObat::create(['nama' => 'mcg']);

        $satuanObats = \App\Models\SatuanObat::all();

        foreach ($tipeObats as $tipeObat) {
            foreach ($obatNames[$tipeObat->nama] as $obatName) {
                \App\Models\Obat::create([
                    'nama_obat' => $obatName,
                    'kode_tipe' => $tipeObat->kode,
                    'harga_jual' => rand(1000, 10000),
                    'harga_beli' => rand(500, 5000),
                    'stok' => rand(10, 100),
                    'satuan' => $satuanObats->random()->kode,
                ]);
            }
        }
    }
}
