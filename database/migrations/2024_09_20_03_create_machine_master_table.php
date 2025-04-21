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
            $table->unsignedBigInteger('node_id');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->tinyInteger('priority')->default(0);
            $table->integer('status')->default(1)->comment('1->Active, 0->Inactive');
            $table->tinyInteger('current_status')->default(0)->comment('1->active, 0->Inactive');
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('node_id')->references('id')->on('node_master')->onDelete('cascade'); // cascade, restrict, set null
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
