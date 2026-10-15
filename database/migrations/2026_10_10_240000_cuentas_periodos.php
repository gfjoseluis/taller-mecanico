<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['codigo' => '1.3', 'nombre' => 'Cuentas por cobrar', 'tipo' => 'activo', 'nivel' => 2, 'imputable' => true],
            ['codigo' => '3', 'nombre' => 'PATRIMONIO', 'tipo' => 'patrimonio', 'nivel' => 1, 'imputable' => false],
            ['codigo' => '3.1', 'nombre' => 'Capital social', 'tipo' => 'patrimonio', 'nivel' => 2, 'imputable' => true],
        ] as $a) {
            \App\Models\ChartAccount::firstOrCreate(['codigo' => $a['codigo']], $a);
        }
        Schema::create('periodos', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('gestion');
            $t->unsignedTinyInteger('mes');
            $t->boolean('cerrado')->default(false);
            $t->foreignId('user_id')->nullable()->constrained('users');
            $t->timestamps();
            $t->unique(['gestion', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos');
    }
};
