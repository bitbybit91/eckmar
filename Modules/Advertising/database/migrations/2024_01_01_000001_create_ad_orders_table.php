<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ad_orders', function (Blueprint $table): void {
            $table->string('id', 32)->primary();
            $table->enum('type', ['banner', 'link']);
            $table->unsignedBigInteger('item_id');
            $table->decimal('usd_amount', 10, 2);
            $table->decimal('xmr_amount', 18, 6);
            $table->decimal('xmr_rate', 12, 2);
            $table->string('wallet');
            $table->enum('status', ['awaiting_payment', 'payment_noted', 'confirmed', 'failed'])
                  ->default('awaiting_payment');
            $table->timestamp('noted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_orders');
    }
}
