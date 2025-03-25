<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashDeposit;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = CashDeposit::with('student')
                              ->orderBy('created_at', 'desc')
                              ->paginate(15);
        return view('pos.deposits.index', compact('deposits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|exists:students,student_id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();
            
            // Check if student exists
            $student = DB::table('students')
                ->where('student_id', $request->student_id)
                ->first();
                
            if (!$student) {
                return redirect()->back()->with('error', 'Student not found with ID: ' . $request->student_id);
            }
            
            // Get current balance
            $currentBalance = DB::table('student_deposits')
                ->where('student_id', $request->student_id)
                ->whereIn('Status', ['active', 'completed'])
                ->orderBy('created_at', 'desc')
                ->value('BalanceAfter') ?? 0;
            
            // Create deposit record
            $newDeposit = new CashDeposit();
            $newDeposit->student_id = $request->student_id;
            $newDeposit->TransactionDate = now();
            $newDeposit->ReferenceNumber = 'DEP-' . time();
            $newDeposit->TransactionType = 'DEPOSIT';
            $newDeposit->Amount = $request->amount;
            $newDeposit->BalanceBefore = $currentBalance;
            $newDeposit->BalanceAfter = $currentBalance + $request->amount;
            $newDeposit->Notes = $request->notes;
            $newDeposit->Status = 'active'; // For direct deposits by admin/cashier
            $newDeposit->save();
            
            DB::commit();
            
            return redirect()->route('deposits.index')
                ->with('success', 'Deposit of ₱' . number_format($request->amount, 2) . ' was successfully added for student ' . $student->first_name . ' ' . $student->last_name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to process deposit: ' . $e->getMessage());
        }
    }

    public function destroy(CashDeposit $deposit)
    {
        // Soft delete deposit logic
    }
    
    /**
     * Student select2 search for AJAX requests
     */
    public function studentSelect2(Request $request)
    {
        $term = $request->term ?? '';
        $page = $request->page ?? 1;
        $resultCount = 10;
        $offset = ($page - 1) * $resultCount;
        
        if (strlen($term) < 3) {
            return response()->json([
                'items' => [],
                'pagination' => [
                    'more' => false
                ]
            ]);
        }
        
        $students = DB::table('students')
            ->whereNull('deleted_at')
            ->where(function($query) use ($term) {
                $query->where('student_id', 'LIKE', "%{$term}%")
                      ->orWhere('first_name', 'LIKE', "%{$term}%")
                      ->orWhere('last_name', 'LIKE', "%{$term}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
            })
            ->select('student_id', 'first_name', 'last_name', 'grade_level', 'section')
            ->orderBy('last_name')
            ->offset($offset)
            ->limit($resultCount)
            ->get();
            
        $total = DB::table('students')
            ->whereNull('deleted_at')
            ->where(function($query) use ($term) {
                $query->where('student_id', 'LIKE', "%{$term}%")
                      ->orWhere('first_name', 'LIKE', "%{$term}%")
                      ->orWhere('last_name', 'LIKE', "%{$term}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
            })
            ->count();
            
        $endCount = $offset + $resultCount;
        $morePages = $total > $endCount;
            
        $results = [];
        foreach ($students as $student) {
            $results[] = [
                'id' => $student->student_id,
                'text' => "{$student->last_name}, {$student->first_name} ({$student->student_id}) - {$student->grade_level}-{$student->section}"
            ];
        }
        
        return response()->json([
            'items' => $results,
            'pagination' => [
                'more' => $morePages
            ]
        ]);
    }
    
    /**
     * Approve a pending deposit
     */
    public function approveDeposit($id)
    {
        try {
            DB::beginTransaction();
            
            // Get the deposit
            $deposit = CashDeposit::findOrFail($id);
            
            if (strtolower($deposit->Status) !== 'pending') {
                return redirect()->back()->with('error', 'Only pending deposits can be approved.');
            }
            
            // Get current balance
            $currentBalance = CashDeposit::where('student_id', $deposit->student_id)
                ->whereIn('Status', ['active', 'completed'])
                ->orderBy('created_at', 'desc')
                ->value('BalanceAfter') ?? 0;
                
            // Calculate new balance
            $newBalance = $currentBalance + $deposit->Amount;
            
            // Update the deposit
            $deposit->Status = 'active';
            $deposit->BalanceBefore = $currentBalance;
            $deposit->BalanceAfter = $newBalance;
            $deposit->save();
                
            DB::commit();
            
            // Get student info for the success message
            $student = DB::table('students')
                ->where('student_id', $deposit->student_id)
                ->first();
                
            return redirect()->route('deposits.index')
                ->with('success', 'Deposit of ₱' . number_format($deposit->Amount, 2) . 
                ' for ' . $student->first_name . ' ' . $student->last_name . 
                ' has been approved and added to their balance.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to approve deposit: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject a pending deposit
     */
    public function rejectDeposit($id)
    {
        try {
            DB::beginTransaction();
            
            // Get the deposit first
            $deposit = CashDeposit::findOrFail($id);
                
            if (strtolower($deposit->Status) !== 'pending') {
                return redirect()->back()->with('error', 'Only pending deposits can be rejected.');
            }
            
            // Update the deposit status
            $deposit->Status = 'rejected';
            $deposit->save();
            
            DB::commit();
            
            // Get student info for the message
            $student = DB::table('students')
                ->where('student_id', $deposit->student_id)
                ->first();
            
            return redirect()->route('deposits.index')
                ->with('success', 'Deposit of ₱' . number_format($deposit->Amount, 2) . 
                ' for ' . $student->first_name . ' ' . $student->last_name . 
                ' has been rejected.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to reject deposit: ' . $e->getMessage());
        }
    }
} 