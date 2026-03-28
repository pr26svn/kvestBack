<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->string('status')->default('active')->after('password');
            $table->json('profile_data')->nullable()->after('status');
            $table->timestamp('last_active_at')->nullable()->after('profile_data');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'display_name',
                'status',
                'profile_data',
                'last_active_at',
            ]);
        });
    }
};
