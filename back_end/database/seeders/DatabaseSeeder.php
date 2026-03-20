<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRole = Role::where('label', 'admin')->first();
        $userRole = Role::where('label', 'user')->first();
        $livreurRole = Role::where('label', 'livreur')->first();

        // 1. Create Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
        ]);

        // 2. Create Standard User
        User::factory()->create([
            'name' => 'Client Test',
            'email' => 'user@user.com',
            'password' => bcrypt('password'),
            'role_id' => $userRole->id,
        ]);

        // 3. Create Livreur
        User::factory()->create([
            'name' => 'Livreur Test',
            'email' => 'livreur@livreur.com',
            'password' => bcrypt('password'),
            'role_id' => $livreurRole->id,
        ]);

        // 4. Create Categories and Products
        \App\Models\Category::factory()
            ->count(5)
            ->has(\App\Models\Product::factory()->count(3))
            ->create();
    }
}
