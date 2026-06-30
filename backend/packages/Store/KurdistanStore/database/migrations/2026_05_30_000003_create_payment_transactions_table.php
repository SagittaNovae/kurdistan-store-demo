<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->string('gateway', 32)->index();
            $table->string('reference', 128)->nullable()->index();
            $table->decimal('amount', 12, 4);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending')->index();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
