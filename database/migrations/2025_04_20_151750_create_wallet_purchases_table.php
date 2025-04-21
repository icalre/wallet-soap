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
        Schema::create('wallet_purchases', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->string('code', 6);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_paid')->default(false);
            $table->foreignId('wallet_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_purchases');
    }
};
