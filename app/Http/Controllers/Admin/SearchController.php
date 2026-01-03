<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PvLedger;
use App\Models\PayoutHistory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->filled('keyword')) {
            return view('admin.search', ['results' => []]);
        }

        $keyword = $request->keyword;
        $results = [
            'members' => Member::where('username', 'like', "%{$keyword}%")
                ->orWhere('name', 'like', "%{$keyword}%")
                ->orWhere('phone', 'like', "%{$keyword}%")
                ->limit(10)
                ->get(),
            'ledgers' => PvLedger::with('member')
                ->where('ref_no', 'like', "%{$keyword}%")
                ->orWhere('memo', 'like', "%{$keyword}%")
                ->limit(10)
                ->get(),
            'payouts' => PayoutHistory::with('member')
                ->where('search_text', 'like', "%{$keyword}%")
                ->limit(10)
                ->get(),
        ];

        if ($request->expectsJson()) {
            return response()->json($results);
        }

        return view('admin.search', compact('results', 'keyword'));
    }
}
