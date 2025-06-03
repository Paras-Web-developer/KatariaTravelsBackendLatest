<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\SettingGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('branches')->truncate();

        $branches = [
            [
                'id' => 1,
                'name' => 'Kataria',
                'slug' => 'kataria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Kataria Company',
                'slug' => 'kataria_company',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ];

        Branch::insert($branches);
    }
}
