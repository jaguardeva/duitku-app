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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('type', 20); // income, expense
            $table->decimal('amount', 19, 4);
            $table->char('currency_code', 3);
            $table->string('title', 160);
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->time('transaction_time')->nullable();
            $table->dateTime('created_at', 6)->nullable();
            $table->dateTime('updated_at', 6)->nullable();
            $table->dateTime('deleted_at', 6)->nullable();

            $table->index(['user_id', 'transaction_date', 'transaction_time']);
            $table->index(['user_id', 'type', 'transaction_date']);
            $table->index(['wallet_id', 'transaction_date']);
            $table->index(['category_id', 'transaction_date']);
            $table->index(['user_id', 'currency_code', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
