<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('topics')->insert([
            'name' => 'Олимпиады',
			'order' => 1,
            'hidden' => false,
            'subject_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
