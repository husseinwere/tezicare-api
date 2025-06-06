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
        DB::table('hospitals')->insert([
            'name' => 'Test Hospital',
            'phone' => '0727854413',
            'email' => 'hussein.were@tezi.co.ke',
            'address' => 'Test Address',
            'registration_fee' => '500',
        ]);

        DB::table('users')->insert([
            'hospital_id' => 1,
            'first_name' => 'Hussein',
            'last_name' => 'Were',
            'phone' => '0727854413',
            'email' => 'hussein.were@tezi.co.ke',
            'roles' => 'ADMINISTRATOR',
            'password' => '$2a$12$Lx.UD20iAN1SG9iLwUr0g.3WijIyOKJPIpR.3dtqcRDb9dbD6u8UK'
        ]);

        DB::table('consultation_types')->insert([
            [
                'hospital_id' => 1,
                'name' => 'General',
                'price' => '500',
                'inpatient_doctor_rate' => '500',
                'inpatient_nurse_rate' => '300',
                'can_delete' => 0
            ],
            [
                'hospital_id' => 1,
                'name' => 'Dentist',
                'price' => '1000',
                'inpatient_doctor_rate' => NULL,
                'inpatient_nurse_rate' => NULL,
                'can_delete' => 0
            ],
            [
                'hospital_id' => 1,
                'name' => 'Pediatrician',
                'price' => '2000',
                'inpatient_doctor_rate' => '1500',
                'inpatient_nurse_rate' => '500',
                'can_delete' => 1
            ]
        ]);

        DB::table('insurance_covers')->insert([
            ['hospital_id' => 1, 'insurance' => 'SHA', 'cap' => '1500', 'created_by' => 1]
        ]);

        DB::table('wards')->insert([
            ['hospital_id' => 1, 'name' => 'Male Ward', 'price' => '2500', 'created_by' => 1]
        ]);

        DB::table('beds')->insert([
            ['ward_id' => 1, 'name' => 'B90090909', 'created_by' => 1]
        ]);
    }
}
