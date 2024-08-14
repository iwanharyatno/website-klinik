<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembelianObat extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'no_transaksi';
}
