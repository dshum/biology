<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'email' => 'denis-shumeev@yandex.ru',
			'password' => password_hash('qwerty', PASSWORD_DEFAULT),
			'first_name' => 'Денис',
            'last_name' => 'Шумеев',
            'activated' => true,
            'banned' => false,
            'service_section_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            'email' => 'vegorova@mail.ru',
			'password' => password_hash('qwerty', PASSWORD_DEFAULT),
			'first_name' => 'Вера',
            'last_name' => 'Егорова',
            'activated' => true,
            'banned' => false,
            'service_section_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
