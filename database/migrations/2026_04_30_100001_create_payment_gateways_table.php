<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('environment', ['test', 'live'])->default('test');
            $table->boolean('is_active')->default(false);
            $table->text('merchant_id')->nullable();
            $table->text('key_id')->nullable();
            $table->text('key_secret')->nullable();
            $table->text('salt')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->text('app_id')->nullable();
            $table->text('encryption_key')->nullable();
            $table->string('status')->default('inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
