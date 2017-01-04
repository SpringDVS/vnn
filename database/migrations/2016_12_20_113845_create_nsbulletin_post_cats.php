<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNsbulletinPostCats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nsbulletin_post_cats', function (Blueprint $table) {
        	$table->integer('postid');
        	$table->integer('catid');
        	$table->primary(['postid','catid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nsbulletin_post_cats');
    }
}
