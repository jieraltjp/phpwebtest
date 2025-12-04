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
        Schema::create('inventory_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('old_quantity');
            $table->integer('new_quantity');
            $table->string('change_type'); // order_created, order_cancelled, manual_update, etc.
            $table->text('reason')->nullable();
            $table->string('changed_by'); // user_id or 'system'
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index('change_type');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_changes');
    }
};