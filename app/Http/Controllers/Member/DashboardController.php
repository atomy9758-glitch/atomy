<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PvBalance;

class DashboardController extends Controller
{
    public function index()
    {
        $member = auth()->user();
        $balance = PvBalance::firstOrCreate(
            ['member_id' => $member->id],
            ['left_pv' => 0, 'right_pv' => 0, 'left_arrear_pv' => 0, 'right_arrear_pv' => 0, 'cycle_no' => 0]
        );

        return view('member.dashboard', compact('member', 'balance'));
    }

    public function apiBalance()
    {
        $member = auth()->user();
        $balance = PvBalance::firstOrCreate(
            ['member_id' => $member->id],
            ['left_pv' => 0, 'right_pv' => 0, 'left_arrear_pv' => 0, 'right_arrear_pv' => 0, 'cycle_no' => 0]
        );

        return response()->json([
            'member' => $member,
            'balance' => $balance,
        ]);
    }
}
