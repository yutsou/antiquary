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
        Schema::create('transaction_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_record_id');
            $table->foreign('order_record_id')->references('id')->on('order_records')->onDelete('cascade');
            $table->index('order_record_id');
            $table->tinyInteger('status');
            $table->tinyInteger('payment_method');
            $table->string('system_order_id')->nullable();
            $table->string('av_code')->nullable();
            $table->unsignedBigInteger('remitter_id')->nullable();
            $table->foreign('remitter_id')->references('id')->on('users');
            $table->index('remitter_id');
            $table->string('remitter_account')->nullable();
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->foreign('payee_id')->references('id')->on('users');
            $table->index('payee_id');
            $table->string('payee_account')->nullable();
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
        Schema::dropIfExists('transaction_records');
    }
};
