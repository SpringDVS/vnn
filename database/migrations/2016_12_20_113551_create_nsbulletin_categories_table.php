<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNsbulletinCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nsbulletin_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nodeid');
            $table->string('category', 16);
            $table->index(['nodeid','category']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nsbulletin_categories');
    }
}
