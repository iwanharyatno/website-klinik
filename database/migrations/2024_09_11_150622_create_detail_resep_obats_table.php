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
        Schema::create('detail_resep_obats', function (Blueprint $table) {
            $table->id('kode');
            $table->foreignId('kode_resep')->constrained('resep_obats', 'kode')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('kode_obat')->constrained('obats', 'kode')->onDelete('cascade')->onUpdate('cascade');
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
