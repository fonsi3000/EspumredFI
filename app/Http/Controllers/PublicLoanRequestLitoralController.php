<?php

namespace App\Http\Controllers;

use App\Models\LoanRequestLitoral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicLoanRequestLitoralController extends Controller
{
    public function showForm()
    {
        return view('public.loan-request-litoral-form', [
            'loanReasons' => LoanRequestLitoral::LOAN_REASONS,
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
            'loan_reason' => 'required|in:' . implode(',', array_keys(LoanRequestLitoral::LOAN_REASONS)),
            'description' => 'nullable|string',
            'guarantee_document' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('guarantee_document')) {
            $path = $request->file('guarantee_document')->store('loan-documents-litoral', 'public');
            $validated['guarantee_document'] = $path;
        }

        $validated['loan_number'] = LoanRequestLitoral::generateLoanNumber();
        $validated['status'] = 'pending_approval';
        $validated['interest_rate'] = 1;

        $validated['responsible_user_id'] = config('loans.default_admin_user_id', 1);
        $validated['created_by_user_id'] = config('loans.default_admin_user_id', 1);

        LoanRequestLitoral::create($validated);

        return redirect()->route('public.loan-request-litoral.success');
    }

    public function success()
    {
        return view('public.loan-request-litoral-success');
    }
}
