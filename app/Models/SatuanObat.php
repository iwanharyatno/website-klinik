<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SatuanObat extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'kode';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];
}
