<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelianObat extends Model
{
    use HasFactory;

    protected $primaryKey = 'kode';
    protected $table = 'detail_pembelian_obats'; // Pastikan nama tabelnya benar

    protected $fillable = [
        'no_transaksi',
        'kode_pembelian', // Relasi ke tabel pembelian_obats (no_transaksi)
        'kode_obat',
        'keterangan',     // Sesuai screenshot
        'qty',            // Sesuai screenshot
        'harga',          // Sesuai screenshot
        'total'           // Sesuai screenshot (Ini yang dijumlah)
    ];
}
