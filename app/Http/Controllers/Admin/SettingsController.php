<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutSetting;
use App\Models\AdminAudit;
use App\Services\PayoutService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function index()
    {
        $settings = PayoutSetting::orderBy('effective_from', 'desc')->get();

        if (request()->expectsJson()) {
            return response()->json($settings);
        }

        return view('admin.settings', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'threshold_pv' => 'required|numeric|min:0',
            'payout_amount' => 'required|numeric|min:0',
        ]);

        $setting = $this->payoutService->createSetting(
            $request->threshold_pv,
            $request->payout_amount,
            auth()->id()
        );

        // Audit 로그
        AdminAudit::create([
            'admin_id' => auth()->id(),
            'action' => 'UPDATE_SETTING',
            'target_type' => 'PayoutSetting',
            'target_id' => $setting->id,
            'detail' => [
                'threshold_pv' => $request->threshold_pv,
                'payout_amount' => $request->payout_amount,
            ],
            'ip_address' => request()->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => '설정 추가 완료', 'setting' => $setting], 201);
        }

        return redirect()->back()->with('success', '설정 추가 완료');
    }
}
