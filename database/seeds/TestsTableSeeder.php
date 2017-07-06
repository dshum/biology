<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tests')->insert([
            'name' => 'Ломоносов 2012',
			'order' => 1,
            'topic_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('tests')->insert([
            'name' => 'Покори Воробьевы горы 2014-2',
			'order' => 2,
            'topic_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('tests')->insert([
            'name' => 'Покори Воробьевы горы 2014-1',
			'order' => 3,
            'topic_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('tests')->insert([
            'name' => 'Покори Воробьевы горы 2015-1',
			'order' => 4,
            'topic_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('tests')->insert([
            'name' => 'Покори Воробьевы горы 2015-7',
			'order' => 5,
            'topic_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('tests')->insert([
            'name' => 'Покори Воробьевы горы 2016',
			'order' => 6,
            'topic_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
