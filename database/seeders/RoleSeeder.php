<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('roles')->truncate();

        $branches = [
            [
                'id' => 1,
                'name' => 'Admin',
                'slug' => 'admin',
                'status' => 1,
                'description' => 'okey',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'User',
                'slug' => 'user',
                'status' => 1,
                'description' => 'okey',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Employee',
                'slug' => 'employee',
                'status' => 1,
                'description' => 'okey',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'status' => 1,
                'description' => 'okey',
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ];

        Role::insert($branches);
    }
}
