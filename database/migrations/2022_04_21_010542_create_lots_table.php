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
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('estimated_price', 11)->nullable();
            $table->decimal('starting_price', 11)->nullable();
            $table->decimal('reserve_price', 11)->nullable();
            $table->decimal('current_bid', 11)->default(0);
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->index('owner_id');
            $table->unsignedBigInteger('winner_id')->nullable();
            $table->foreign('winner_id')->references('id')->on('users');
            $table->index('winner_id');
            $table->unsignedBigInteger('auction_id')->nullable();
            $table->foreign('auction_id')->references('id')->on('auctions')->onDelete('cascade');
            $table->index('auction_id');
            $table->timestamp('auction_start_at')->nullable();
            $table->index('auction_start_at');
            $table->timestamp('auction_end_at')->nullable();
            $table->index('auction_end_at');
            $table->tinyInteger('rating')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('entrust');
            $table->text('suggestion')->nullable();
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
        Schema::dropIfExists('lots');
    }
};
