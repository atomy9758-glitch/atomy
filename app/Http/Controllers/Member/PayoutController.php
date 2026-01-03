<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PayoutHistory;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $member = auth()->user();
        $query = PayoutHistory::where('member_id', $member->id);

        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        $payouts = $query->orderBy('paid_at', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($payouts);
        }

        return view('member.payouts', compact('payouts'));
    }
}
