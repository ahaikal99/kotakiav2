<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->foreignId('theme_id')->constrained()->restrictOnDelete();
            $table->string('slug')->unique();
            $table->string('status', 20)->default('draft')->index(); // draft, active, expired, suspended

            // Pengantin
            $table->string('event_title')->default('Walimatulurus');
            $table->string('groom_nickname');
            $table->string('groom_fullname')->nullable();
            $table->string('bride_nickname');
            $table->string('bride_fullname')->nullable();
            $table->string('hosts')->nullable()->comment('Nama ibu bapa / tuan rumah');
            $table->string('cover_image')->nullable();

            // Majlis
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('venue_name');
            $table->text('venue_address')->nullable();
            $table->string('maps_url', 500)->nullable();
            $table->string('waze_url', 500)->nullable();

            // Kandungan
            $table->text('greeting')->nullable();
            $table->text('message')->nullable();
            $table->text('doa')->nullable();
            $table->json('schedule')->nullable()->comment('Aturcara majlis');
            $table->json('contacts')->nullable();
            $table->json('gift')->nullable()->comment('Maklumat salam kaut');

            // Media
            $table->string('music_path')->nullable();
            $table->string('music_title')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('video_path')->nullable();

            // RSVP
            $table->boolean('rsvp_enabled')->default(true);
            $table->date('rsvp_deadline')->nullable();
            $table->unsignedSmallInteger('max_pax_per_rsvp')->default(5);

            $table->unsignedInteger('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('attendance', 10)->index(); // hadir, tidak
            $table->unsignedSmallInteger('pax')->default(1);
            $table->text('wish')->nullable();
            $table->boolean('wish_visible')->default(true);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('pending')->index(); // pending, paid, failed
            $table->string('gateway', 30);
            $table->string('gateway_bill_code')->nullable()->index();
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('rsvps');
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('invitations');
    }
};
