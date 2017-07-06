<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Moonlight\Models\User;
use Moonlight\Models\Group;
use Carbon\Carbon;

class MoonlightUserTableSeeder extends Seeder {

	public function run()
	{
		DB::table('admin_users')->insert([
			'login' => 'magus',
			'password' => password_hash("secret", PASSWORD_DEFAULT),
			'email' => 'denis-shumeev@yandex.ru',
			'first_name' => 'Super',
			'last_name' => 'Magus',
			'superuser' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);

		DB::table('admin_users')->insert([
			'login' => 'denis',
			'password' => password_hash("qwerty", PASSWORD_DEFAULT),
			'email' => 'denis-shumeev@yandex.ru',
			'first_name' => 'Denis',
			'last_name' => 'Shumeev',
			'superuser' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);
        
        DB::table('admin_users')->insert([
			'login' => 'stepa',
			'password' => password_hash("qwerty", PASSWORD_DEFAULT),
			'email' => 'stepenin@yandex.ru',
			'first_name' => 'Андрей',
			'last_name' => 'Степенин',
			'superuser' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);
        
        DB::table('admin_users')->insert([
			'login' => 'vera',
			'password' => password_hash("qwerty", PASSWORD_DEFAULT),
			'email' => 'vegorova@mail.ru',
			'first_name' => 'Вера',
			'last_name' => 'Егорова',
			'superuser' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);
        
        DB::table('admin_users')->insert([
			'login' => 'valeria',
			'password' => password_hash("qwerty", PASSWORD_DEFAULT),
			'email' => 'valeria-guzhvinskaya@yandex.ru',
			'first_name' => 'Валерия',
			'last_name' => 'Гужвинская',
			'superuser' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
		]);
	}
}
