<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNsbulletinTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nsbulletin_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nodeid');
            $table->string('tag', 16);
            $table->index(['nodeid','tag']);
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nsbulletin_tags');
    }
}
