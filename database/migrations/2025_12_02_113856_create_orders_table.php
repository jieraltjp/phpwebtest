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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->foreignId('user_id')->constrained();
            $table->string('status');
            $table->text('status_message')->nullable();
            $table->decimal('total_fee_cny', 10, 2);
            $table->decimal('total_fee_jpy', 10, 2);
            $table->string('shipping_address');
            $table->string('domestic_tracking_number')->nullable();
            $table->string('international_tracking_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
