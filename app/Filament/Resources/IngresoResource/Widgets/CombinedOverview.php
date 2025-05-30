<?php

namespace App\Filament\Resources\IngresoResource\Widgets;

use App\Models\Ingreso;
use App\Models\Gasto;
use App\Models\ActiveLoan;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CombinedOverview extends Widget
{
    protected static string $view = 'filament.resources.ingreso-resource.widgets.combined-overview';
    protected int | string | array $columnSpan = 'full';

    // Definir las constantes que necesitamos aquí
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';

    public $data = [];
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->filterResults();
    }

    public function filterResults()
    {
        $this->data = $this->getData();
    }

    public function getData()
    {
        return [
            'totalIngresos' => $this->getTotalIngresos(),
            'countIngresos' => $this->getCountIngresos(),
            'totalGastos' => $this->getTotalGastos(),
            'countGastos' => $this->getCountGastos(),
            'balance' => $this->getBalance(),
            'activeLoansTotal' => $this->getActiveLoansTotal(),
            'latePaymentsBalance' => $this->getLatePaymentsBalance(),
            'loanPaymentsIncome' => $this->getLoanPaymentsIncome(),
            'activeLoansCount' => $this->getActiveLoansCount(),
            'overdueBalance' => $this->getOverdueBalance(), // Add this line
        ];
    }

    public function getTotalIngresos(): string
    {
        $regularIncome = Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        $loanPayments = DB::table('loan_payments')
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->where('status', self::STATUS_PAID)
            ->sum('amount_paid');

        return number_format($regularIncome + $loanPayments, 2, ',', '.');
    }

    public function getCountIngresos(): int
    {
        $regularCount = Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->count();

        $loanPaymentsCount = DB::table('loan_payments')
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->where('status', self::STATUS_PAID)
            ->count();

        return $regularCount + $loanPaymentsCount;
    }

    public function getTotalGastos(): string
    {
        $regularExpenses = Gasto::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        // Ahora incluimos todos los préstamos desembolsados en el período,
        // sin importar su estado actual
        $loanDisbursements = ActiveLoan::query()
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->sum('amount');

        return number_format($regularExpenses + $loanDisbursements, 2, ',', '.');
    }

    // También necesitamos actualizar el contador de gastos
    public function getCountGastos(): int
    {
        $regularCount = Gasto::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->count();

        // Contamos todos los préstamos desembolsados en el período
        $loanDisbursementsCount = ActiveLoan::query()
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->count();

        return $regularCount + $loanDisbursementsCount;
    }

    // Y también el balance
    public function getBalance(): string
    {
        $ingresos = Ingreso::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        $loanPayments = DB::table('loan_payments')
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->where('status', self::STATUS_PAID)
            ->sum('amount_paid');

        $gastos = Gasto::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        // Incluimos todos los préstamos desembolsados
        $loanDisbursements = ActiveLoan::query()
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->sum('amount');

        return number_format(($ingresos + $loanPayments) - ($gastos + $loanDisbursements), 2, ',', '.');
    }

    public function getActiveLoansTotal(): string
    {
        $total = ActiveLoan::query()
            ->where('status', ActiveLoan::STATUS_ACTIVE)
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->sum('current_balance');

        return number_format($total, 2, ',', '.');
    }

    public function getActiveLoansCount(): int
    {
        return ActiveLoan::query()
            ->where('status', ActiveLoan::STATUS_ACTIVE)
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->count();
    }

    public function getLatePaymentsBalance(): string
    {
        // Obtener los préstamos activos y completados dentro del rango de fechas
        $loans = ActiveLoan::whereIn('status', [
            ActiveLoan::STATUS_ACTIVE,
            ActiveLoan::STATUS_COMPLETED
        ])
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->get();

        $totalBalance = 0;

        foreach ($loans as $loan) {
            // Obtener los pagos pendientes
            $pendingPayments = $loan->payments()
                ->where('status', 'pending')
                ->get();

            // Sumar el monto principal y el interés de cada pago pendiente
            foreach ($pendingPayments as $payment) {
                $totalBalance += $payment->interest_amount;
            }
        }

        return number_format($totalBalance, 2, ',', '.');
    }

    public function getLoanPaymentsIncome(): string
    {
        $paymentsIncome = DB::table('loan_payments')
            ->whereBetween('payment_date', [$this->startDate, $this->endDate])
            ->whereIn('status', [self::STATUS_PAID, self::STATUS_PARTIAL])
            ->sum('amount_paid');

        return number_format($paymentsIncome, 2, ',', '.');
    }
    public function getOverdueBalance(): string
    {
        // Get the current month's 6th day
        $sixthDay = Carbon::parse($this->endDate)->startOfMonth()->addDays(5);

        // Get active loans
        $loans = ActiveLoan::whereIn('status', [
            ActiveLoan::STATUS_ACTIVE,
            ActiveLoan::STATUS_COMPLETED
        ])
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->get();

        $overdueBalance = 0;

        foreach ($loans as $loan) {
            // Get payments that were due before the 6th of the month and are still pending
            $overduePayments = $loan->payments()
                ->where('status', 'pending')
                ->where('scheduled_date', '<', $sixthDay) // Cambiado de due_date a scheduled_date
                ->get();

            // Sum both principal and interest for overdue payments
            foreach ($overduePayments as $payment) {
                $overdueBalance += $payment->getTotalAmount(); // Usando el método del modelo que suma principal + interés
            }
        }

        return number_format($overdueBalance, 2, ',', '.');
    }
    public function render(): View
    {
        return view(static::$view, [
            'data' => $this->getData(),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}
