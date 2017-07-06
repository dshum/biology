<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(SectionsTableSeeder::class);
        $this->call(ServiceSectionsTableSeeder::class);
        $this->call(SiteSettingsTableSeeder::class);
        $this->call(SubjectsTableSeeder::class);
        $this->call(TopicsTableSeeder::class);
        $this->call(TestsTableSeeder::class);
        $this->call(QuestionTypesTableSeeder::class);
    }
}
