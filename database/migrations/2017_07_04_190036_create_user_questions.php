<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_questions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
            $table->boolean('correct')->nullable();
            $table->string('answer')->nullable();
			$table->integer('user_test_id')->unsigned()->nullable()->default(null)->index();
            $table->integer('question_id')->unsigned()->nullable()->default(null)->index();
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
        Schema::drop('user_questions');
    }
}
