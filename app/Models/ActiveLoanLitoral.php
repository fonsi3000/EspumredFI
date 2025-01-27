<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActiveLoanLitoral extends Model
{
    use SoftDeletes;

    protected $table = 'active_loans_litorals';

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
        'disbursement_date',
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
        'disbursement_date' => 'date',
    ];

    const STATUS_PENDING_DISBURSEMENT = 'pending_disbursement';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_PENDING_DISBURSEMENT => 'Sin Desembolsar',
        self::STATUS_ACTIVE => 'Activo',
        self::STATUS_COMPLETED => 'Completado'
    ];

    const PAYMENT_FREQUENCY_MONTHLY = 'monthly';
    const PAYMENT_FREQUENCY_BIWEEKLY = 'biweekly';

    const PAYMENT_FREQUENCIES = [
        self::PAYMENT_FREQUENCY_MONTHLY => 'Mensual',
        self::PAYMENT_FREQUENCY_BIWEEKLY => 'Quincenal'
    ];

    // Relaciones
    public function loanRequest(): BelongsTo
    {
        return $this->belongsTo(LoanRequestLitoral::class, 'loan_request_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPaymentLitoral::class, 'active_loan_litoral_id');
    }


    // Método estático para crear desde una solicitud aprobada
    public static function createFromRequest(LoanRequestLitoral $request): self
    {
        $totalPayments = $request->payment_frequency === self::PAYMENT_FREQUENCY_BIWEEKLY
            ? $request->term_months * 2
            : $request->term_months;

        $loan = new self([
            'loan_request_id' => $request->id,
            'loan_number' => $request->loan_number,
            'amount' => $request->amount,
            'current_balance' => $request->amount,
            'term_months' => $request->term_months,
            'interest_rate' => $request->interest_rate,
            'payment_frequency' => $request->payment_frequency,
            'total_payments' => $totalPayments,
            'payments_remaining' => $totalPayments,
            'status' => self::STATUS_PENDING_DISBURSEMENT
        ]);

        $loan->save();
        return $loan;
    }

    // Método para desembolsar el préstamo
    public function disburse(): void
    {
        if ($this->status !== self::STATUS_PENDING_DISBURSEMENT) {
            throw new \Exception('El préstamo ya ha sido desembolsado');
        }

        $this->disbursement_date = now();
        $this->status = self::STATUS_ACTIVE;

        // Calcular primera fecha de pago basada en el día de desembolso
        $paymentDates = $this->calculatePaymentDates();
        $firstPaymentDate = $paymentDates[0];

        $this->start_date = $firstPaymentDate;
        $this->next_payment_date = $firstPaymentDate;

        // La fecha de finalización será la última fecha del cronograma
        $this->end_date = end($paymentDates);

        // Calcular cuota
        $monthlyRate = $this->interest_rate / 100;
        $amount = $this->amount;
        $term = $this->term_months;

        // Cálculo de cuota según frecuencia
        if ($this->payment_frequency === self::PAYMENT_FREQUENCY_BIWEEKLY) {
            $totalPayments = $term * 2;
            $principal = $amount / $totalPayments;
            $interest = $amount * ($monthlyRate / 2); // Tasa quincenal
            $this->monthly_payment = round($principal + $interest, 2);
        } else {
            $principal = $amount / $term;
            $interest = $amount * $monthlyRate;
            $this->monthly_payment = round($principal + $interest, 2);
        }

        $this->save();

        // Generar tabla de amortización y crear pagos programados
        $schedule = $this->generateAmortizationSchedule();
        $this->createScheduledPayments($schedule);
    }

    // Método para calcular fechas de pago
    protected function calculatePaymentDates(): array
    {
        $dates = [];
        $currentDate = Carbon::parse($this->disbursement_date);
        $totalPayments = $this->payment_frequency === self::PAYMENT_FREQUENCY_BIWEEKLY
            ? $this->term_months * 2
            : $this->term_months;

        // Determinar primera fecha de pago
        if ($currentDate->day <= 15) {
            // Si se desembolsa antes del 15, primer pago será fin de mes
            $firstPayment = $currentDate->copy()->endOfMonth();
        } else {
            // Si se desembolsa después del 15, primer pago será 15 del siguiente mes
            $firstPayment = $currentDate->copy()
                ->startOfMonth()  // Primero ir al inicio del mes
                ->addMonth()      // Luego sumar el mes
                ->addDays(14);    // Finalmente agregar los 14 días
        }

        $dates[] = $firstPayment;
        $workingDate = $firstPayment->copy();

        // Generar fechas restantes
        for ($i = 1; $i < $totalPayments; $i++) {
            if ($this->payment_frequency === self::PAYMENT_FREQUENCY_BIWEEKLY) {
                if ($dates[$i - 1]->day > 15) {
                    // Si el último pago fue fin de mes, el siguiente es día 15
                    $nextDate = $workingDate->copy()
                        ->startOfMonth()  // Primero ir al inicio del mes
                        ->addMonth()      // Luego sumar el mes
                        ->addDays(14);    // Finalmente agregar los 14 días
                } else {
                    // Si el último pago fue día 15, el siguiente es fin del mismo mes
                    $nextDate = $workingDate->copy()->endOfMonth();
                }
            } else {
                // Para pagos mensuales
                if ($workingDate->day > 28) {
                    $nextDate = $workingDate->copy()
                        ->startOfMonth()
                        ->addMonth()
                        ->endOfMonth();
                } else {
                    $nextDate = $workingDate->copy()->addMonth();
                }
            }

            $dates[] = $nextDate;
            $workingDate = $nextDate->copy();
        }

        return $dates;
    }

    // Generar tabla de amortización
    public function generateAmortizationSchedule(): array
    {
        if ($this->status === self::STATUS_PENDING_DISBURSEMENT) {
            throw new \Exception('El préstamo aún no ha sido desembolsado');
        }

        $schedule = [];
        $balance = $this->amount;
        $monthlyRate = $this->interest_rate / 100;
        $paymentDates = $this->calculatePaymentDates();

        // Ajustar cálculos según frecuencia de pago
        if ($this->payment_frequency === self::PAYMENT_FREQUENCY_BIWEEKLY) {
            $totalPayments = $this->term_months * 2;
            $fixedPrincipal = round($this->amount / $totalPayments, 2);
            $periodRate = $monthlyRate / 2; // Tasa quincenal
        } else {
            $totalPayments = $this->term_months;
            $fixedPrincipal = round($this->amount / $totalPayments, 2);
            $periodRate = $monthlyRate; // Tasa mensual
        }

        // Registro inicial (desembolso)
        $schedule[] = [
            'payment_number' => 0,
            'date' => $this->disbursement_date->format('Y-m-d'),
            'monthly_rate' => $monthlyRate * 100,
            'payment_amount' => 0,
            'interest' => 0,
            'principal' => 0,
            'balance' => $this->amount,
            'accumulated_principal' => 0
        ];

        // Generar cronograma de pagos
        foreach ($paymentDates as $i => $paymentDate) {
            $interest = round($balance * $periodRate, 2);
            $principal = $fixedPrincipal;
            $paymentAmount = $principal + $interest;
            $balance -= $principal;
            $accumulatedPrincipal = $fixedPrincipal * ($i + 1);

            $schedule[] = [
                'payment_number' => $i + 1,
                'date' => $paymentDate->format('Y-m-d'),
                'monthly_rate' => $monthlyRate * 100,
                'payment_amount' => $paymentAmount,
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
        // Omitimos el primer registro (desembolso)
        array_shift($schedule);

        foreach ($schedule as $payment) {
            LoanPaymentLitoral::create([
                'active_loan_litoral_id' => $this->id,
                'payment_number' => $payment['payment_number'],
                'scheduled_date' => $payment['date'],
                'principal_amount' => $payment['principal'],
                'interest_amount' => $payment['interest'],
                'remaining_balance' => $payment['balance'],
                'amount_paid' => 0,
                'status' => LoanPaymentLitoral::STATUS_PENDING,
                'registered_by' => auth()->id()
            ]);
        }
    }

    // Aplicar un pago al préstamo
    public function applyPayment($amount, $paymentDate, $receiptNumber = null, $notes = null): LoanPaymentLitoral
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            throw new \Exception('No se pueden aplicar pagos a préstamos que no estén activos');
        }

        // Obtener el siguiente pago pendiente
        $payment = $this->payments()
            ->where('status', LoanPaymentLitoral::STATUS_PENDING)
            ->orderBy('payment_number')
            ->first();

        if (!$payment) {
            throw new \Exception('No hay pagos pendientes');
        }

        // Calculamos los valores usando la amortización
        $schedule = $this->generateAmortizationSchedule();
        $paymentValues = $schedule[$payment->payment_number] ?? null;

        if (!$paymentValues) {
            throw new \Exception('Error al obtener los valores de la cuota');
        }

        // Actualizar el registro de pago
        $payment->update([
            'payment_date' => $paymentDate,
            'amount_paid' => $amount,
            'receipt_number' => $receiptNumber,
            'notes' => $notes,
            'status' => $amount >= ($paymentValues['principal'] + $paymentValues['interest'])
                ? LoanPaymentLitoral::STATUS_PAID
                : LoanPaymentLitoral::STATUS_PARTIAL,
            'registered_by' => auth()->id()
        ]);

        $this->updateLoanStatus();

        return $payment;
    }

    // Actualizar estado del préstamo
    protected function updateLoanStatus(): void
    {
        $this->refresh();

        if ($this->status === self::STATUS_PENDING_DISBURSEMENT) {
            return;
        }

        // Calcular pagos totales
        $totalPaid = $this->payments()
            ->whereIn('status', [LoanPaymentLitoral::STATUS_PAID])
            ->sum('amount_paid');

        $totalPrincipalPaid = $this->payments()
            ->whereIn('status', [LoanPaymentLitoral::STATUS_PAID])
            ->sum('principal_amount');

        $totalInterestPaid = $this->payments()
            ->whereIn('status', [LoanPaymentLitoral::STATUS_PAID])
            ->sum('interest_amount');

        // Actualizar contadores de pagos
        $this->payments_made = $this->payments()
            ->whereIn('status', [LoanPaymentLitoral::STATUS_PAID])
            ->count();
        $this->payments_remaining = $this->total_payments - $this->payments_made;

        // Actualizar totales
        $this->total_paid = $totalPaid;
        $this->total_principal_paid = $totalPrincipalPaid;
        $this->total_interest_paid = $totalInterestPaid;

        // Actualizar saldo actual
        $this->current_balance = max(0, $this->amount - $totalPrincipalPaid);

        // Determinar estado
        if ($this->current_balance <= 1) {
            $this->status = self::STATUS_COMPLETED;
        }

        $this->save();
    }

    // Verificar si el préstamo tiene pagos atrasados
    public function hasLatePayments(): bool
    {
        return $this->payments()
            ->whereDate('scheduled_date', '<', now())
            ->where('status', LoanPaymentLitoral::STATUS_PENDING)
            ->exists();
    }

    // Obtener próximo pago pendiente
    public function getNextPayment()
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return null;
        }

        return $this->payments()
            ->where('status', LoanPaymentLitoral::STATUS_PENDING)
            ->orderBy('payment_number')
            ->first();
    }

    // Calcular progreso del préstamo
    public function getProgressPercentage(): float
    {
        if ($this->amount <= 0) return 0;
        return ($this->total_principal_paid / $this->amount) * 100;
    }

    // Verificar si el préstamo está al día
    public function isUpToDate(): bool
    {
        return !$this->hasLatePayments() && $this->status === self::STATUS_ACTIVE;
    }
}

