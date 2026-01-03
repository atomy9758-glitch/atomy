<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('action', 50); // 'CREATE_MEMBER', 'ADJUST_PV', 'UPDATE_SETTING' 등
            $table->string('target_type', 50)->nullable(); // 'Member', 'PvLedger', 'PayoutSetting' 등
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('detail')->nullable(); // 상세 내용
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // 외래키
            $table->foreign('admin_id')->references('id')->on('members')->onDelete('cascade');
            
            // 인덱스
            $table->index(['admin_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit');
    }
};
