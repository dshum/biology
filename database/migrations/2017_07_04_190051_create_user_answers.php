<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_answers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('user_question_id')->unsigned()->nullable()->default(null)->index();
            $table->integer('answer_id')->unsigned()->nullable()->default(null)->index();
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
        Schema::drop('user_answers');
    }
}
