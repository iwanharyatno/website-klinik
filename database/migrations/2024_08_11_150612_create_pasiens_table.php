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
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id('no_pasien');
            $table->string('nama_pasien');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('tipe')->nullable();
            $table->enum('status', ['Sakit', 'Sembuh'])->nullable();
            $table->string('tempat_tgl_lahir')->nullable();
            $table->integer('umur')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('propinsi')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kode_pos')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasiens');
    }
};
