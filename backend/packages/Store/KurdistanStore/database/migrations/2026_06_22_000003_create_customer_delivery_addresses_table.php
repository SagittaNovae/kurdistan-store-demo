<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_delivery_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->string('label', 20)->default('Home'); // Home | Work | Other
            $table->string('nickname', 100)->nullable();
            $table->text('address_text')->nullable(); // reverse-geocoded human-readable
            $table->string('governorate', 100);
            $table->string('city', 100);
            $table->string('address_line', 500);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();

            $table->index(['customer_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_delivery_addresses');
    }
};
