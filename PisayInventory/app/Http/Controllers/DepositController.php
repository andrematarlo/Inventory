<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashDeposit;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = CashDeposit::with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $students = Student::whereNull('deleted_at')->get();

        return view('pos.deposits.index', compact('deposits', 'students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $balance = CashDeposit::where('student_id', $request->student_id)
                ->whereNull('deleted_at')
                ->sum('Amount');

            CashDeposit::create([
                'student_id' => $request->student_id,
                'TransactionDate' => now(),
                'ReferenceNumber' => null, // Will be auto-generated
                'TransactionType' => 'deposit',
                'Amount' => $request->amount,
                'BalanceBefore' => $balance,
                'BalanceAfter' => $balance + $request->amount,
                'Notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('deposits.index')
                ->with('success', 'Deposit recorded successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function history($studentId)
    {
        $student = Student::findOrFail($studentId);
        
        $transactions = CashDeposit::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $balance = CashDeposit::where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->sum('Amount');

        return view('pos.deposits.history', compact('student', 'transactions', 'balance'));
    }
} 