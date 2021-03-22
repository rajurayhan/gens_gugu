<?php

use Illuminate\Database\Seeder;

class E2ETestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(E2ETestToCreateDefinitionInfoSeeder::class);
        $this->call(E2ETestToCreateRawDataSeeder::class);
    }
}
