<?php

namespace Database\Seeders;

use App\Models\User;
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
        DB::table('missing_isrcs')->truncate();
        DB::table('missing_isrcs')->insert([
            ['code' => 'US7VG1846811'],
            ['code' => 'US7QQ1846811'],
            ['code' => 'BRC310600002'],
            ['code' => 'BR1SP1200071'],
            ['code' => 'BR1SP1200070'],
            ['code' => 'BR1SP1500002'],
            ['code' => 'BXKZM1900338'],
            ['code' => 'BXKZM1900345'],
            ['code' => 'QZNJX2081700'],
            ['code' => 'QZNJX2078148'],
        ]);
    }
}
