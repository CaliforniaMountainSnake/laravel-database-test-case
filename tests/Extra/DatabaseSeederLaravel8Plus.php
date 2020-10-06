<?php

use Illuminate\Database\Seeder;
use App\User;

class DatabaseSeederLaravel8Plus extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(5)->create();
    }
}
