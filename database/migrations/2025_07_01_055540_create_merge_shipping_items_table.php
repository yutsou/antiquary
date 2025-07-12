<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merge_shipping_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merge_shipping_request_id');
            $table->foreign('merge_shipping_request_id')->references('id')->on('merge_shipping_requests')->onDelete('cascade');
            $table->index('merge_shipping_request_id');
            $table->unsignedBigInteger('lot_id');
            $table->foreign('lot_id')->references('id')->on('lots')->onDelete('cascade');
            $table->index('lot_id');
            $table->integer('quantity')->default(1);
            $table->decimal('original_shipping_fee', 11, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merge_shipping_items');
    }
};
