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
        Schema::create('merge_shipping_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->decimal('original_shipping_fee', 11, 2);
            $table->decimal('new_shipping_fee', 11, 2)->nullable();
            $table->tinyInteger('status')->default(0); // 0: 待處理, 1: 已處理, 2: 已拒絕
            $table->tinyInteger('delivery_method'); // 1: 宅配, 2: 境外
            $table->text('remark')->nullable(); // 拍賣師備註
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
        Schema::dropIfExists('merge_shipping_requests');
    }
};
