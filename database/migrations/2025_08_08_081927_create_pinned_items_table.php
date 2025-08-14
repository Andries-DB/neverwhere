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
        Schema::create('pinned_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('title')->nullable();
            $table->string('sort_chart')->nullable();
            $table->string('_x')->nullable();
            $table->string('_y')->nullable();
            $table->string('_agg')->nullable();
            $table->json('json')->nullable();
            $table->text('width')->nullable();
            $table->integer('display_order')->default(0);
            $table->index('display_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinned_items');
    }
};
