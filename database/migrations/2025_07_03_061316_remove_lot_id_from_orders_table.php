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
        Schema::table('orders', function (Blueprint $table) {
            // 先移除外鍵約束
            $table->dropForeign(['lot_id']);
            // 移除索引
            $table->dropIndex(['lot_id']);
            // 移除欄位
            $table->dropColumn('lot_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('lot_id')->after('user_id');
            $table->foreign('lot_id')->references('id')->on('lots')->onDelete('cascade');
            $table->index('lot_id');
        });
    }
};
