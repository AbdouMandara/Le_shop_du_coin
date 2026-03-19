<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function up(): void
    {
        Role::create(['label' => 'admin']);
        Role::create(['label' => 'user']);
        Role::create(['label' => 'livreur']);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['label' => 'admin']);
        Role::updateOrCreate(['label' => 'user']);
        Role::updateOrCreate(['label' => 'livreur']);
    }
}
