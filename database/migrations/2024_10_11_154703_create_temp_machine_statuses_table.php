<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempMachineStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_machine_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_status_id');
            $table->unsignedBigInteger('machine_log_id');
            $table->unsignedBigInteger('machine_id');
            $table->tinyInteger('active_machine')->default(1)->comment('1->active, 0->Inactive');
            $table->unsignedBigInteger('speed')->nullable();
            $table->unsignedBigInteger('status')->default(0)->comment('1->Start, 0->Stop');
            $table->unsignedBigInteger('no_of_stoppage')->nullable();
            $table->decimal('last_stop', 10, 2)->nullable();
            $table->decimal('last_running', 10, 2)->nullable();
            $table->decimal('total_running', 10, 2)->nullable();
            $table->decimal('total_time', 10, 2)->nullable();
            $table->decimal('efficiency', 10, 2)->nullable();
            $table->dateTime('machine_datetime')->nullable();
            $table->dateTime('device_datetime')->nullable();
            $table->date('shift_date')->nullable();
            $table->string('shift_name')->nullable();
            $table->dateTime('shift_start_datetime')->nullable();
            $table->dateTime('shift_end_datetime')->nullable();
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
        Schema::dropIfExists('temp_machine_status');
    }
}
