<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('order');
            $table->mediumText('answer')->nullable();
            $table->boolean('correct')->nullable();
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
        Schema::drop('answers');
    }
}
