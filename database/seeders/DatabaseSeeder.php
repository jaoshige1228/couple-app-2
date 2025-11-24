<?php

namespace Database\Seeders;

use App\Models\User;
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
        // 太郎と花子の初期ユーザーを作成
        User::firstOrCreate(
            ['name' => '太郎'],
            ['password' => bcrypt('123456')] // デフォルトパスワード: 123456
        );

        User::firstOrCreate(
            ['name' => '花子'],
            ['password' => bcrypt('123456')] // デフォルトパスワード: 123456
        );
    }
}
