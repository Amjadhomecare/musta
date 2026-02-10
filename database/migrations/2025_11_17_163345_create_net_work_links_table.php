<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('net_work_links', function (Blueprint $table) {
            $table->id();

            // Customer relationship
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Maid relationship
            $table->foreignId('maid_id')
                ->nullable()
                ->constrained('maids_d_b_s')
                ->nullOnDelete();

            // N-Genius references
            $table->string('gateway_reference', 100)->unique();
            $table->string('order_reference', 100)->nullable();
            $table->string('outlet_ref', 100)->nullable();

            $table->date('expiry_date')->nullable();
            $table->string('transaction_type', 50)->nullable();

            $table->decimal('amount_value', 12, 2)->nullable();

            $table->text('self_url')->nullable();
            $table->text('payment_url')->nullable();
            $table->text('email_data_url')->nullable();
            $table->text('resend_url')->nullable();

            $table->boolean('skip_email_notification')->default(false);

            $table->tinyInteger('status')->default(0);

            $table->timestamp('paid_at')->nullable();

            $table->text('note')->nullable();

            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();


            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('net_work_links');
    }
};
