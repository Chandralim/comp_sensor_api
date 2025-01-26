<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensor_lists', function (Blueprint $table) {
            $table->id();
            $table->string("uname", 50);
            $table->string("name", 50);
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->double("value_top_limit")->nullable();
            $table->double("value_down_limit")->nullable();
            $table->string("unit_name", 20);
            $table->bigInteger('sensor_token_id');
            $table->string("type", 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensor_lists');
    }
}
