<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThinServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thin_services', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nodeid');
            $table->string('module', 32);
            $table->string('type',32);
            $table->text('config')->nullable();
            $table->index(['nodeid','service']);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thin_services');
    }
}
