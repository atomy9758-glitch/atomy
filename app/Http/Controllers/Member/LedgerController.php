<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PvLedger;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $member = auth()->user();
        $query = PvLedger::where('member_id', $member->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $ledgers = $query->orderBy('occurred_at', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($ledgers);
        }

        return view('member.ledger', compact('ledgers'));
    }
}
