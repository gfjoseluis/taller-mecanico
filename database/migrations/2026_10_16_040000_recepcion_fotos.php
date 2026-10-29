<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fotos de recepción: una por posición, normalizadas a 1280px JPEG.
        Schema::create('recepcion_fotos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('recepcion_id')->constrained('recepciones')->cascadeOnDelete();
            $t->string('posicion', 20);
            $t->string('ruta', 255);
            $t->timestamps();
            $t->unique(['recepcion_id', 'posicion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_fotos');
    }
};
