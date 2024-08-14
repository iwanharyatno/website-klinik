<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasien extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'no_pasien';

    protected $guarded = [
        'no_pasien'
    ];

    public function rekamMedis() {
        return $this->hasMany(RekamMedis::class, 'no_pasien', 'no_pasien');
    }
}

