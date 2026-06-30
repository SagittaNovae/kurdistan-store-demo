<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_phone_verified')->default(false)->after('is_verified');
        });

        // All existing accounts are treated as verified upon migration.
        DB::table('customers')->update(['is_phone_verified' => true]);
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_phone_verified');
        });
    }
};
