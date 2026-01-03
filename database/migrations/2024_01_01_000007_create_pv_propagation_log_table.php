<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pv_propagation_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ledger_id'); // 원장 기록
            $table->unsignedBigInteger('buyer_id'); // 구매자
            $table->unsignedBigInteger('ancestor_id'); // 전파 받은 상위
            $table->enum('side', ['L', 'R']); // 전파된 라인
            $table->decimal('applied_amount', 15, 2); // 전파된 금액 (절댓값)
            $table->enum('applied_to', ['PV', 'ARREAR']); // PV 또는 미수
            $table->datetime('occurred_at'); // 전파 시각
            $table->timestamp('created_at')->useCurrent();
            
            // 외래키
            $table->foreign('ledger_id')->references('id')->on('pv_ledger')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('ancestor_id')->references('id')->on('members')->onDelete('cascade');
            
            // 인덱스
            $table->index('ledger_id');
            $table->index(['buyer_id', 'occurred_at']);
            $table->index(['ancestor_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_propagation_log');
    }
};
