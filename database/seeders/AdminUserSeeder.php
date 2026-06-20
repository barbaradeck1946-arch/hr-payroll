<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed default administrator role and user with all permissions.
     */
    public function run(): void
    {
        $adminRole = Role::query()->updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full system access',
                'is_system' => true,
            ]
        );

        $adminRole->permissions()->sync(
            \App\Models\Permission::query()->pluck('id')->all()
        );

        $email = env('DEFAULT_ADMIN_EMAIL', 'admin@zerihr.local');
        $password = env('DEFAULT_ADMIN_PASSWORD', 'password');

        $admin = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('DEFAULT_ADMIN_NAME', 'System Admin'),
                'password' => Hash::make($password),
                'account_status' => 'active',
                'approved_at' => now(),
                'rejected_reason' => null,
            ]
        );

        $admin->roles()->syncWithoutDetaching([
            $adminRole->id => [
                'assigned_by' => null,
                'assigned_at' => now(),
            ],
        ]);

        $this->command?->info('Default admin user is ready.');
        $this->command?->line('Email: ' . $email);

        if ($password === 'password') {
            $this->command?->warn('Default password is "password". Change it after first login or set DEFAULT_ADMIN_PASSWORD in .env before seeding.');
        }
    }
}
