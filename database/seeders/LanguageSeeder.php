<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('languages')->insert([
            'code' => 'en',
            'name' => 'English',
            'image' => 'Flag_of_the_United_States.png',
        ]);
        DB::table('languages')->insert([
            'code' => 'bn',
            'name' => 'Bengali',
            'image' => 'Flag_of_Bangladesh.png',
        ]);
    }
}
