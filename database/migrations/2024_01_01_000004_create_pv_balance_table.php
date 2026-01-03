<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pv_balance', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->primary();
            $table->decimal('left_pv', 15, 2)->default(0); // >=0
            $table->decimal('right_pv', 15, 2)->default(0); // >=0
            $table->decimal('left_arrear_pv', 15, 2)->default(0); // 좌측 미수 >=0
            $table->decimal('right_arrear_pv', 15, 2)->default(0); // 우측 미수 >=0
            $table->integer('cycle_no')->default(0); // 지급 사이클 번호
            $table->datetime('cycle_started_at')->nullable(); // 현재 사이클 시작 시각
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            // 외래키
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            
            // 인덱스
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_balance');
    }
};
