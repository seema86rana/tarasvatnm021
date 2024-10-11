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
            $table->integer('status')->comment('1->Active, 0->Inactive')->nullable();
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
        Schema::dropIfExists('pick_calculations');
    }
}
