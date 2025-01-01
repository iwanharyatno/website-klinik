<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obat extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'kode';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function tipeObat() {
        return $this->belongsTo(TipeObat::class, 'kode_tipe', 'kode');
    }

    public function satuanObat() {
        return $this->belongsTo(SatuanObat::class, 'satuan', 'kode');
    }
}
