<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SanctuaryHubSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        DB::table('divisi')->truncate();
        DB::table('settings')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::table('divisi')->insert([
            ['id' => 1, 'nama' => 'Engineering'],
            ['id' => 2, 'nama' => 'Marketing'],
            ['id' => 3, 'nama' => 'Finance'],
            ['id' => 4, 'nama' => 'Human Resources'],
            ['id' => 5, 'nama' => 'Operations'],
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'nama' => 'Karyawan Demo',
                'email' => 'karyawan@sanctuaryhub.test',
                'password' => Hash::make('password'),
                'role' => 'karyawan',
                'divisi_id' => 1,
            ],
            [
                'id' => 2,
                'nama' => 'HRD Demo',
                'email' => 'hrd@sanctuaryhub.test',
                'password' => Hash::make('password'),
                'role' => 'hrd',
                'divisi_id' => 4,
            ],
            [
                'id' => 3,
                'nama' => 'Admin Demo',
                'email' => 'admin@sanctuaryhub.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'divisi_id' => 1,
            ],
        ]);

        DB::table('settings')->insert([
            ['kunci' => 'app_name', 'nilai' => 'SanctuaryHub'],
            ['kunci' => 'default_threshold', 'nilai' => '0.25'],
            ['kunci' => 'maintenance_mode', 'nilai' => '0'],
        ]);
    }
}
