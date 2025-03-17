<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\CashDeposit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    /**
     * Display the cash deposit management interface.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $students = Student::orderBy('LastName')->get();
        
        // Get recent deposits for display
        $recentDeposits = CashDeposit::with('student', 'processedBy')
            ->orderBy('TransactionDate', 'desc')
            ->take(20)
            ->get();
            
        return view('pos.deposit.index', compact('students', 'recentDeposits'));
    }
    
    /**
     * Add a new deposit for a student.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addDeposit(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,StudentID',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            $deposit = new CashDeposit();
            $deposit->StudentID = $request->student_id;
            $deposit->Amount = $request->amount;
            $deposit->TransactionDate = now();
            $deposit->Description = $request->description ?? 'Cash Deposit';
            $deposit->TransactionType = 'Deposit';
            $deposit->ProcessedBy = Auth::id();
            $deposit->save();
            
            DB::commit();
            
            return redirect()->route('pos.deposit.index')
                ->with('success', 'Deposit added successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('pos.deposit.index')
                ->with('error', 'Failed to add deposit: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the current balance for a student.
     *
     * @param  int  $student
     * @return \Illuminate\Http\Response
     */
    public function getBalance($student)
    {
        $student = Student::findOrFail($student);
        
        $balance = CashDeposit::where('StudentID', $student->StudentID)
            ->sum('Amount');
            
        return response()->json([
            'success' => true,
            'student' => $student,
            'balance' => $balance,
        ]);
    }
    
    /**
     * Get transaction history for a student.
     *
     * @param  int  $student
     * @return \Illuminate\Http\Response
     */
    public function getHistory($student)
    {
        $student = Student::with('section.gradeLevelGroup')->findOrFail($student);
        
        $transactions = CashDeposit::with('processedBy')
            ->where('StudentID', $student->StudentID)
            ->orderBy('TransactionDate', 'desc')
            ->get();
            
        $balance = $transactions->sum('Amount');
        
        return view('pos.deposit.history', compact('student', 'transactions', 'balance'));
    }
} 