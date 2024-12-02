<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('node_id');
            $table->unsignedBigInteger('machine_id');
            $table->dateTime('machine_datetime');
            $table->dateTime('device_datetime');
            $table->dateTime('current_datetime');
            $table->unsignedBigInteger('mode')->default(0)->comment('1->Start, 0->Stop');
            $table->unsignedBigInteger('speed')->nullable();
            $table->unsignedBigInteger('pick')->nullable();
            $table->integer('status')->default(1)->comment('1->Active, 2->Inactive');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            // $table->timestamps();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machine_logs');
    }
}
