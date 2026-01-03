<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('threshold_pv', 15, 2); // 매칭 기준 PV
            $table->decimal('payout_amount', 15, 2); // 수당 지급액
            $table->datetime('effective_from'); // 적용 시작 시각
            $table->unsignedBigInteger('created_by')->nullable(); // 설정 생성자
            $table->timestamp('created_at')->useCurrent();
            
            // 외래키
            $table->foreign('created_by')->references('id')->on('members')->onDelete('set null');
            
            // 인덱스 (최신 설정 조회용)
            $table->index('effective_from');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_settings');
    }
};
