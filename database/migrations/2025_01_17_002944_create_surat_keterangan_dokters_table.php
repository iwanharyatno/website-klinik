<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_keterangan_dokters', function (Blueprint $table) {
            $table->id();
            $table->integer('no_surat');
            $table->string('no_bulan');
            $table->integer('no_tahun');
            $table->string('no_surat_full');
            $table->string('nama_pasien');
            $table->integer('umur');
            $table->text('alamat');
            $table->string('pekerjaan');
            $table->date('mulai_istirahat');
            $table->date('selesai_istirahat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan_dokters');
    }
};
