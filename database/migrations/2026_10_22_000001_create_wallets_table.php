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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('type', 20); // cash, bank, e_wallet, savings, other
            $table->char('currency_code', 3);
            $table->decimal('opening_balance', 19, 4)->default(0);
            $table->string('icon', 60)->nullable();
            $table->string('color_token', 30)->nullable();
            $table->boolean('is_archived')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('created_at', 6)->nullable();
            $table->dateTime('updated_at', 6)->nullable();

            $table->index(['user_id', 'is_archived', 'sort_order']);
            $table->index(['user_id', 'currency_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
