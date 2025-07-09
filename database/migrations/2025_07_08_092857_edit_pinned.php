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
        Schema::table('pinned_graphs', function (Blueprint $table) {
            $table->text('width')->nullable()->after('json');
        });

        Schema::table('pinned_tables', function (Blueprint $table) {
            $table->text('width')->nullable()->after('json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
