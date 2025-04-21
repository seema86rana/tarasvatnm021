<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineStatusLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_status_id');
            $table->unsignedBigInteger('machine_log_id');
            $table->unsignedBigInteger('machine_id');
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

            // Add foreign key constraint
            $table->foreign('machine_status_id')->references('id')->on('machine_status')->onDelete('cascade'); // cascade, restrict, set null
            $table->foreign('machine_log_id')->references('id')->on('machine_master_logs')->onDelete('cascade'); // cascade, restrict, set null
            $table->foreign('machine_id')->references('id')->on('machine_master')->onDelete('cascade'); // cascade, restrict, set null
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machine_status_logs');
    }
}
