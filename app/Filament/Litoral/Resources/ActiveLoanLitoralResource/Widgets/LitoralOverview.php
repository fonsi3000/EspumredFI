<?php

namespace App\Filament\Litoral\Resources\ActiveloanLitoralResource\Widgets;

use App\Models\ActiveLoanLitoral;
use App\Models\GastoLitoral;
use App\Models\IngresoLitoral;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class LitoralOverview extends Widget
{
    protected static string $view = 'filament.litoral.resources.activeloan-litoral-resource.widgets.litoral-overview';
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

    protected function getData()
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
            'activeLoansCount' => $this->getActiveLoansCount()
        ];
    }

    public function getTotalIngresos(): string
    {
        // Ingresos regulares del litoral
        $regularIncome = IngresoLitoral::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        // Pagos de préstamos del litoral
        $loanPayments = DB::table('loan_payments_litorals')
            ->join('active_loans_litorals', 'loan_payments_litorals.active_loan_litoral_id', '=', 'active_loans_litorals.id')
            ->whereBetween('loan_payments_litorals.payment_date', [$this->startDate, $this->endDate])
            ->where('loan_payments_litorals.status', self::STATUS_PAID)
            ->sum('loan_payments_litorals.amount_paid');

        $total = $regularIncome + $loanPayments;

        return number_format($total, 2, ',', '.');
    }

    public function getCountIngresos(): int
    {
        // Contador de ingresos regulares
        $regularCount = IngresoLitoral::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->count();

        // Contador de pagos de préstamos
        $loanPaymentsCount = DB::table('loan_payments_litorals')
            ->join('active_loans_litorals', 'loan_payments_litorals.active_loan_litoral_id', '=', 'active_loans_litorals.id')
            ->whereBetween('loan_payments_litorals.payment_date', [$this->startDate, $this->endDate])
            ->where('loan_payments_litorals.status', self::STATUS_PAID)
            ->count();

        return $regularCount + $loanPaymentsCount;
    }

    public function getTotalGastos(): string
    {
        // Gastos regulares
        $regularExpenses = GastoLitoral::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        // Préstamos desembolsados
        $loanDisbursements = ActiveLoanLitoral::query()
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $total = $regularExpenses + $loanDisbursements;

        return number_format($total, 2, ',', '.');
    }

    public function getCountGastos(): int
    {
        // Contador de gastos regulares
        $regularCount = GastoLitoral::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->count();

        // Contador de préstamos desembolsados
        $loanDisbursementsCount = ActiveLoanLitoral::query()
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->count();

        return $regularCount + $loanDisbursementsCount;
    }

    public function getBalance(): string
    {
        // Ingresos regulares
        $ingresos = IngresoLitoral::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        // Pagos de préstamos recibidos
        $loanPayments = DB::table('loan_payments_litorals')
            ->join('active_loans_litorals', 'loan_payments_litorals.active_loan_litoral_id', '=', 'active_loans_litorals.id')
            ->whereBetween('loan_payments_litorals.payment_date', [$this->startDate, $this->endDate])
            ->where('loan_payments_litorals.status', self::STATUS_PAID)
            ->sum('loan_payments_litorals.amount_paid');

        // Gastos regulares
        $gastos = GastoLitoral::query()
            ->where('estado', 'activo')
            ->whereBetween('fecha', [$this->startDate, $this->endDate])
            ->sum('monto');

        // Préstamos desembolsados
        $loanDisbursements = ActiveLoanLitoral::query()
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $total = ($ingresos + $loanPayments) - ($gastos + $loanDisbursements);

        return number_format($total, 2, ',', '.');
    }

    public function getActiveLoansTotal(): string
    {
        $total = ActiveLoanLitoral::query()
            ->where('status', ActiveLoanLitoral::STATUS_ACTIVE)
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->sum('current_balance');

        return number_format($total, 2, ',', '.');
    }

    public function getActiveLoansCount(): int
    {
        return ActiveLoanLitoral::query()
            ->where('status', ActiveLoanLitoral::STATUS_ACTIVE)
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->count();
    }

    public function getLatePaymentsBalance(): string
    {
        // Obtener los préstamos activos y completados dentro del rango de fechas
        $loans = ActiveLoanLitoral::whereIn('status', [
            ActiveLoanLitoral::STATUS_ACTIVE,
            ActiveLoanLitoral::STATUS_COMPLETED
        ])
            ->whereBetween('disbursement_date', [$this->startDate, $this->endDate])
            ->get();

        $totalBalance = 0;

        foreach ($loans as $loan) {
            // Obtener los pagos pendientes
            $pendingPayments = $loan->payments()
                ->where('status', 'pending')
                ->get();

            // Sumar el interés de cada pago pendiente
            foreach ($pendingPayments as $payment) {
                $totalBalance += $payment->interest_amount;
            }
        }

        return number_format($totalBalance, 2, ',', '.');
    }

    public function getLoanPaymentsIncome(): string
    {
        $paymentsIncome = DB::table('loan_payments_litorals')
            ->join('active_loans_litorals', 'loan_payments_litorals.active_loan_litoral_id', '=', 'active_loans_litorals.id')
            ->whereBetween('loan_payments_litorals.payment_date', [$this->startDate, $this->endDate])
            ->whereIn('loan_payments_litorals.status', [self::STATUS_PAID, self::STATUS_PARTIAL])
            ->sum('loan_payments_litorals.amount_paid');

        return number_format($paymentsIncome, 2, ',', '.');
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
