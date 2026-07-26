<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default users for testing
        $users = [
            [
                'name' => 'Admin Santrix',
                'email' => 'admin@santrix.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Staff Pendidikan',
                'email' => 'pendidikan@santrix.com',
                'password' => Hash::make('password'),
                'role' => 'pendidikan',
            ],
            [
                'name' => 'Staff Sekretaris',
                'email' => 'sekretaris@santrix.com',
                'password' => Hash::make('password'),
                'role' => 'sekretaris',
            ],
            [
                'name' => 'Staff Bendahara',
                'email' => 'bendahara@santrix.com',
                'password' => Hash::make('password'),
                'role' => 'bendahara',
            ],
            [
                'name'         => 'Owner Santrix',
                'email'        => env('OWNER_EMAIL', 'owner@santrix.com'),
                'password'     => Hash::make(env('OWNER_PASSWORD', 'changeme123')),
                'role'         => 'owner',
                'pesantren_id' => null,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✅ Default users created successfully!');
        $this->command->info('📧 Lihat kredensial di file .env (OWNER_EMAIL, OWNER_PASSWORD)');
        $this->command->info('📧 Email: admin@santrix.com | Password: password');
        $this->command->info('📧 Email: pendidikan@santrix.com | Password: password');
        $this->command->info('📧 Email: sekretaris@santrix.com | Password: password');
        $this->command->info('📧 Email: bendahara@santrix.com | Password: password');
    }
}
