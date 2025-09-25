<?php

use App\Models\Source;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->uuid('guid')->after('id')->nullable()->unique();
        });

        Source::withoutEvents(function () {
            Source::chunk(100, function ($sources) {
                foreach ($sources as $source) {
                    $source->guid = (string) Str::uuid();
                    $source->save();
                }
            });
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
