<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('imei')->unique();
            $table->string('imei2')->nullable();
            $table->string('model')->default('Pending');
            $table->string('brand')->default('Pending');
            $table->string('android_version')->default('Pending');
            $table->string('api_key', 64)->unique()->nullable();
            $table->text('fcm_token')->nullable();
            $table->string('sim_serial')->nullable();
            $table->string('phone_number')->nullable();
            $table->enum('status', ['active', 'locked', 'kiosk', 'wiped'])->default('locked');
            $table->string('lock_reason')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedTinyInteger('battery_level')->nullable();
            $table->unsignedBigInteger('storage_used')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_rooted')->default(false);
            $table->boolean('is_tampered')->default(false);
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('devices'); }
};
