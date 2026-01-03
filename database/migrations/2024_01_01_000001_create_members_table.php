<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255);
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            
            // 추천인 (스폰서 트리)
            $table->unsignedBigInteger('sponsor_id')->nullable();
            
            // 바이너리 배치
            $table->unsignedBigInteger('binary_parent_id')->nullable();
            $table->enum('binary_position', ['L', 'R'])->nullable();
            
            // 본인 누적 PV
            $table->decimal('self_pv', 15, 2)->default(0);
            
            // 자격 획득 시각
            $table->datetime('qualified_from_at')->nullable();
            
            // 역할 및 상태
            $table->enum('role', ['ADMIN', 'MEMBER'])->default('MEMBER');
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'SUSPENDED'])->default('ACTIVE');
            
            $table->timestamps();
            
            // 외래키
            $table->foreign('sponsor_id')->references('id')->on('members')->onDelete('set null');
            $table->foreign('binary_parent_id')->references('id')->on('members')->onDelete('set null');
            
            // 인덱스
            $table->index('sponsor_id');
            $table->index('binary_parent_id');
            
            // 동일 부모 아래 동일 위치 중복 방지
            $table->unique(['binary_parent_id', 'binary_position'], 'unique_binary_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
