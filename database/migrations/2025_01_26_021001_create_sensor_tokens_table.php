<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensor_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('token');
            $table->bigInteger('created_at');
            $table->bigInteger('updated_at');
            $table->bigInteger('admin_id');
            $table->double('coor_long')->nullable();
            $table->double('coor_lat')->nullable();
            $table->string('name', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensor_tokens');
    }
}
