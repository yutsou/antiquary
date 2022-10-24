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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->unsignedBigInteger('lot_id');
            $table->foreign('lot_id')->references('id')->on('lots')->onDelete('cascade');;
            $table->index('lot_id');
            $table->tinyInteger('payment_method')->nullable();
            $table->tinyInteger('delivery_method')->nullable();
            $table->tinyInteger('status');
            $table->timestamp('payment_due_at')->nullable();
            $table->decimal('subtotal', 11)->nullable();
            $table->decimal('delivery_cost', 11)->nullable();
            $table->decimal('total', 11)->nullable();
            $table->text('remark')->nullable();
            $table->decimal('owner_real_take')->nullable();
            $table->decimal('commission')->nullable();
            $table->decimal('premium')->nullable();
            $table->decimal('earning')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
