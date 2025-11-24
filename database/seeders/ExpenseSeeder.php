<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taro = User::where('name', '太郎')->first();
        $hanako = User::where('name', '花子')->first();

        if (!$taro || !$hanako) {
            $this->command->error('太郎と花子のユーザーが見つかりません。先にDatabaseSeederを実行してください。');
            return;
        }

        $items = ['食費', '光熱費', '医療費', '交通費', '日用品', '外食', '娯楽', '衣服', '雑費', 'その他'];
        $memos = [
            'スーパーで買い物',
            'コンビニ',
            '病院',
            '電車代',
            'タクシー',
            '薬局',
            'レストラン',
            '映画',
            '本',
            'プレゼント',
            null, // メモなしも含める
        ];

        $users = [$taro, $hanako];
        $expenses = [];

        // 過去6ヶ月分のデータを作成
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        for ($i = 0; $i < 100; $i++) {
            // ランダムな日付を生成
            $randomDays = rand(0, $startDate->diffInDays($endDate));
            $date = $startDate->copy()->addDays($randomDays);

            // ランダムなユーザーを選択
            $user = $users[array_rand($users)];

            // ランダムな項目と金額
            $item = $items[array_rand($items)];
            $amount = rand(100, 10000) * 10; // 100円〜100,000円（10円単位）

            // 全額精算は20%の確率
            $isFullSettlement = rand(1, 100) <= 20;

            // メモは70%の確率で追加
            $memo = null;
            if (rand(1, 100) <= 70) {
                $memo = $memos[array_rand($memos)];
            }

            $expenses[] = [
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'item' => $item,
                'amount' => $amount,
                'is_full_settlement' => $isFullSettlement,
                'memo' => $memo,
                'created_at' => $date->copy()->addHours(rand(9, 18))->addMinutes(rand(0, 59)),
                'updated_at' => $date->copy()->addHours(rand(9, 18))->addMinutes(rand(0, 59)),
            ];
        }

        // バルクインサートで一括登録
        Expense::insert($expenses);

        $this->command->info('100件のテスト支出データを作成しました。');
    }
}
