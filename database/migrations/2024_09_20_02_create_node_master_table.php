<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->string('name');
            $table->tinyInteger('status')->default(1)->comment('1->Active, 0->Inactive');
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade'); // cascade, restrict, set null
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node_master');
    }
}
