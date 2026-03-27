<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emi_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('down_payment', 12, 2)->default(0);
            $table->decimal('installment_amount', 12, 2);
            $table->enum('frequency', ['weekly', 'monthly', 'custom']);
            $table->unsignedSmallInteger('frequency_days');
            $table->unsignedSmallInteger('total_installments');
            $table->unsignedSmallInteger('paid_installments')->default(0);
            $table->decimal('remaining_amount', 12, 2);
            $table->decimal('late_fee_per_day', 8, 2)->default(0);
            $table->unsignedTinyInteger('grace_period_days')->default(3);
            $table->date('start_date');
            $table->date('next_due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active', 'completed', 'defaulted'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('emi_plans'); }
};
