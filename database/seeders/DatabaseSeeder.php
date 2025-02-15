<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'first_name' => 'Hussein',
            'last_name' => 'Were',
            'phone' => '0727854413',
            'email' => 'hussein.were@tezi.co.ke',
            'roles' => 'ADMINISTRATOR',
            'password' => '$2a$12$Lx.UD20iAN1SG9iLwUr0g.3WijIyOKJPIpR.3dtqcRDb9dbD6u8UK'
        ]);

        DB::table('consultation_types')->insert([
            ['name' => 'General', 'price' => '2000', 'inpatient_doctor_rate' => '1000', 'inpatient_nurse_rate' => '500'],
            ['name' => 'Pediatrician', 'price' => '2000', 'inpatient_doctor_rate' => '1500', 'inpatient_nurse_rate' => '500']
        ]);

        DB::table('insurance_covers')->insert([
            ['insurance' => 'SHA', 'cap' => '1500', 'created_by' => 1]
        ]);

        DB::table('wards')->insert([
            ['name' => 'Male Ward', 'price' => '2500', 'created_by' => 1]
        ]);

        DB::table('beds')->insert([
            ['ward_id' => 1, 'name' => 'B90090909', 'created_by' => 1]
        ]);
    }
}
