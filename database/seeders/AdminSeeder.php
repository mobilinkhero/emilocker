<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@noxlock.com'],
            [
                'name'     => 'Super Admin',
                'password' => bcrypt('admin123'),
                'role'     => 'super_admin',
            ]
        );

        $this->command->info('Admin created: admin@noxlock.com / admin123');
    }
}
