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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->date('spent_on');
            $table->string('description');
            $table->string('attachment_path')->nullable();
            $table->decimal('amount', 10, 2);
            $table->unsignedTinyInteger('vat_rate')->default(21);
            $table->boolean('billable')->default(true);
            $table->boolean('price_includes_vat')->default(false);
            $table->decimal('amount_excl_vat', 10, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->decimal('amount_incl_vat', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
