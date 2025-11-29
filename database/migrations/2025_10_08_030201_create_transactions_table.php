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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Relasi ke kategori
            $table->decimal('jumlah', 16, 2); // DECIMAL sesuai aturan (15 digit total, 2 di belakang koma)
            $table->text('catatan')->nullable(); // Catatan bisa null
            $table->date('tanggal_transaksi'); // Tanggal transaksi
            $table->timestamps();
            $table->softDeletes(); // Tambahkan Soft Deletes sesuai aturan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
