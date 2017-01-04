<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNsbulletinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nsbulletins', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nodeid');
            $table->string('title', 256);
            $table->text('content');
            $table->timestamp('created')->useCurrent();
            $table->index(['id','nodeid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nsbulletins');
    }
}
