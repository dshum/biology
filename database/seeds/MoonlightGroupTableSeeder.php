<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Moonlight\Models\Group;
use Carbon\Carbon;

class MoonlightGroupTableSeeder extends Seeder {

	public function run()
	{
		DB::table('admin_groups')->insert([
            'name' => 'Системные пользователи',
			'default_permission' => 'delete',
			'permissions' => serialize([
				'admin' => 1,
			]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

		DB::table('admin_groups')->insert([
			'name' => 'Администраторы',
			'default_permission' => 'delete',
			'permissions' => serialize([
				'admin' => 0,
			]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);

		DB::table('admin_groups')->insert([
			'name' => 'Модераторы',
			'default_permission' => 'deny',
			'permissions' => serialize([
				'admin' => 0,
			]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);
	}
}
