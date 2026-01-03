<?php

namespace App\Services;

use App\Models\Member;
use App\Models\PvBalance;
use App\Models\PayoutSetting;
use App\Models\PayoutHistory;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    /**
     * 수당 지급 기록 생성 + PV 리셋
     * 
     * @param Member $member
     * @param PvBalance $balance
     * @param PayoutSetting $setting
     * @return PayoutHistory|null
     */
    public function createPayout(Member $member, PvBalance $balance, PayoutSetting $setting): ?PayoutHistory
    {
        return DB::transaction(function () use ($member, $balance, $setting) {
            // 중복 체크 (이미 이번 사이클 지급 기록이 있는지)
            $exists = PayoutHistory::where('member_id', $member->id)
                ->where('cycle_no', $balance->cycle_no)
                ->exists();

            if ($exists) {
                // 이미 지급됨 (동시성 방어)
                return null;
            }

            // 스냅샷
            $leftSnapshot = $balance->left_pv;
            $rightSnapshot = $balance->right_pv;
            $minPv = min($leftSnapshot, $rightSnapshot);
            $wastedPv = $leftSnapshot + $rightSnapshot - ($minPv * 2);

            // 지급 기록 생성
            $payout = PayoutHistory::create([
                'member_id' => $member->id,
                'cycle_no' => $balance->cycle_no,
                'paid_at' => now(),
                'threshold_pv' => $setting->threshold_pv,
                'payout_amount' => $setting->payout_amount,
                'left_pv_snapshot' => $leftSnapshot,
                'right_pv_snapshot' => $rightSnapshot,
                'wasted_pv' => $wastedPv,
                'status' => 'PENDING',
                'rule_id' => $setting->id,
                'search_text' => "{$member->name} {$member->username} cycle:{$balance->cycle_no}",
            ]);

            // PV 리셋 + cycle 증가
            $balance->left_pv = 0;
            $balance->right_pv = 0;
            $balance->cycle_no += 1;
            $balance->cycle_started_at = now();
            $balance->save();

            return $payout;
        });
    }

    /**
     * 수당 상태 업데이트 (PENDING → COMPLETED)
     * 
     * @param int $payoutId
     * @param string $status
     * @param string|null $note
     * @return PayoutHistory
     */
    public function updatePayoutStatus(int $payoutId, string $status, ?string $note = null): PayoutHistory
    {
        return DB::transaction(function () use ($payoutId, $status, $note) {
            $payout = PayoutHistory::lockForUpdate()->findOrFail($payoutId);
            $payout->status = $status;
            if ($note) {
                $payout->note = $note;
            }
            $payout->save();

            return $payout;
        });
    }

    /**
     * 최신 수당 설정 조회
     * 
     * @return PayoutSetting|null
     */
    public function getCurrentSetting(): ?PayoutSetting
    {
        return PayoutSetting::where('effective_from', '<=', now())
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    /**
     * 새 수당 설정 추가
     * 
     * @param float $thresholdPv
     * @param float $payoutAmount
     * @param int|null $createdBy
     * @return PayoutSetting
     */
    public function createSetting(float $thresholdPv, float $payoutAmount, ?int $createdBy = null): PayoutSetting
    {
        return PayoutSetting::create([
            'threshold_pv' => $thresholdPv,
            'payout_amount' => $payoutAmount,
            'effective_from' => now(),
            'created_by' => $createdBy,
        ]);
    }
}
