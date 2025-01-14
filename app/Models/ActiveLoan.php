<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActiveLoan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_request_id',
        'loan_number',
        'amount',
        'current_balance',
        'term_months',
        'interest_rate',
        'payment_frequency',
        'start_date',
        'end_date',
        'next_payment_date',
        'monthly_payment',
        'total_paid',
        'total_interest_paid',
        'total_principal_paid',
        'total_payments',
        'payments_made',
        'payments_remaining',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'total_interest_paid' => 'decimal:2',
        'total_principal_paid' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_payment_date' => 'date',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_DELAYED = 'delayed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DEFAULTED = 'defaulted';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_ACTIVE => 'Activo',
        self::STATUS_DELAYED => 'Atrasado',
        self::STATUS_COMPLETED => 'Completado',
        self::STATUS_DEFAULTED => 'En Mora',
        self::STATUS_CANCELLED => 'Cancelado'
    ];

    // Relaciones
    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(LoanRequest::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    // Método estático para crear desde una solicitud aprobada
    public static function createFromRequest(LoanRequest $request): self
    {
        $startDate = now();

        // Calcular el pago mensual
        $monthlyRate = $request->interest_rate / 100;
        $amount = $request->amount;
        $term = $request->term_months;

        // Si el pago es quincenal, ajustar los cálculos
        if ($request->payment_frequency === 'biweekly') {
            $monthlyPayment = ($amount / $term) + ($amount * $monthlyRate);  // Capital + Interés
        } else {
            $monthlyPayment = ($amount / $term) + ($amount * $monthlyRate);  // Capital + Interés
        }

        $loan = new self([
            'loan_request_id' => $request->id,
            'loan_number' => $request->loan_number,
            'amount' => $request->amount,
            'current_balance' => $request->amount,
            'term_months' => $request->term_months,
            'interest_rate' => $request->interest_rate,
            'payment_frequency' => $request->payment_frequency,
            'start_date' => $startDate,
            'end_date' => $startDate->copy()->addMonths($request->term_months),
            'next_payment_date' => $startDate->copy()->addMonth(),
            'monthly_payment' => round($monthlyPayment, 2), // Agregamos el pago mensual calculado
            'total_payments' => $request->term_months,
            'payments_remaining' => $request->term_months,
            'status' => self::STATUS_ACTIVE
        ]);

        $loan->save();

        // Generar tabla de amortización y crear pagos programados
        $schedule = $loan->generateAmortizationSchedule();
        $loan->createScheduledPayments($schedule);

        return $loan;
    }

    // Generar tabla de amortización
    public function generateAmortizationSchedule(): array
    {
        $schedule = [];
        $balance = $this->amount;
        $monthlyRate = $this->interest_rate / 100;
        $fixedPrincipal = round($this->amount / $this->term_months, 2);
        $startDate = Carbon::parse($this->start_date);

        // Registro inicial (Mes 0)
        $schedule[] = [
            'month' => 0,
            'date' => $startDate->copy()->format('Y-m-d'),
            'monthly_rate' => $monthlyRate * 100,
            'monthly_payment' => 0,
            'interest' => 0,
            'principal' => 0,
            'balance' => $this->amount,
            'accumulated_principal' => 0
        ];

        // Generar cronograma de pagos
        for ($month = 1; $month <= $this->term_months; $month++) {
            // Cálculo de interés sobre saldo
            $interest = round($balance * $monthlyRate, 2);

            // La amortización es fija
            $principal = $fixedPrincipal;

            // Cuota total = amortización + intereses
            $monthlyPayment = $principal + $interest;

            // Actualizar saldo
            $balance -= $principal;

            // Capital amortizado acumulado
            $accumulatedPrincipal = $fixedPrincipal * $month;

            $schedule[] = [
                'month' => $month,
                'date' => $startDate->copy()->addMonths($month)->format('Y-m-d'),
                'monthly_rate' => $monthlyRate * 100,
                'monthly_payment' => $monthlyPayment,
                'interest' => $interest,
                'principal' => $principal,
                'balance' => max(0, $balance),
                'accumulated_principal' => $accumulatedPrincipal
            ];
        }

        return $schedule;
    }

    // Crear pagos programados
    protected function createScheduledPayments(array $schedule): void
    {
        // Omitimos el primer registro (mes 0)
        array_shift($schedule);

        foreach ($schedule as $payment) {
            LoanPayment::create([
                'active_loan_id' => $this->id,
                'payment_number' => $payment['month'],
                'scheduled_date' => $payment['date'],
                'principal_amount' => $payment['principal'],
                'interest_amount' => $payment['interest'],
                'remaining_balance' => $payment['balance'],
                'amount_paid' => 0,
                'status' => LoanPayment::STATUS_PENDING,
                'registered_by' => auth()->id() // Agregamos el ID del usuario autenticado
            ]);
        }
    }

    public function applyPayment($amount, $paymentDate, $receiptNumber = null, $notes = null): LoanPayment
    {
        // Obtener el siguiente pago pendiente
        $payment = $this->payments()
            ->where('status', LoanPayment::STATUS_PENDING)
            ->orderBy('payment_number')
            ->first();

        if (!$payment) {
            throw new \Exception('No hay pagos pendientes');
        }

        // Calculamos los valores usando la amortización
        $schedule = $this->generateAmortizationSchedule();
        $monthlyValues = $schedule[$payment->payment_number] ?? null;

        if (!$monthlyValues) {
            throw new \Exception('Error al obtener los valores de la cuota');
        }

        // Actualizar el registro de pago existente
        $payment->update([
            'payment_date' => $paymentDate,
            'amount_paid' => $amount,
            'receipt_number' => $receiptNumber,
            'notes' => $notes,
            'status' => $amount >= ($monthlyValues['principal'] + $monthlyValues['interest'])
                ? LoanPayment::STATUS_PAID
                : LoanPayment::STATUS_PARTIAL,
            'registered_by' => auth()->id()
        ]);

        $this->updateLoanStatus();

        return $payment;
    }

    // Actualizar estado del préstamo
    protected function updateLoanStatus(): void
    {
        $this->refresh();

        // Calcular pagos totales
        $totalPaid = $this->payments()
            ->whereIn('status', [LoanPayment::STATUS_PAID])
            ->sum('amount_paid');

        $totalPrincipalPaid = $this->payments()
            ->whereIn('status', [LoanPayment::STATUS_PAID])
            ->sum('principal_amount');

        $totalInterestPaid = $this->payments()
            ->whereIn('status', [LoanPayment::STATUS_PAID])
            ->sum('interest_amount');

        // Actualizar contadores de pagos
        $this->payments_made = $this->payments()
            ->whereIn('status', [LoanPayment::STATUS_PAID])
            ->count();
        $this->payments_remaining = $this->total_payments - $this->payments_made;

        // Actualizar totales
        $this->total_paid = $totalPaid;
        $this->total_principal_paid = $totalPrincipalPaid;
        $this->total_interest_paid = $totalInterestPaid;

        // Actualizar saldo actual
        $this->current_balance = max(0, $this->amount - $totalPrincipalPaid);

        // Determinar estado
        if ($this->current_balance <= 0) {
            $this->status = self::STATUS_COMPLETED;
        } elseif ($this->hasLatePayments()) {
            $this->status = self::STATUS_DELAYED;
        } else {
            $this->status = self::STATUS_ACTIVE;
        }

        $this->save();
    }

    // Verificar pagos atrasados
    public function hasLatePayments(): bool
    {
        return $this->payments()
            ->where('scheduled_date', '<', now())
            ->where('status', LoanPayment::STATUS_PENDING)
            ->exists();
    }

    // Obtener próximo pago pendiente
    public function getNextPayment()
    {
        return $this->payments()
            ->where('status', LoanPayment::STATUS_PENDING)
            ->orderBy('payment_number')
            ->first();
    }

    // Calcular progreso del préstamo
    public function getProgressPercentage(): float
    {
        if ($this->amount <= 0) return 0;
        return ($this->total_principal_paid / $this->amount) * 100;
    }
}

class LoanPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'active_loan_id',
        'payment_number',
        'scheduled_date',
        'payment_date',
        'amount_paid',
        'principal_amount',
        'interest_amount',
        'remaining_balance',
        'receipt_number',
        'receipt_file',
        'status',
        'notes',
        'registered_by'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'payment_date' => 'date',
        'amount_paid' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_LATE = 'late';

    public function activeLoan(): BelongsTo
    {
        return $this->belongsTo(ActiveLoan::class);
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
