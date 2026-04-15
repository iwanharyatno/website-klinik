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
        // =========================
        // REKAM MEDIS
        // =========================
        Schema::table('rekam_medis', function (Blueprint $table) {
            // Index untuk filter waktu
            $table->index('created_at', 'idx_rm_created_at');

            // Composite index untuk join + filter waktu
            $table->index(['no_pasien', 'created_at'], 'idx_rm_no_pasien_created');
        });

        // =========================
        // PASIENS
        // =========================
        Schema::table('pasiens', function (Blueprint $table) {
            $table->index('no_pasien', 'idx_pasien_no_pasien');
            $table->index('kode_kecamatan', 'idx_pasien_kode_kecamatan');
        });

        // =========================
        // REGIONS
        // =========================
        Schema::table('tbl_regions', function (Blueprint $table) {
            $table->index('region_code', 'idx_region_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekam_medis', function (Blueprint $table) {
            $table->dropIndex('idx_rm_created_at');
            $table->dropIndex('idx_rm_no_pasien_created');
        });

        Schema::table('pasiens', function (Blueprint $table) {
            $table->dropIndex('idx_pasien_no_pasien');
            $table->dropIndex('idx_pasien_kode_kecamatan');
        });

        Schema::table('tbl_regions', function (Blueprint $table) {
            $table->dropIndex('idx_region_code');
        });
    }
};
