<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineMasterLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_master_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id');
            $table->unsignedBigInteger('speed')->nullable();
            $table->unsignedBigInteger('mode')->default(0)->comment('1->Start, 0->Stop');
            $table->unsignedBigInteger('pick')->nullable();
            $table->dateTime('machine_datetime');
            $table->timestamps();

            // Add foreign key constraint
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
        Schema::dropIfExists('machine_master_logs');
    }
}
