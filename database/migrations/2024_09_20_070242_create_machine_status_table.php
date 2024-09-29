<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('node_id');
            $table->unsignedBigInteger('machine_id');
            $table->unsignedBigInteger('speed')->nullable();
            $table->decimal('avg_speed', 10, 2)->nullable();
            $table->unsignedBigInteger('total_pick')->nullable();
            $table->decimal('avg_total_pick', 10, 2)->nullable();
            $table->unsignedBigInteger('total_pick_shift_wise')->nullable();
            $table->decimal('efficiency', 10, 2)->nullable();
            $table->unsignedBigInteger('no_of_stoppage')->nullable();
            $table->decimal('last_stop', 10, 2)->nullable();
            $table->decimal('last_running', 10, 2)->nullable();
            $table->decimal('total_running', 10, 2)->nullable();
            $table->string('shift_name')->nullable();
            $table->dateTime('shift_start_datetime')->nullable();
            $table->dateTime('shift_end_datetime')->nullable();
            $table->date('machine_date')->nullable();
            $table->integer('status')->comment('1->Start, 0->Stop');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('machine_status');
    }
}
