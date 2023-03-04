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
        $time = now();

        DB::table('users')->insert([
            'name' => 'admin',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $roles = ['admin', 'sale'];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role,
                'created_at' => $time,
                'updated_at' => $time
            ]);
        }

        DB::table('role_user')->insert([
            'role_id' => 1,
            'user_id' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);
    }
}
