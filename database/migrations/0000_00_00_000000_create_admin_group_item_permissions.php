<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminGroupItemPermissions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admin_group_item_permissions', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('group_id')->unsigned()->index();
			$table->string('class')->index();
			$table->string('permission');
            $table->engine = 'InnoDB';
			$table->unique(array('group_id', 'class'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admin_group_item_permissions');
	}

}
