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
        Schema::create('specialist_custom_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialist_id')->constrained()->onDelete('cascade');
            
            $table->date('date'); // Fecha específica (ej: 2024-05-20)
            $table->time('start_time');
            $table->time('end_time');
            
            // Este campo es opcional pero muy útil: 
            // true = disponible ese día, false = bloqueado (vacaciones/permisos)
            $table->boolean('is_available')->default(true); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialist_custom_availabilities');
    }
};
