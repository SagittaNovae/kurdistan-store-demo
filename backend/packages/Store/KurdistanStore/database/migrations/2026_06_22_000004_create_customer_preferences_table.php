<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->unique();
            $table->boolean('notify_order_updates')->default(true);
            $table->boolean('notify_promotions')->default(false);
            $table->string('preferred_language', 5)->default('en');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_preferences');
    }
};
