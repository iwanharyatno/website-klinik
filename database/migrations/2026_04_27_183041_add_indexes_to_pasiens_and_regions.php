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
        // Adding indexes to the pasiens table
        Schema::table('pasiens', function (Blueprint $table) {
            // Index for name-based search
            $table->index('nama_pasien');

            // Indexes for region code lookups (crucial for 'alamat' mode)
            $table->index('kode_propinsi');
            $table->index('kode_kabupaten');
            $table->index('kode_kecamatan');
            $table->index('kode_kelurahan');
            
            // Optional: If you frequently search the text address field directly
            // $table->fulltext('alamat'); 
        });

        // Adding index to the tbl_regions table
        Schema::table('tbl_regions', function (Blueprint $table) {
            // Index for looking up region codes by their names (e.g., searching "Sleman")
            $table->index('region_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasiens', function (Blueprint $table) {
            $table->dropIndex(['nama_pasien']);
            $table->dropIndex(['kode_propinsi']);
            $table->dropIndex(['kode_kabupaten']);
            $table->dropIndex(['kode_kecamatan']);
            $table->dropIndex(['kode_kelurahan']);
        });

        Schema::table('tbl_regions', function (Blueprint $table) {
            $table->dropIndex(['region_name']);
        });
    }
};