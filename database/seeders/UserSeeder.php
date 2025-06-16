<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'guid' => (string) Str::uuid(),
            'name' => 'Super Admin',
            'email' => 'andries@okappi.be',
            'password' => Hash::make('@dmin123'),
        ]);
    }
}
