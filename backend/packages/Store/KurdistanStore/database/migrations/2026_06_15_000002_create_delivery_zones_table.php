<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('governorate', 64)->index();
            $table->string('district', 64)->nullable();
            $table->decimal('flat_rate', 8, 2)->default(5000.00);
            $table->unsignedSmallInteger('estimated_days')->default(2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['governorate', 'district']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
