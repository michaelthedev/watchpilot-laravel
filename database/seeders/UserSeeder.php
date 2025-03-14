<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user
        User::create([
            'first_name' => 'Mike',
            'last_name' => 'Test',
            'username' => 'tester',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
    }
}
