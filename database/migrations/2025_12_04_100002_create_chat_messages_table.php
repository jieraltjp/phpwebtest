<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('from_user_id');
            $table->unsignedBigInteger('to_user_id');
            $table->text('message');
            $table->enum('chat_type', ['direct', 'customer_service', 'group'])->default('direct');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // 索引
            $table->index(['from_user_id', 'created_at']);
            $table->index(['to_user_id', 'created_at']);
            $table->index(['to_user_id', 'read_at']);
            $table->index('chat_type');
            $table->index('created_at');

            // 外键约束
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};