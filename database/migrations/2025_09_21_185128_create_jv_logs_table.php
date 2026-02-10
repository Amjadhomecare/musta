<?php

// database/migrations/2025_09_21_000000_create_jv_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jv_logs', function (Blueprint $table) {
            $table->id();

            // who/what changed
            $table->unsignedBigInteger('voucher_id')->index();
            $table->string('ref_code', 30)->nullable()->index();     // maps general_journal_vouchers.refCode
            $table->string('voucher_type', 25)->nullable()->index(); // maps voucher_type
            $table->enum('line_type', ['debit','credit'])->nullable()->index(); // maps type

            // account & maid (id + denormalized name for history)
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->string('account_name', 255)->nullable(); // from ledger table at change time

            $table->unsignedBigInteger('maid_id')->nullable()->index();
            $table->string('maid_name', 255)->nullable(); // from maids table at change time

            // notes as they existed when amount changed
            $table->string('notes', 255)->nullable();

            // the actual amount delta
            $table->decimal('amount_before', 10, 2)->nullable();
            $table->decimal('amount_after', 10, 2)->nullable();

            // meta
            $table->string('changed_by', 255)->nullable()->index();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('voucher_id')
                ->references('id')
                ->on('general_journal_vouchers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jv_logs');
    }
};
