<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ServiceSectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        DB::table('service_sections')->insert([
            'name' => 'Ученики',
			'order' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

        ]);
        
        DB::table('service_sections')->insert([
            'name' => 'Предметы',
			'order' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('service_sections')->insert([
            'name' => 'Справочники',
			'order' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        DB::table('service_sections')->insert([
            'name' => 'Загрузка тестов',
			'order' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        
        DB::table('service_sections')->insert([
            'name' => 'Типы вопросов',
			'order' => 5,
            'service_section_id' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
