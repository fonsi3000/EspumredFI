<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabla principal de préstamos activos
        Schema::create('active_loans', function (Blueprint $table) {
            $table->id();

            // Relación con la solicitud original
            $table->foreignId('loan_request_id')
                ->constrained('loan_requests')
                ->onDelete('restrict');

            // Información básica
            $table->string('loan_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('current_balance', 12, 2);
            $table->integer('term_months');
            $table->decimal('interest_rate', 5, 2);
            $table->enum('payment_frequency', ['monthly', 'biweekly']);

            // Control de fechas - Ahora nullable porque dependen del desembolso
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('next_payment_date')->nullable();
            $table->date('disbursement_date')->nullable();

            // Montos y totales
            $table->decimal('monthly_payment', 12, 2)->nullable();
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_interest_paid', 12, 2)->default(0);
            $table->decimal('total_principal_paid', 12, 2)->default(0);

            // Control de pagos
            $table->integer('total_payments');
            $table->integer('payments_made')->default(0);
            $table->integer('payments_remaining');

            // Estado del préstamo - Actualizado con los nuevos estados
            $table->enum('status', [
                'pending_disbursement',  // Sin desembolsar
                'active',               // Activo
                'completed'             // Completado
            ])->default('pending_disbursement');

            // Control
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Cambiado a softDeletes() para consistencia
        });

        // Tabla para el historial de pagos
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('active_loan_id')
                ->constrained('active_loans')
                ->onDelete('restrict');

            // Detalles del pago
            $table->integer('payment_number');
            $table->date('scheduled_date');
            $table->date('payment_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2);
            $table->decimal('remaining_balance', 12, 2);

            // Comprobante y estado
            $table->string('receipt_number')->nullable();
            $table->string('receipt_file')->nullable();

            // Estado del pago - Corregido con los estados correctos
            $table->enum('status', [
                'pending',    // Pendiente de pago
                'paid',      // Pagado completamente
                'partial',   // Pago parcial
                'late'       // Pago atrasado
            ])->default('pending');

            // Control
            $table->text('notes')->nullable();
            $table->foreignId('registered_by')
                ->constrained('users')
                ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('active_loans');
    }
};
