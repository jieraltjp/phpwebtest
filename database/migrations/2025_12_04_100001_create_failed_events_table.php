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
        Schema::create('failed_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_name');
            $table->json('event_data');
            $table->json('event_metadata')->nullable();
            $table->text('error_message');
            $table->longText('error_trace')->nullable();
            $table->integer('attempts')->default(1);
            $table->timestamp('failed_at');
            $table->timestamps();

            $table->index('event_name');
            $table->index('failed_at');
            $table->index('attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_events');
    }
};