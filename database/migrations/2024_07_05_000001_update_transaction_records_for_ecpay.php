<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaction_records', function (Blueprint $table) {
            $table->dropColumn(['system_order_id', 'av_code']);
            $table->string('merchant_trade_no')->nullable()->after('payment_method');
            $table->string('trade_no')->nullable()->after('merchant_trade_no');
        });
    }

    public function down()
    {
        Schema::table('transaction_records', function (Blueprint $table) {
            $table->string('system_order_id')->nullable()->after('payment_method');
            $table->string('av_code')->nullable()->after('system_order_id');
            $table->dropColumn(['merchant_trade_no', 'trade_no']);
        });
    }
};
