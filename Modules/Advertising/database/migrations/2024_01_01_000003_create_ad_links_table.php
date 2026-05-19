<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('ad_links', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->enum('status', ['pending', 'active', 'rejected', 'expired'])->default('pending');
            $table->string('advertiser_email');
            $table->string('destination_url');
            $table->string('anchor_text', 60);
            $table->string('order_id', 32);
            $table->unsignedTinyInteger('month_count');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('ad_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_links');
    }
}
