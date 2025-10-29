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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            // Foreign key ke tabel users, pastikan unique untuk relasi 1:1
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('nama_usaha');
            $table->string('logo_path')->nullable(); // Path logo bisa null
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
