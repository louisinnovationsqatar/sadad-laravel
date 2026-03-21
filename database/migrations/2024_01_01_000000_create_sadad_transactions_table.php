<?php
// Built by Louis Innovations (www.louis-innovations.com)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional SADAD transaction log table.
 *
 * Publish and run this migration if you want to persist every webhook and
 * callback event for auditing or reconciliation purposes.
 *
 * Publish:  php artisan vendor:publish --tag=sadad-migrations
 * Migrate:  php artisan migrate
 *
 * Enable automatic logging in config/sadad.php:
 *   'log_transactions' => true,
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sadad_transactions', function (Blueprint $table) {
            $table->id();

            // SADAD-assigned transaction reference
            $table->string('transaction_number')->nullable()->index();

            // Merchant-side order reference
            $table->string('order_number')->nullable()->index();

            // Monetary fields
            $table->decimal('amount', 12, 3)->nullable();
            $table->string('currency', 10)->default('QAR');

            // Payment outcome
            $table->boolean('is_success')->default(false);
            $table->string('response_code', 20)->nullable();
            $table->string('response_message')->nullable();
            $table->string('transaction_status', 20)->nullable();

            // Gateway metadata
            $table->string('merchant_id', 20)->nullable();
            $table->string('invoice_number')->nullable();
            $table->boolean('is_test_mode')->default(true);

            // Source of this record: "webhook" or "callback"
            $table->string('source', 20)->default('webhook');

            // Full raw payload for audit/debugging
            $table->json('raw_payload')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sadad_transactions');
    }
};
