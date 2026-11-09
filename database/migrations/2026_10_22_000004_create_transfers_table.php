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
        Schema::create('transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('source_wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignUuid('destination_wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->decimal('amount', 19, 4);
            $table->char('currency_code', 3);
            $table->date('transfer_date');
            $table->time('transfer_time')->nullable();
            $table->string('note', 500)->nullable();
            $table->dateTime('created_at', 6)->nullable();
            $table->dateTime('updated_at', 6)->nullable();
            $table->dateTime('deleted_at', 6)->nullable();

            $table->index(['user_id', 'transfer_date', 'transfer_time']);
            $table->index(['source_wallet_id', 'transfer_date']);
            $table->index(['destination_wallet_id', 'transfer_date']);
            $table->index(['user_id', 'currency_code', 'transfer_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
