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
        Schema::table('logistic_records', function (Blueprint $table) {
            $table->string('county')->nullable()->after('delivery_zip_code');
            $table->string('district')->nullable()->after('county');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logistic_records', function (Blueprint $table) {
            $table->dropColumn(['county', 'district']);
        });
    }
};
