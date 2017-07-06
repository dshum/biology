<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_types', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('order');
			$table->integer('service_section_id')->unsigned()->nullable()->default(null)->index();
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
        Schema::drop('question_types');
    }
}
