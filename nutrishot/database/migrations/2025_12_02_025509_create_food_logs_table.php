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
        Schema::create('food_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('food_name');
            $table->integer('calories');      // kkal
            $table->float('carbs')->default(0); // gram
            $table->float('fat')->default(0);   // gram
            $table->float('protein')->default(0); // gram
            
            // Menyimpan path gambar jika hasil dari scan/upload
            $table->string('image_path')->nullable(); 

            // Waktu makan (bisa diinput mundur/backdate)
            $table->timestamp('eaten_at')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_logs');
    }
};