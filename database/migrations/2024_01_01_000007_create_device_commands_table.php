<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('issued_by_type')->nullable();
            $table->unsignedBigInteger('issued_by_id')->nullable();
            $table->enum('command', ['lock', 'unlock', 'kiosk', 'wipe', 'message', 'update_config']);
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'delivered', 'executed', 'failed'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('device_commands'); }
};
