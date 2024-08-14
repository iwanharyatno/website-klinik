<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekamMedis extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'kode';

    protected $guarded = [
        'kode'
    ];

    public function pasien() {
        return $this->belongsTo(Pasien::class, 'no_pasien', 'no_pasien');
    }
}
