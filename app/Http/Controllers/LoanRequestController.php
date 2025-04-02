<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Models\LoanRequestLitoral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LoanRequestController extends Controller
{
    public function showForm()
    {
        // Usamos las constantes de cualquiera de los dos modelos ya que ambos tienen los mismos valores
        return view('public.loan-request-form', [
            'loanReasons' => LoanRequest::LOAN_REASONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_number' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'area' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'company' => 'required|in:espumas_medellin,espumados_litoral,ctn_carga',
            'loan_reason' => 'required|in:' . implode(',', array_keys(LoanRequest::LOAN_REASONS)),
            'description' => 'nullable|string',
            'guarantee_document' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // Determinar el modelo a usar basado en la empresa seleccionada
        if ($validated['company'] === 'espumados_litoral') {
            // Si es Espumados del Litoral, usamos ese modelo
            return $this->storeLitoralRequest($validated, $request);
        } else {
            // Para Espumas Medellin y CTN Carga, usamos el modelo principal
            return $this->storeMedellinRequest($validated, $request);
        }
    }

    private function storeMedellinRequest($validated, Request $request)
    {
        // Manejar el Documento
        if ($request->hasFile('guarantee_document')) {
            $path = $request->file('guarantee_document')->store('loan-documents', 'public');
            $validated['guarantee_document'] = $path;
        }

        // Asignar valores predeterminados
        $validated['loan_number'] = LoanRequest::generateLoanNumber();
        $validated['status'] = 'pending_approval';
        $validated['interest_rate'] = 1; // Valor fijo de 1%

        // Asignar un usuario responsable
        $validated['responsible_user_id'] = config('loans.default_admin_user_id', 1);
        $validated['created_by_user_id'] = config('loans.default_admin_user_id', 1);

        // Crear la solicitud
        LoanRequest::create($validated);

        return redirect()->route('public.loan-request.success');
    }

    private function storeLitoralRequest($validated, Request $request)
    {
        // Manejar el Documento
        if ($request->hasFile('guarantee_document')) {
            $path = $request->file('guarantee_document')->store('loan-documents-litoral', 'public');
            $validated['guarantee_document'] = $path;
        }

        // Asignar valores predeterminados
        $validated['loan_number'] = LoanRequestLitoral::generateLoanNumber();
        $validated['status'] = 'pending_approval';
        $validated['interest_rate'] = 1;

        // Asignar un usuario responsable
        $validated['responsible_user_id'] = config('loans.default_admin_user_id', 1);
        $validated['created_by_user_id'] = config('loans.default_admin_user_id', 1);

        // Crear la solicitud
        LoanRequestLitoral::create($validated);

        return redirect()->route('public.loan-request.success');
    }

    public function success()
    {
        return view('public.loan-request-success');
    }
}
