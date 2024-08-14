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
        Schema::create('detail_pembelian_obats', function (Blueprint $table) {
            $table->id('kode');
            $table->foreignId('kode_pembelian')->constrained('pembelian_obats', 'no_transaksi');
            $table->foreignId('kode_obat')->constrained('obats', 'kode');
            $table->text('keterangan')->nullable();
            $table->integer('qty');
            $table->decimal('harga', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian_obats');
    }
};
