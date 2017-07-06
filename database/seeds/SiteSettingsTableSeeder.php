<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SiteSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('site_settings')->insert([
            'name' => 'Настройки сайта',
			'title' => 'Тесты ЕГЭ по биологии',
			'meta_keywords' => '',
			'meta_description' => '',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
