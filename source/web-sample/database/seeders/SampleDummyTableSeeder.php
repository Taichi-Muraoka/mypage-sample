<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sample;

class SampleDummyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Sample::factory()->count(10)->create();
    }
}
