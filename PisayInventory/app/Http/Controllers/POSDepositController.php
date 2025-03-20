<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class POSDepositController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('students')
            ->leftJoin('student_deposits', 'students.student_id', '=', 'student_deposits.student_id')
            ->select(
                'students.student_id',
                DB::raw('CONCAT(students.first_name, " ", students.last_name) as student_name'),
                DB::raw('COALESCE(SUM(student_deposits.Amount * CASE WHEN student_deposits.TransactionType = "DEPOSIT" THEN 1 ELSE -1 END), 0) as balance'),
                DB::raw('MAX(CASE WHEN student_deposits.TransactionType = "DEPOSIT" THEN student_deposits.TransactionDate END) as last_deposit'),
                DB::raw('MAX(CASE WHEN student_deposits.TransactionType = "DEPOSIT" THEN student_deposits.Amount END) as last_deposit_amount'),
                DB::raw('MAX(student_deposits.TransactionDate) as last_transaction')
            )
            ->where('students.status', 'Active')
            ->whereNull('student_deposits.deleted_at')
            ->groupBy('students.student_id', 'students.first_name', 'students.last_name');

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('students.student_id', 'like', "%{$search}%")
                  ->orWhere('students.first_name', 'like', "%{$search}%")
                  ->orWhere('students.last_name', 'like', "%{$search}%");
            });
        }

        // Apply date filters
        if ($request->has('date_from')) {
            $query->where('student_deposits.TransactionDate', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->has('date_to')) {
            $query->where('student_deposits.TransactionDate', '<=', $request->date_to . ' 23:59:59');
        }

        $deposits = $query->paginate(10);

        return view('pos.deposits.index', compact('deposits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'notes' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Get current balance
            $currentBalance = DB::table('student_deposits')
                ->where('student_id', $request->student_id)
                ->whereNull('deleted_at')
                ->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END'));

            // Check if new balance would exceed the database limit
            if (($currentBalance + $request->amount) > 9999999.99) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deposit amount would cause balance to exceed the maximum limit of ₱9,999,999.99'
                ], 422);
            }

            // Generate reference number
            $referenceNumber = 'DEP-' . Carbon::now()->format('Ymd') . '-' . Str::padLeft(
                Deposit::whereDate('created_at', Carbon::today())->count() + 1, 4, '0'
            );

            // Create deposit record
            $deposit = Deposit::create([
                'student_id' => $request->student_id,
                'TransactionDate' => Carbon::now(),
                'ReferenceNumber' => $referenceNumber,
                'TransactionType' => 'DEPOSIT',
                'Amount' => $request->amount,
                'BalanceBefore' => $currentBalance,
                'BalanceAfter' => $currentBalance + $request->amount,
                'Notes' => $request->notes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deposit added successfully',
                'deposit' => $deposit
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history($studentId)
    {
        $student = Student::where('student_id', $studentId)->firstOrFail();
        
        // Get current balance
        $balance = DB::table('student_deposits')
            ->where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->sum(DB::raw('Amount * CASE WHEN TransactionType = "DEPOSIT" THEN 1 ELSE -1 END'));

        $transactions = Deposit::where('student_id', $studentId)
            ->orderBy('TransactionDate', 'desc')
            ->paginate(15);

        return view('pos.deposits._history', compact('student', 'transactions', 'balance'));
    }

    public function studentSelect2(Request $request)
    {
        $search = $request->get('q');
        $students = Student::where('status', 'Active')
            ->where(function($query) use ($search) {
                $query->where('student_id', 'like', "%{$search}%")
                    ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json([
            'results' => $students->map(function($student) {
                return [
                    'id' => $student->student_id,
                    'text' => $student->student_id . ' - ' . $student->first_name . ' ' . $student->last_name
                ];
            })
        ]);
    }
} 