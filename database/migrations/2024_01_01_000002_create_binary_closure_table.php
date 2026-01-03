<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('binary_closure', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->integer('depth'); // 0=자기자신, 1=직계자식, 2=손자...
            $table->enum('side_from_ancestor', ['L', 'R'])->nullable(); // depth=0이면 NULL
            
            // 복합 기본키
            $table->primary(['ancestor_id', 'descendant_id']);
            
            // 외래키
            $table->foreign('ancestor_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('descendant_id')->references('id')->on('members')->onDelete('cascade');
            
            // 인덱스
            $table->index('descendant_id');
            $table->index(['ancestor_id', 'depth']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('binary_closure');
    }
};
