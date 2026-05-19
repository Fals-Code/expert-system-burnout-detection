<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BurnoutXpertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function up(): void
    {
        // Disable foreign key checks to safely truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('divisi')->truncate();
        DB::table('settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Divisi
        DB::table('divisi')->insert([
            ['id' => 1, 'nama' => 'Engineering'],
            ['id' => 2, 'nama' => 'Marketing'],
            ['id' => 3, 'nama' => 'Finance'],
            ['id' => 4, 'nama' => 'Human Resources'],
            ['id' => 5, 'nama' => 'Operations'],
        ]);

        // 2. Users
        DB::table('users')->insert([
            [
                'id' => 1,
                'nama' => 'Ahmad Fauzi',
                'email' => 'karyawan@burnoutxpert.com',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
                'divisi_id' => 1,
            ],
            [
                'id' => 2,
                'nama' => 'Siti Rahayu',
                'email' => 'hrd@burnoutxpert.com',
                'password' => Hash::make('password'),
                'role' => 'hrd',
                'divisi_id' => 4,
            ],
            [
                'id' => 3,
                'nama' => 'Budi Santoso',
                'email' => 'admin@burnoutxpert.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'divisi_id' => 1,
            ],
        ]);

        // 3. Settings
        DB::table('settings')->insert([
            ['kunci' => 'app_name', 'nilai' => 'BurnoutXpert'],
            ['kunci' => 'threshold_high', 'nilai' => '0.8'],
            ['kunci' => 'maintenance_mode', 'nilai' => '0'],
        ]);
    }

    public function run(): void
    {
        $this->up();
    }
}
