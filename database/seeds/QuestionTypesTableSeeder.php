<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class QuestionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('question_types')->insert([
            'name' => 'Одиночный выбор',
			'order' => 1,
            'service_section_id' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('question_types')->insert([
            'name' => 'Множественный выбор',
			'order' => 2,
            'service_section_id' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('question_types')->insert([
            'name' => 'Соответствие букв и чисел',
			'order' => 3,
            'service_section_id' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('question_types')->insert([
            'name' => 'Строка',
			'order' => 4,
            'service_section_id' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('question_types')->insert([
            'name' => 'Развернутый ответ',
			'order' => 5,
            'service_section_id' => 5,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
