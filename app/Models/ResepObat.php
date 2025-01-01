<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResepObat extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'kode';

    protected $guarded = [
        'kode'
    ];

    public function detailResepObats() {
        return $this->hasMany(DetailResepObat::class, 'kode_resep', 'kode');
    }

    public function rekamMedis() {
        return $this->belongsTo(RekamMedis::class, 'kode_rekam_medis', 'kode');
    }
}
