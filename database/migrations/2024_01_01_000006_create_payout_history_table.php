<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->integer('cycle_no'); // 해당 회원의 사이클 번호
            $table->datetime('paid_at'); // 지급 기록 시각
            $table->decimal('threshold_pv', 15, 2); // 적용된 기준 PV
            $table->decimal('payout_amount', 15, 2); // 지급액
            $table->decimal('left_pv_snapshot', 15, 2); // 지급 전 좌측 PV
            $table->decimal('right_pv_snapshot', 15, 2); // 지급 전 우측 PV
            $table->decimal('wasted_pv', 15, 2)->default(0); // 초과 소멸 PV
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->unsignedBigInteger('rule_id')->nullable(); // 적용된 설정
            $table->text('note')->nullable();
            $table->text('search_text')->nullable(); // 검색용 통합 텍스트
            $table->timestamp('created_at')->useCurrent();
            
            // 외래키
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('rule_id')->references('id')->on('payout_settings')->onDelete('set null');
            
            // 중복 지급 방지
            $table->unique(['member_id', 'cycle_no'], 'unique_member_cycle');
            
            // 인덱스
            $table->index('paid_at');
            $table->index(['member_id', 'paid_at']);
            
            // FULLTEXT 인덱스
            $table->fullText('search_text', 'payout_history_search_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_history');
    }
};
