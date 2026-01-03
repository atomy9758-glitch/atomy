<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Services\ClosureService;
use Illuminate\Support\Facades\Hash;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $closureService = app(ClosureService::class);

        // ADMIN 계정 생성
        $admin = Member::firstOrCreate(
            ['username' => 'admin'],
            [
                'password_hash' => Hash::make('admin1234!'),
                'name' => '시스템 관리자',
                'phone' => '010-0000-0000',
                'email' => 'admin@100serolife.co.kr',
                'role' => 'ADMIN',
                'status' => 'ACTIVE',
                'self_pv' => 100, // 관리자도 자격 보유
                'qualified_from_at' => now(),
            ]
        );

        // Closure 생성
        if ($admin->wasRecentlyCreated) {
            $closureService->insertMember($admin);
            $this->command->info('✅ ADMIN 계정 생성: admin / admin1234!');
        } else {
            $this->command->info('ℹ️  ADMIN 계정 이미 존재');
        }
    }
}
