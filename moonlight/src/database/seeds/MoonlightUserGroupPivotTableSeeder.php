<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Moonlight\Models\Group;

class MoonlightUserGroupPivotTableSeeder extends Seeder {

	public function run()
	{
		DB::table('admin_users_groups_pivot')->insert([
            'user_id' => 2,
			'group_id' => 1,
        ]);
        
        DB::table('admin_users_groups_pivot')->insert([
            'user_id' => 3,
			'group_id' => 1,
        ]);
        
        DB::table('admin_users_groups_pivot')->insert([
            'user_id' => 4,
			'group_id' => 1,
        ]);
        
        DB::table('admin_users_groups_pivot')->insert([
            'user_id' => 5,
			'group_id' => 2,
        ]);
	}
}
