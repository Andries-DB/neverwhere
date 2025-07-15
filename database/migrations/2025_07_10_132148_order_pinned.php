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
            $table->integer('display_order')->default(0)->after('width');
            $table->index('display_order');
        });

        Schema::table('pinned_tables', function (Blueprint $table) {
            $table->integer('display_order')->default(0)->after('width');
            $table->index('display_order');
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
