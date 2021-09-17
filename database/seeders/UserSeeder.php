<?php

namespace Database\Seeders;

use App\Models\User;
use App\Utilidades\Texto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::query()->truncate();
        User::create(
            [
                'name' => 'John Doe',
                'email' => 'john@doe.com',
                'password' => Hash::make('johnd123'),
            ]
        );
    }
}
