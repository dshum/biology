<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tests', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('order');
			$table->integer('topic_id')->unsigned()->nullable()->default(null)->index();
            $table->integer('subtopic_id')->unsigned()->nullable()->default(null)->index();
			$table->timestamps();
			$table->softDeletes();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tests');
    }
}
