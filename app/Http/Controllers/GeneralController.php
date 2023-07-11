<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneralController extends Controller
{
    public function monthly_deposit()
    {
        $monthly_deposit = Payment::query()
            ->where('user_id', Auth::id())
            ->where('category', '=', 'payment')
            ->where('type', '=', 'Deposit')
            ->where('status', '=', 'Successful')
            ->whereBetween('created_at', [
                now()->startOfMonth(), // Start of the current month
                now()->endOfMonth(),   // End of the current month
            ])
            ->sum('amount');

        return response()->json([
            'monthly_deposit' => $monthly_deposit,
        ]);
    }
}
