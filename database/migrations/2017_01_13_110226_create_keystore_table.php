<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeystoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keystore', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nodeid');
            $table->string('module', 32);
            $table->string('key', 32);
            $table->string('value');
            $table->index(['nodeid', 'module', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keystore');
    }
}
