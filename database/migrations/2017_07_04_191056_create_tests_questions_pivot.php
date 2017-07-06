<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestsQuestionsPivot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tests_questions_pivot', function(Blueprint $table)
		{
			$table->integer('question_id')->unsigned()->index();
			$table->integer('test_id')->unsigned()->index();
			$table->primary(['question_id', 'test_id']);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tests_questions_pivot');
    }
}
