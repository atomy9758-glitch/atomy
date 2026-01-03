<?php

namespace App\Services;

use App\Models\Member;
use App\Models\PvLedger;
use App\Models\PvBalance;
use App\Models\BinaryClosure;
use App\Models\PvPropagationLog;
use App\Models\PayoutSetting;
use Illuminate\Support\Facades\DB;

class PvService
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * PV 기록 + 전파 + 미수 관리 + 매칭 트리거
     * 
     * @param int $memberId 구매자
     * @param float $pvAmount +적립 또는 -취소/조정
     * @param string $type ORDER, CANCEL, ADJUST
     * @param string|null $refNo 주문번호 등
     * @param string|null $memo 메모
     * @return PvLedger
     */
    public function recordAndPropagate(
        int $memberId,
        float $pvAmount,
        string $type,
        ?string $refNo = null,
        ?string $memo = null
    ): PvLedger {
        return DB::transaction(function () use ($memberId, $pvAmount, $type, $refNo, $memo) {
            $occurredAt = now();

            // 1. 원장 기록
            $ledger = PvLedger::create([
                'member_id' => $memberId,
                'pv_amount' => $pvAmount,
                'occurred_at' => $occurredAt,
                'type' => $type,
                'ref_no' => $refNo,
                'memo' => $memo,
            ]);

            // 2. 본인 self_pv 갱신 (자격 체크)
            $this->updateMemberSelfPv($memberId, $pvAmount, $occurredAt);

            // 3. 상위로 PV 전파
            $this->propagateToAncestors($ledger, $memberId, $pvAmount, $occurredAt);

            return $ledger;
        });
    }

    /**
     * 본인 self_pv 갱신 및 자격 체크
     */
    protected function updateMemberSelfPv(int $memberId, float $pvAmount, $occurredAt): void
    {
        $member = Member::lockForUpdate()->find($memberId);
        if (!$member) {
            throw new \Exception("Member not found: {$memberId}");
        }

        $newSelfPv = $member->self_pv + $pvAmount;
        if ($newSelfPv < 0) {
            $newSelfPv = 0; // 0 바닥
        }

        // 자격 획득/상실 체크 (self_pv >= 1)
        if ($member->self_pv < 1 && $newSelfPv >= 1) {
            // 자격 획득
            $member->qualified_from_at = $occurredAt;
        } elseif ($newSelfPv < 1) {
            // 자격 상실
            $member->qualified_from_at = null;
        }

        $member->self_pv = $newSelfPv;
        $member->save();
    }

    /**
     * 상위로 PV 전파
     */
    protected function propagateToAncestors(PvLedger $ledger, int $buyerId, float $pvAmount, $occurredAt): void
    {
        // 상위 조회 (depth > 0, 가까운 순)
        $ancestors = BinaryClosure::where('descendant_id', $buyerId)
            ->where('depth', '>', 0)
            ->orderBy('depth', 'asc')
            ->get();

        foreach ($ancestors as $closure) {
            $ancestor = Member::lockForUpdate()->find($closure->ancestor_id);
            if (!$ancestor) {
                continue;
            }

            // 자격 체크: 소급 방지
            if (!$ancestor->qualified_from_at || $occurredAt < $ancestor->qualified_from_at) {
                continue;
            }

            // PV Balance 조회 또는 생성
            $balance = PvBalance::lockForUpdate()->firstOrCreate(
                ['member_id' => $ancestor->id],
                [
                    'left_pv' => 0,
                    'right_pv' => 0,
                    'left_arrear_pv' => 0,
                    'right_arrear_pv' => 0,
                    'cycle_no' => 0,
                ]
            );

            $side = $closure->side_from_ancestor; // 'L' or 'R'

            if ($pvAmount > 0) {
                // +PV: 미수부터 상계 → 남은 금액을 현재PV에 적립
                $this->applyPositivePv($balance, $side, $pvAmount, $ledger->id, $buyerId, $ancestor->id, $occurredAt);
            } else {
                // -PV: 현재PV에서 차감 → 부족분을 미수에 누적
                $this->applyNegativePv($balance, $side, abs($pvAmount), $ledger->id, $buyerId, $ancestor->id, $occurredAt);
            }

            $balance->save();

            // 매칭 체크 (미수가 없을 때만)
            if (!$balance->hasArrear()) {
                $this->checkAndTriggerPayout($ancestor, $balance);
            }
        }
    }

    /**
     * +PV 처리: 미수부터 상계 → 남은 금액 PV 적립
     */
    protected function applyPositivePv(
        PvBalance $balance,
        string $side,
        float $amount,
        int $ledgerId,
        int $buyerId,
        int $ancestorId,
        $occurredAt
    ): void {
        $arrearKey = $side === 'L' ? 'left_arrear_pv' : 'right_arrear_pv';
        $pvKey = $side === 'L' ? 'left_pv' : 'right_pv';

        $arrear = $balance->$arrearKey;

        if ($arrear > 0) {
            // 미수부터 상계
            $settleAmount = min($arrear, $amount);
            $balance->$arrearKey -= $settleAmount;
            $amount -= $settleAmount;

            // 로그: 미수 상계
            PvPropagationLog::create([
                'ledger_id' => $ledgerId,
                'buyer_id' => $buyerId,
                'ancestor_id' => $ancestorId,
                'side' => $side,
                'applied_amount' => $settleAmount,
                'applied_to' => 'ARREAR',
                'occurred_at' => $occurredAt,
            ]);
        }

        if ($amount > 0) {
            // 남은 금액 PV 적립
            $balance->$pvKey += $amount;

            // 로그: PV 적립
            PvPropagationLog::create([
                'ledger_id' => $ledgerId,
                'buyer_id' => $buyerId,
                'ancestor_id' => $ancestorId,
                'side' => $side,
                'applied_amount' => $amount,
                'applied_to' => 'PV',
                'occurred_at' => $occurredAt,
            ]);
        }
    }

    /**
     * -PV 처리: 현재PV에서 차감 → 부족분 미수 누적
     */
    protected function applyNegativePv(
        PvBalance $balance,
        string $side,
        float $amount,
        int $ledgerId,
        int $buyerId,
        int $ancestorId,
        $occurredAt
    ): void {
        $pvKey = $side === 'L' ? 'left_pv' : 'right_pv';
        $arrearKey = $side === 'L' ? 'left_arrear_pv' : 'right_arrear_pv';

        $currentPv = $balance->$pvKey;

        if ($currentPv >= $amount) {
            // 현재 PV에서 충분히 차감 가능
            $balance->$pvKey -= $amount;

            // 로그: PV 차감
            PvPropagationLog::create([
                'ledger_id' => $ledgerId,
                'buyer_id' => $buyerId,
                'ancestor_id' => $ancestorId,
                'side' => $side,
                'applied_amount' => $amount,
                'applied_to' => 'PV',
                'occurred_at' => $occurredAt,
            ]);
        } else {
            // PV 부족 → PV는 0으로, 부족분은 미수로
            if ($currentPv > 0) {
                $balance->$pvKey = 0;
                PvPropagationLog::create([
                    'ledger_id' => $ledgerId,
                    'buyer_id' => $buyerId,
                    'ancestor_id' => $ancestorId,
                    'side' => $side,
                    'applied_amount' => $currentPv,
                    'applied_to' => 'PV',
                    'occurred_at' => $occurredAt,
                ]);
                $amount -= $currentPv;
            }

            // 부족분 미수 누적
            $balance->$arrearKey += $amount;
            PvPropagationLog::create([
                'ledger_id' => $ledgerId,
                'buyer_id' => $buyerId,
                'ancestor_id' => $ancestorId,
                'side' => $side,
                'applied_amount' => $amount,
                'applied_to' => 'ARREAR',
                'occurred_at' => $occurredAt,
            ]);
        }
    }

    /**
     * 매칭 조건 체크 후 수당 생성 트리거
     */
    protected function checkAndTriggerPayout(Member $member, PvBalance $balance): void
    {
        $setting = PayoutSetting::orderBy('effective_from', 'desc')->first();
        if (!$setting) {
            return;
        }

        $minPv = min($balance->left_pv, $balance->right_pv);

        if ($minPv >= $setting->threshold_pv) {
            // PayoutService 호출
            $this->payoutService->createPayout($member, $balance, $setting);
        }
    }
}
