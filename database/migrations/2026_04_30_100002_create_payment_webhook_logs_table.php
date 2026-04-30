<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_name')->nullable();
            $table->string('gateway_slug')->index();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('gateway_event_id')->nullable()->index();
            $table->string('gateway_event_type')->nullable();
            $table->string('status')->default('received');
            $table->boolean('signature_valid')->default(false);
            $table->longText('payload')->nullable();
            $table->longText('headers')->nullable();
            $table->longText('response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_logs');
    }
};
