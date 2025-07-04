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
        Schema::table('users', function (Blueprint $table) {
            $table->string('google2fa_secret')->nullable()->after('remember_token');
            $table->boolean('google2fa_enabled')->default(false)->after('google2fa_secret');
            $table->timestamp('two_factor_verified_at')->nullable()->after('google2fa_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google2fa_secret', 'google2fa_enabled', 'two_factor_verified_at']);
        });
    }
};
