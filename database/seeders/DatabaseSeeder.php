<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Empresa\Empresa::query()->truncate();
        \App\Models\Empresa\Empresa::factory(1)->create();

        $this->call(UserSeeder::class);
        $this->call(UbicacionSeeder::class);
    }
}
