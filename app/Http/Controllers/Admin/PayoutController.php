<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutHistory;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = PayoutHistory::with('member');

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        $payouts = $query->orderBy('paid_at', 'desc')->paginate(50);

        if ($request->expectsJson()) {
            return response()->json($payouts);
        }

        return view('admin.payouts', compact('payouts'));
    }

    public function show($id)
    {
        $payout = PayoutHistory::with(['member', 'rule'])->findOrFail($id);
        
        // 연결된 전파 로그 조회 (해당 사이클 기간 동안의 로그)
        $logs = \App\Models\PvPropagationLog::where('ancestor_id', $payout->member_id)
            ->whereBetween('occurred_at', [
                $payout->member->pvBalance->cycle_started_at ?? $payout->paid_at,
                $payout->paid_at
            ])
            ->with(['ledger', 'buyer'])
            ->get();

        return response()->json([
            'payout' => $payout,
            'propagation_logs' => $logs,
        ]);
    }
}
