<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPembelianObatSeeder extends Seeder
{
    public function run(): void
    {
        $pembelianIds = DB::table('pembelian_obats')->pluck('no_transaksi');
        $obats = DB::table('obats')->get();

        foreach ($pembelianIds as $pembelianId) {

            // tiap transaksi beli 1–5 jenis obat
            $items = $obats->random(rand(1, 5));

            foreach ($items as $obat) {
                $qty = rand(5, 50);
                $harga = (float) $obat->harga_beli;
                $total = $qty * $harga;

                DB::table('detail_pembelian_obats')->insert([
                    'kode_pembelian' => $pembelianId,
                    'kode_obat' => $obat->kode,
                    'qty' => $qty,
                    'harga' => $harga,
                    'total' => $total,
                    'keterangan' => 'Pembelian rutin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}