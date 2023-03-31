<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\ProductStatus;
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
            [
                'name' => 'admin',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => $time,
                'updated_at' => $time,
            ],
            [
                'name' => 'admin2',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => $time,
                'updated_at' => $time,
            ],
            [
                'name' => 'sale',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => $time,
                'updated_at' => $time,
            ],
            [
                'name' => 'sale2',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'created_at' => $time,
                'updated_at' => $time,
            ]
        ]);

        $roles = ['admin', 'sale', 'investor', 'customer'];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role,
                'created_at' => $time,
                'updated_at' => $time
            ]);
        }

        DB::table('role_user')->insert([
            [
                'role_id' => 1,
                'user_id' => 1,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'role_id' => 2,
                'user_id' => 1,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'role_id' => 1,
                'user_id' => 2,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'role_id' => 2,
                'user_id' => 2,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'role_id' => 2,
                'user_id' => 3,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'role_id' => 2,
                'user_id' => 4,
                'created_at' => $time,
                'updated_at' => $time
            ],
        ]);

        DB::table('products')->insert([
            [
                'name' => 'ချိုခါးစိမ့် အပူ',
                'price' => 1200,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ချိုခါးစိမ့် အအေး',
                'price' => 1500,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'Black Coffee Ice',
                'price' => 1500,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'Black Coffee Hot',
                'price' => 1200,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'Honey Crush Coffee',
                'price' => 1800,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'Expresso',
                'price' => 1500,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ဒိန်ချဉ်',
                'price' => 1500,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'စတော်ဘယ်ရီ(ဒိန်ချဉ်) ဖျော်ရည် ',
                'price' => 1800,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ရေသန့် 1L',
                'price' => 600,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ရေသန့် 0.35L',
                'price' => 400,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'စိတ်တိုင်းကျ ကော်ဖီထုပ်',
                'price' => 4000,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ချစ်ကောင်း ကော်ဖီထုပ်',
                'price' => 4500,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ရိုးရာ ကော်ဖီထုပ်',
                'price' => 4500,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ပေါင်းစီး ကော်ဖီကဒ်',
                'price' => 3500,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'မေမြို့ နှပ်ကော်ဖီထုပ်',
                'price' => 3800,
                'created_at' => $time,
                'updated_at' => $time
            ],
        ]);

        DB::table('products')->insert([
            [
                'name' => 'ခါးစိမ့်',
                'price' => 1200,
                'status' => ProductStatus::DISABLED->value,
                'created_at' => $time,
                'updated_at' => $time
            ],
            [
                'name' => 'ချိုစိမ့်',
                'price' => 1200,
                'status' => ProductStatus::DISABLED->value,
                'created_at' => $time,
                'updated_at' => $time
            ],
        ]);

        DB::table('toppings')->insert([
            [
                'name' => 'သစ်ကြားသီး',
                'price' => 300,
                'created_at' => $time,
                'updated_at' => $time
            ]
        ]);
    }
}
