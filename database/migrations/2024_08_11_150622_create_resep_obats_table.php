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
        Schema::create('resep_obats', function (Blueprint $table) {
            $table->id('kode');
            $table->foreignId('no_pasien')->constrained('pasiens', 'no_pasien');
            $table->string('nama_pasien');
            $table->foreignId('kode_rekam_medis')->constrained('rekam_medis', 'kode');
            $table->foreignId('kode_obat')->constrained('obats', 'kode');
            $table->decimal('harga', 10, 2);
            $table->integer('kuantitas');
            $table->decimal('total', 10, 2);
            $table->text('aturan_pakai')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep_obats');
    }
};
