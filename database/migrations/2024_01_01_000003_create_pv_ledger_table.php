<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pv_ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->decimal('pv_amount', 15, 2); // +적립 또는 -취소/조정
            $table->datetime('occurred_at');
            $table->enum('type', ['ORDER', 'CANCEL', 'ADJUST']);
            $table->string('ref_no', 100)->nullable(); // 주문번호 등
            $table->text('memo')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // 외래키
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            
            // 인덱스
            $table->index(['member_id', 'occurred_at']);
            $table->index('ref_no');
            $table->index(['type', 'occurred_at']);
            
            // FULLTEXT 인덱스 (검색용 - MySQL 5.7+)
            $table->fullText('memo', 'pv_ledger_memo_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_ledger');
    }
};
