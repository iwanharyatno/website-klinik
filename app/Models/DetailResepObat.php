<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailResepObat extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'kode';

    protected $guarded = [
        'kode'
    ];

    public function obat() {
        return $this->belongsTo(Obat::class, 'kode_obat', 'kode');
    }

    public function resepObat() {
        return $this->belongsTo(ResepObat::class, 'kode_resep', 'kode');
    }
}
