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
        Schema::table('users', function (Blueprint $table) {
            // TAMBAHKAN kolom id_perusahaan
            // Langsung buat 'nullable' agar sesuai alur onboarding Anda
            // 'after('email')' opsional, hanya untuk merapikan posisi
            
            $table->foreignId('id_perusahaan')
                  ->nullable()
                  ->after('email') 
                  ->constrained('perusahaans') // Menautkan ke tabel 'perusahaans'
                  ->nullOnDelete(); // Jika perusahaan dihapus, user ini jadi null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key dan kolomnya jika rollback
            $table->dropForeign(['id_perusahaan']);
            $table->dropColumn('id_perusahaan');
        });
    }
};