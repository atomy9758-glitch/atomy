<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PvLedger;
use App\Models\AdminAudit;
use App\Services\PvService;
use Illuminate\Http\Request;

class PvController extends Controller
{
    protected $pvService;

    public function __construct(PvService $pvService)
    {
        $this->pvService = $pvService;
    }

    public function index(Request $request)
    {
        return view('admin.pv');
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'pv_amount' => 'required|numeric',
            'type' => 'required|in:ORDER,CANCEL,ADJUST',
            'ref_no' => 'nullable|string|max:100',
            'memo' => 'nullable|string',
        ]);

        $ledger = $this->pvService->recordAndPropagate(
            $request->member_id,
            $request->pv_amount,
            $request->type,
            $request->ref_no,
            $request->memo
        );

        // Audit 로그
        AdminAudit::create([
            'admin_id' => auth()->id(),
            'action' => 'ADJUST_PV',
            'target_type' => 'PvLedger',
            'target_id' => $ledger->id,
            'detail' => [
                'member_id' => $request->member_id,
                'pv_amount' => $request->pv_amount,
                'type' => $request->type,
            ],
            'ip_address' => request()->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'PV 입력 완료', 'ledger' => $ledger], 201);
        }

        return redirect()->back()->with('success', 'PV 입력 완료');
    }

    public function ledger(Request $request)
    {
        $query = PvLedger::with('member');

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('ref_no')) {
            $query->where('ref_no', 'like', "%{$request->ref_no}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $ledgers = $query->orderBy('occurred_at', 'desc')->paginate(50);

        return response()->json($ledgers);
    }
}
