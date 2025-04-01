<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PublicLoanRequestController extends Controller
{
    public function showForm()
    {
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
            'amount' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'loan_reason' => 'required|in:' . implode(',', array_keys(LoanRequest::LOAN_REASONS)),
            'guarantee_document' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // Manejar el documento de garantía
        if ($request->hasFile('guarantee_document')) {
            $path = $request->file('guarantee_document')->store('loan-documents', 'public');
            $validated['guarantee_document'] = $path;
        }

        // Asignar valores predeterminados
        $validated['loan_number'] = LoanRequest::generateLoanNumber();
        $validated['status'] = 'pending_approval';
        $validated['interest_rate'] = 1; // Valor fijo de 1%
        $validated['payment_frequency'] = 'monthly'; // Valor fijo de mensual

        // Asignar un usuario responsable (por ejemplo, un administrador predeterminado)
        $validated['responsible_user_id'] = config('loans.default_admin_user_id', 1);
        $validated['created_by_user_id'] = config('loans.default_admin_user_id', 1);

        // Crear la solicitud
        LoanRequest::create($validated);

        return redirect()->route('public.loan-request.success');
    }

    public function success()
    {
        return view('public.loan-request-success');
    }
}
