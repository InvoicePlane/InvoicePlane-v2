<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('relations')->cascadeOnDelete();
            $table->string('number', 50)->nullable();
            $table->string('name')->nullable();
            $table->string('status', 30)->default('active');
            $table->string('billing_interval', 30)->default('monthly');
            $table->string('interval_unit', 20)->default('month');
            $table->integer('interval_count')->default(1);

            $table->decimal('price', 15, 4)->default(0.0000);
            $table->string('currency_code', 3)->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->integer('grace_period_days')->default(0);
            $table->timestamp('grace_period_ends_at')->nullable();

            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();

            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resume_at')->nullable();

            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('canceled_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['customer_id']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name');
            $table->decimal('quantity', 15, 4)->default(1.0000);
            $table->decimal('unit_price', 15, 4)->default(0.0000);
            $table->decimal('subtotal', 15, 4)->default(0.0000);
            $table->decimal('tax', 15, 4)->default(0.0000);
            $table->decimal('total', 15, 4)->default(0.0000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
    }
};
