<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \Illuminate\Support\Facades\DB;

class AddForeignKeyConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** Using raw queries to prevent need for doctrine/dbal */

        DB::statement('ALTER TABLE `device_sensors` MODIFY `device_id` INTEGER UNSIGNED NULL;');
        DB::statement('ALTER TABLE `device_sensors` MODIFY `type_id` INTEGER UNSIGNED NULL;');
        Schema::table('device_sensors', function (Blueprint $table) {
            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('type_id')->references('id')->on('sensor_types');
        });

        DB::statement('ALTER TABLE `device_sensor_values` MODIFY `device_sensor_id` INTEGER UNSIGNED NULL;');
        Schema::table('device_sensor_values', function (Blueprint $table) {
            $table->foreign('device_sensor_id')->references('id')->on('device_sensors');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_sensor_values', function (Blueprint $table) {
            $table->dropForeign(['device_sensor_id']);
        });

        Schema::table('device_sensors', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropForeign(['type_id']);
        });
    }
}
