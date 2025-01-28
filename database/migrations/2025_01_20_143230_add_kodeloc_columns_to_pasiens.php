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
        Schema::table('pasiens', function (Blueprint $table) {
            $table->string('kode_propinsi')->nullable();
            $table->string('kode_kabupaten')->nullable();
            $table->string('kode_kecamatan')->nullable();
            $table->string('kode_kelurahan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasiens', function (Blueprint $table) {
            //
        });
    }
};
