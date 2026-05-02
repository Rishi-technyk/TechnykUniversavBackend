<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'gateway_name')) {
                $table->string('gateway_name')->nullable()->after('type');
            }

            if (!Schema::hasColumn('transactions', 'gateway_slug')) {
                $table->string('gateway_slug')->nullable()->after('gateway_name');
            }

            if (!Schema::hasColumn('transactions', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('gateway_slug');
            }

            if (!Schema::hasColumn('transactions', 'gateway_order_id')) {
                $table->string('gateway_order_id')->nullable()->after('gateway_transaction_id');
            }

            if (!Schema::hasColumn('transactions', 'raw_response')) {
                $table->longText('raw_response')->nullable()->after('bank_response');
            }

            if (!Schema::hasColumn('transactions', 'webhook_response')) {
                $table->longText('webhook_response')->nullable()->after('raw_response');
            }

            if (!Schema::hasColumn('transactions', 'payment_status_code')) {
                $table->string('payment_status_code')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('transactions', 'ImportFlag')) {
                $table->unsignedTinyInteger('ImportFlag')->default(1)->after('Importflag');
            }

            if (!Schema::hasColumn('transactions', 'module_reference_id')) {
                $table->string('module_reference_id')->nullable()->after('payment_type');
            }

            if (!Schema::hasColumn('transactions', 'retry_count')) {
                $table->unsignedInteger('retry_count')->default(0)->after('module_reference_id');
            }

            if (!Schema::hasColumn('transactions', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('retry_count');
            }

            if (!Schema::hasColumn('transactions', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('processed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach ([
                'gateway_name',
                'gateway_slug',
                'gateway_transaction_id',
                'gateway_order_id',
                'raw_response',
                'webhook_response',
                'payment_status_code',
                'ImportFlag',
                'module_reference_id',
                'retry_count',
                'processed_at',
                'idempotency_key',
            ] as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
