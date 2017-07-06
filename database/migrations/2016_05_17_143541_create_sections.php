<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('order');
			$table->string('url');
			$table->string('title')->nullable();
			$table->string('h1')->nullable();
			$table->string('meta_keywords')->nullable();
			$table->text('meta_description')->nullable();
			$table->mediumText('fullcontent')->nullable();
			$table->integer('section_id')->unsigned()->nullable()->default(null)->index();
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
        Schema::drop('sections');
    }
}
