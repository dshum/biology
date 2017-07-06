<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('order');
            $table->mediumText('question')->nullable();
            $table->mediumText('explanation')->nullable();
            $table->text('comments')->nullable();
            $table->integer('mark')->nullable();
            $table->string('answer')->nullable();
			$table->integer('topic_id')->unsigned()->nullable()->default(null)->index();
            $table->integer('question_type_id')->unsigned()->nullable()->default(null)->index();
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
        Schema::drop('questions');
    }
}
