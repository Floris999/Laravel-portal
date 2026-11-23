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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('worked_on');
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->decimal('hours', 8, 2);
            // Snapshot of the product rate at registration time, so later rate changes don't alter history.
            $table->decimal('hourly_rate', 8, 2);
            $table->unsignedTinyInteger('vat_rate')->default(21);
            $table->boolean('billable')->default(true);
            $table->boolean('price_includes_vat')->default(false);
            $table->decimal('amount_excl_vat', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('amount_incl_vat', 10, 2);
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
