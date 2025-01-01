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
        Schema::create('rekam_medis', function (Blueprint $table) {
            $table->id('kode');
            $table->foreignId('no_pasien')->constrained('pasiens', 'no_pasien');
            $table->integer('no_antrian')->default(1);
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->integer('denyut_nadi')->nullable();
            $table->decimal('suhu_tubuh', 5, 2)->nullable();
            $table->integer('tekanan_darah_sistole')->nullable();
            $table->integer('tekanan_darah_diastole')->nullable();
            $table->integer('rate_pernafasan')->nullable();
            $table->text('keluhan_awal')->nullable();
            $table->text('keluhan_tambahan')->nullable();
            $table->text('diagnosa_akhir')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
