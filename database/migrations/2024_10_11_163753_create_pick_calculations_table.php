<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickCalculationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pick_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_status_id')->nullable();
            $table->unsignedBigInteger('intime_pick')->nullable();
            $table->unsignedBigInteger('shift_pick')->nullable();
            $table->unsignedBigInteger('total_pick')->nullable();
            $table->unsignedBigInteger('new_pick')->nullable();
            $table->unsignedBigInteger('difference_pick')->nullable();
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
        Schema::dropIfExists('pick_calculations');
    }
}
