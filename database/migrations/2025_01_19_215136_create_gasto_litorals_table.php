<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gasto_litorals', function (Blueprint $table) {
            $table->id();
            $table->string('numero_comprobante')->unique();
            $table->foreignId('cuenta_egreso_litoral_id')->constrained('cuenta_egreso_litorals');
            $table->foreignId('user_id')->constrained();
            $table->decimal('monto', 20, 2);
            $table->date('fecha');
            $table->enum('forma_pago', [
                'efectivo',
                'transferencia',
                'tarjeta_credito',
                'tarjeta_debito',
                'cheque'
            ]);
            $table->text('descripcion');
            $table->string('comprobante_path');
            $table->enum('estado', ['activo', 'anulado'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gasto_litorals');
    }
};
