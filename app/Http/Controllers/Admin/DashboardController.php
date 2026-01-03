<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PvBalance;
use App\Models\PayoutHistory;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_members' => Member::where('role', 'MEMBER')->count(),
            'active_members' => Member::where('role', 'MEMBER')->where('status', 'ACTIVE')->count(),
            'total_payouts_today' => PayoutHistory::whereDate('paid_at', today())->count(),
            'total_payout_amount_today' => PayoutHistory::whereDate('paid_at', today())->sum('payout_amount'),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function apiStats()
    {
        return response()->json([
            'total_members' => Member::where('role', 'MEMBER')->count(),
            'active_members' => Member::where('role', 'MEMBER')->where('status', 'ACTIVE')->count(),
            'total_payouts_today' => PayoutHistory::whereDate('paid_at', today())->count(),
            'total_payout_amount_today' => PayoutHistory::whereDate('paid_at', today())->sum('payout_amount'),
        ]);
    }
}
