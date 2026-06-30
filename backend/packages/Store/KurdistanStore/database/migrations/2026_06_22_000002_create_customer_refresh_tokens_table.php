<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->string('token_hash', 64)->unique(); // SHA-256 hex of the plain token
            $table->timestamp('expires_at');
            $table->boolean('remember')->default(false);
            $table->string('user_agent', 500)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();

            $table->index(['customer_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_refresh_tokens');
    }
};
