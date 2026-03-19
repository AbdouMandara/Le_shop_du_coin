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

        // 4. Sample Categories
        $cat1 = \App\Models\Category::create(['label' => 'Électronique']);
        $cat2 = \App\Models\Category::create(['label' => 'Solaire']);

        // 5. Sample Products
        \App\Models\Product::create([
            'name' => 'Smartphone Ultra',
            'price' => 250000,
            'quantity' => 15,
            'description' => 'Un smartphone puissant avec un écran OLED.',
            'category_id' => $cat1->id,
        ]);

        \App\Models\Product::create([
            'name' => 'Panneau Solaire 100W',
            'price' => 75000,
            'quantity' => 25,
            'description' => 'Idéal pour le camping et les petites installations.',
            'category_id' => $cat2->id,
        ]);
    }
}
