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
        Schema::create('logistic_records', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type');
            $table->string('addressee_name')->nullable();
            $table->string('addressee_phone')->nullable();
            $table->string('addressee_address')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('face_to_face_address')->nullable();
            $table->string('delivery_zip_code')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('cross_board_delivery_country')->nullable();
            $table->string('cross_board_delivery_country_code')->nullable();
            $table->string('cross_board_delivery_address')->nullable();
            $table->string('remark')->nullable();
            $table->integer('logistic_recordable_id');
            $table->string('logistic_recordable_type');
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
        Schema::dropIfExists('delivery_records');
    }
};
