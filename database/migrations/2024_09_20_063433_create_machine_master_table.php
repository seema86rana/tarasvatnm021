<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('node_id');
            $table->string('machine_name');
            $table->string('machine_display_name');
            $table->dateTime('device_datetime');
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
        Schema::dropIfExists('machine_master');
    }
}