class LoanPaymentLitoral extends Model
{
    use SoftDeletes;

    protected $table = 'loan_payments_litorals';

    protected $fillable = [
        'active_loan_litoral_id',
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

    const PAYMENT_TYPES = [
        self::STATUS_PENDING => 'Pendiente',
        self::STATUS_PAID => 'Pagado',
        self::STATUS_PARTIAL => 'Pago Parcial',
        self::STATUS_LATE => 'Atrasado'
    ];

    public function activeLoanLitoral(): BelongsTo
    {
        return $this->belongsTo(ActiveLoanLitoral::class, 'active_loan_litoral_id');
    }

    public function registeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    // Verificar si el pago está atrasado
    public function isLate(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->scheduled_date < now();
    }

    // Actualizar estado del pago
    public function updatePaymentStatus(): void
    {
        if ($this->status === self::STATUS_PENDING && $this->scheduled_date < now()) {
            $this->status = self::STATUS_LATE;
            $this->save();
        }
    }

    // Obtener el monto total del pago
    public function getTotalAmount(): float
    {
        return $this->principal_amount + $this->interest_amount;
    }

    // Verificar si el pago está completo
    public function isFullyPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    // Verificar si el pago es parcial
    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    // Obtener el monto pendiente
    public function getPendingAmount(): float
    {
        return $this->getTotalAmount() - $this->amount_paid;
    }

    // Verificar si el pago tiene recibo
    public function hasReceipt(): bool
    {
        return !empty($this->receipt_number) || !empty($this->receipt_file);
    }

    // Boot method para eventos del modelo
    protected static function boot()
    {
        parent::boot();

        // Antes de guardar, verificar si el pago está atrasado
        static::saving(function ($payment) {
            if ($payment->status === self::STATUS_PENDING && $payment->scheduled_date < now()) {
                $payment->status = self::STATUS_LATE;
            }
        });
    }
}
