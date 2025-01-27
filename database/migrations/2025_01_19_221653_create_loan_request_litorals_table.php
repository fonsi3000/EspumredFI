<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_requests_litoral', function (Blueprint $table) {
            $table->id();

            // Solicitant Information
            $table->string('name');
            $table->string('document_number');
            $table->string('phone');
            $table->string('email');
            $table->string('area');
            $table->string('position');

            // Loan Details
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

            // Documentation
            $table->string('guarantee_document')->nullable();
            $table->text('observations')->nullable();

            // Control and Status
            $table->enum('status', [
                'pending_approval',
                'approved',
                'rejected'
            ])->default('pending_approval');

            // Relations
            $table->foreignId('responsible_user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_requests_litoral');
    }
};
