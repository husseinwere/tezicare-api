<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'first_name' => 'Hussein',
            'last_name' => 'Were',
            'phone' => '0727854413',
            'email' => 'hussein.were@tezi.co.ke',
            'roles' => 'Administrator',
            'password' => 'novacandy'
        ]);
    }
}
