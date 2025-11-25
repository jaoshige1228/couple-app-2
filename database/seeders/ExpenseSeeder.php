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

        // 登録画面で選べる項目（ExpenseForm.vueと同じ）
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

        // 2025年9月、10月、11月のデータを作成
        $months = [
            ['year' => 2025, 'month' => 9],
            ['year' => 2025, 'month' => 10],
            ['year' => 2025, 'month' => 11],
        ];

        foreach ($months as $monthData) {
            $year = $monthData['year'];
            $month = $monthData['month'];
            
            // 月の開始日と終了日
            $startDate = Carbon::create($year, $month, 1);
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            $daysInMonth = $endDate->day;

            // 各月に10件のデータを作成
            for ($i = 0; $i < 10; $i++) {
                // ランダムな日付を生成（その月内）
                $randomDay = rand(1, $daysInMonth);
                $date = Carbon::create($year, $month, $randomDay);

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

                // 登録日時と更新日時（登録と編集を区別するため、少しランダムに）
                $createdAt = $date->copy()->addHours(rand(9, 18))->addMinutes(rand(0, 59));
                $updatedAt = $createdAt->copy();
                
                // 編集された可能性を20%で設定（更新日時を後ろにずらす）
                if (rand(1, 100) <= 20) {
                    $updatedAt = $createdAt->copy()->addDays(rand(1, 5))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                }

                $expenses[] = [
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'item' => $item,
                    'amount' => $amount,
                    'is_full_settlement' => $isFullSettlement,
                    'memo' => $memo,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            }
        }

        // バルクインサートで一括登録
        Expense::insert($expenses);

        $this->command->info('2025年9月、10月、11月にそれぞれ10件ずつ、合計30件のテスト支出データを作成しました。');
    }
}
