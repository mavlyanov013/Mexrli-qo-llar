<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Legacy Mongo fields
            $table->string('legacy_mongo_id')->nullable()->index();
            $table->unsignedBigInteger('legacy_local_id')->nullable()->index();

            // Core payment fields
            $table->string('provider', 50)->index(); // paycom, click, paynet, apelsin, uzumbank
            $table->string('transaction_id')->index();
            $table->string('status', 50)->default('pending')->index();
            $table->string('category', 50)->nullable()->index();

            // User/donor reference from old system
            $table->string('payer_reference')->nullable();

            // Amounts
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency', 10)->default('UZS');
            $table->decimal('refunded_amount', 14, 2)->default(0);

            // Provider-specific ids
            $table->string('external_id')->nullable()->index(); // click_paydoc_id etc.
            $table->string('service_id')->nullable()->index();  // click_service_id etc.

            // Provider timestamps in milliseconds
            $table->bigInteger('provider_time_ms')->nullable();
            $table->bigInteger('provider_create_time')->nullable();
            $table->bigInteger('provider_perform_time')->nullable();
            $table->bigInteger('provider_cancel_time')->nullable();

            // Extra metadata
            $table->boolean('live_mode')->default(false);
            $table->json('payload')->nullable();         // parsed information
            $table->longText('raw_information')->nullable(); // original serialized info

            // Optional link to current donations table later
            $table->unsignedBigInteger('donation_id')->nullable()->index();

            $table->timestamps();

            $table->unique(['provider', 'transaction_id'], 'payments_provider_transaction_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
