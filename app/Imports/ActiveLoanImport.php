<?php

namespace App\Imports;

use App\Models\ActiveLoan;
use App\Models\LoanPayment;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ActiveLoanImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Obtener los encabezados de la primera fila
        $headers = $rows->shift();

        foreach ($rows as $row) {
            try {
                // El número de préstamo está en la primera columna
                $loanNumber = $row[0] ?? null;
                // El valor para pagar está en la última columna
                $shouldPay = $row->last() == 1;

                if ($loanNumber && $shouldPay) {
                    DB::beginTransaction();
                    try {
                        // Buscar el préstamo
                        $loan = ActiveLoan::where('loan_number', $loanNumber)->first();

                        if ($loan) {
                            $nextPayment = $loan->getNextPayment();

                            if ($nextPayment) {
                                // Actualizar solo el pago sin cambiar el estado del préstamo
                                $nextPayment->update([
                                    'payment_date' => now(),
                                    'amount_paid' => $nextPayment->getPaymentQuota(),
                                    'status' => LoanPayment::STATUS_PAID,
                                    'notes' => 'Pago por importación de Excel'
                                ]);

                                // Actualizar los totales del préstamo
                                $loan->total_paid = $loan->payments()
                                    ->where('status', LoanPayment::STATUS_PAID)
                                    ->sum('amount_paid');

                                $loan->total_principal_paid = $loan->payments()
                                    ->where('status', LoanPayment::STATUS_PAID)
                                    ->sum('principal_amount');

                                $loan->total_interest_paid = $loan->payments()
                                    ->where('status', LoanPayment::STATUS_PAID)
                                    ->sum('interest_amount');

                                $loan->current_balance = max(0, $loan->amount - $loan->total_principal_paid);
                                $loan->save();

                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Pago registrado')
                                    ->body("Pago aplicado al préstamo {$loanNumber}")
                                    ->send();
                            }
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        throw $e;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error procesando préstamo {$loanNumber}: " . $e->getMessage());

                \Filament\Notifications\Notification::make()
                    ->danger()
                    ->title('Error al procesar pago')
                    ->body("Error en préstamo {$loanNumber}: " . $e->getMessage())
                    ->send();
            }
        }
    }
}
