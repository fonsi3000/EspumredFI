<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();

            // Información del Solicitante
            $table->string('name');
            $table->string('document_number');
            $table->string('phone');
            $table->string('email');
            $table->string('area');
            $table->string('position');

            // Detalles del Préstamo
            $table->string('loan_number')->unique();
            $table->decimal('amount', 12, 0);
            $table->integer('term_months');
            $table->decimal('interest_rate', 5, 2);
            $table->enum('payment_frequency', ['monthly', 'biweekly']);
            $table->enum('loan_reason', [
                'education',
                'health',
                'housing',
                'debt_consolidation',
                'personal',
                'others'
            ]);

            // Documentación
            $table->string('guarantee_document')->nullable();
            $table->text('observations')->nullable();

            // Control y Estado
            $table->enum('status', [
                'pending_approval',
                'approved',
                'rejected'
            ])->default('pending_approval');

            // Relaciones
            $table->foreignId('responsible_user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable(); // Columna normal de deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
