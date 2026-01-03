<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayoutSetting;

class InitialPayoutSettingSeeder extends Seeder
{
    public function run(): void
    {
        $exists = PayoutSetting::count() > 0;

        if (!$exists) {
            PayoutSetting::create([
                'threshold_pv' => 300000,
                'payout_amount' => 30000,
                'effective_from' => now(),
                'created_by' => null,
            ]);

            $this->command->info('✅ 초기 수당 설정 생성: threshold_pv=300000, payout_amount=30000');
        } else {
            $this->command->info('ℹ️  수당 설정 이미 존재');
        }
    }
}
