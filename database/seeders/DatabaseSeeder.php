<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $managerUser = User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'is_manager' => true,
        ]);

        $staffUser = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);

        $adminTeam = Team::forceCreate([
            'user_id' => $adminUser->id,
            'name' => 'Admin',
            'personal_team' => false,
        ]);

        $managerTeam = Team::forceCreate([
            'user_id' => $adminUser->id,
            'name' => 'Manager',
            'personal_team' => false,
        ]);

        $staffTeam = Team::forceCreate([
            'user_id' => $adminUser->id,
            'name' => 'Staff',
            'personal_team' => false,
        ]);

        // Manager user is member of Manager/Admin/Staff
        $managerUser->teams()->attach($managerTeam, ['role' => 'admin']);
        $managerUser->teams()->attach($adminTeam, ['role' => 'admin']);
        $managerUser->teams()->attach($staffTeam, ['role' => 'admin']);

        // Admin user is member of Admin/Staff
        $adminUser->teams()->attach($adminTeam, ['role' => 'admin']);
        $adminUser->teams()->attach($staffTeam, ['role' => 'admin']);

        // Staff user is member of Staff Team only
        $staffUser->teams()->attach($staffTeam, ['role' => 'editor']);

        User::factory()->count(10)->create([
            'password' => 'password',
        ])->each(function ($user) use ($staffTeam) {
            $staffTeam->users()->attach($user, ['role' => 'editor']);
        });
    }
}
