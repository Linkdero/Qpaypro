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
        Schema::create('tbl_transacciones', function (Blueprint $table) {
            $table->id('id_transaccion');
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('pais', 50);
            $table->text('direccion');
            $table->string('nit', 20)->nullable();
            $table->text('descripcion');
            $table->string('moneda', 3); // GTQ o USD
            $table->decimal('cantidad', 10, 2);
            $table->dateTime('fecha')->useCurrent();
            $table->tinyInteger('estado')->default(0); // 0 = fallido, 1 = exitoso
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transacciones');
    }
};
