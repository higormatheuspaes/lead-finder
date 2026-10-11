<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('search_id')->nullable()->constrained()->nullOnDelete();
            $table->string('google_place_id')->unique();
            $table->string('name');
            $table->string('greeting_name')->nullable();     // nome limpo para o template
            $table->string('niche')->index();                 // "academia", "dentista"...
            $table->string('phone_raw')->nullable();          // "+55 47 99122-6162"
            $table->string('phone_e164')->nullable()->index(); // "5547991226162"
            $table->boolean('is_mobile')->default(false);
            $table->string('website')->nullable();
            $table->string('website_type')->default('none');
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('ratings_count')->default(0);
            $table->string('address')->nullable();
            $table->string('primary_type')->nullable();
            $table->string('business_status')->nullable();
            $table->string('maps_url')->nullable();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('problem')->nullable();             // vira a variável {{2}} do template
            $table->string('status')->default('novo')->index();
            $table->string('discard_reason')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
