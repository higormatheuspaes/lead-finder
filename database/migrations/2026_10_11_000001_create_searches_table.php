<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('searches', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->string('city');
            $table->string('status')->default('pending')->index(); // pending | running | done | failed
            $table->unsignedTinyInteger('pages_fetched')->default(0);
            $table->unsignedInteger('results_count')->default(0);
            $table->unsignedInteger('new_leads_count')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('searches');
    }
};
