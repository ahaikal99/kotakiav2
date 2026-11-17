<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->boolean('has_music')->default(false);
            $table->boolean('has_gallery')->default(false);
            $table->unsignedSmallInteger('max_gallery')->default(0);
            $table->boolean('has_video')->default(false);
            $table->boolean('has_rsvp')->default(true);
            $table->unsignedInteger('max_rsvp')->nullable()->comment('null = tanpa had');
            $table->boolean('has_wishes')->default(true);
            $table->boolean('has_countdown')->default(true);
            $table->boolean('has_gift')->default(false)->comment('Salam kaut / maklumat akaun bank');
            $table->boolean('premium_themes')->default(false);
            $table->unsignedSmallInteger('active_days')->default(90)->comment('Tempoh aktif selepas tarikh majlis');
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->json('palette')->comment('Warna & fon tema');
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
        Schema::dropIfExists('packages');
    }
};
