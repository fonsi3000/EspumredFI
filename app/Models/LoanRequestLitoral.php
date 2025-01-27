<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRequestLitoral extends Model
{
    protected $table = 'loan_requests_litoral';

    protected $fillable = [
        'name',
        'document_number',
        'phone',
        'email',
        'area',
        'position',
        'loan_number',
        'amount',
        'term_months',
        'interest_rate',
        'payment_frequency',
        'loan_reason',
        'guarantee_document',
        'observations',
        'status',
        'responsible_user_id',
        'created_by_user_id',
        'deleted_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // Constants
    const PAYMENT_FREQUENCIES = [
        'monthly' => 'Mensual',
        'biweekly' => 'Quincenal'
    ];

    const LOAN_REASONS = [
        'education' => 'Educación',
        'health' => 'Salud',
        'housing' => 'Vivienda',
        'debt_consolidation' => 'Consolidación de Deudas',
        'personal' => 'Personal',
        'others' => 'Otros'
    ];

    const STATUSES = [
        'pending_approval' => 'Pendiente de Aprobación',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado'
    ];

    // Relations
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function activeLoan()
    {
        return $this->hasOne(ActiveLoanLitoral::class, 'loan_request_id');
    }

    // Scopes
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Method to generate loan number
    public static function generateLoanNumber(): string
    {
        $lastNumber = static::max('id') ?? 0;
        return str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    public function markAsDeleted()
    {
        $this->deleted_at = now();
        return $this->save();
    }
}
