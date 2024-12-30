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
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_comprobante')->unique();
            $table->foreignId('cuenta_ingreso_id')->constrained('cuenta_ingresos');
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
            $table->string('comprobante_path'); // Para guardar la ruta del archivo
            $table->enum('estado', ['activo', 'anulado'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
