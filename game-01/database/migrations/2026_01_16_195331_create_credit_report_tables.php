<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('document', 20);
            $table->string('email');
            $table->string('phone', 20);
            $table->timestamps();
        });

        Schema::create('subscription_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->string('period', 7);
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('report_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_report_id')->constrained('subscription_reports')->onDelete('cascade');
            $table->string('bank', 100);
            $table->string('status', 3);
            $table->string('currency', 3)->default('PEN');
            $table->decimal('amount', 12, 2);
            $table->integer('expiration_days')->default(0);
            $table->timestamps();
        });

        Schema::create('report_other_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_report_id')->constrained('subscription_reports')->onDelete('cascade');
            $table->string('entity', 100);
            $table->string('currency', 3)->default('PEN');
            $table->decimal('amount', 12, 2);
            $table->integer('expiration_days')->default(0);
            $table->timestamps();
        });

        Schema::create('report_credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_report_id')->constrained('subscription_reports')->onDelete('cascade');
            $table->string('bank', 100);
            $table->string('currency', 3)->default('PEN');
            $table->decimal('line', 12, 2);
            $table->decimal('used', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_credit_cards');
        Schema::dropIfExists('report_other_debts');
        Schema::dropIfExists('report_loans');
        Schema::dropIfExists('subscription_reports');
        Schema::dropIfExists('subscriptions');
    }
};