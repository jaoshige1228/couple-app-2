<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettlementController extends Controller
{
    /**
     * 月ごとの精算計算結果を取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        // 太郎と花子のユーザー情報を取得
        $taro = User::where('name', '太郎')->first();
        $hanako = User::where('name', '花子')->first();

        if (!$taro || !$hanako) {
            return response()->json(['error' => 'ユーザーが見つかりません。'], 404);
        }

        // 月の開始日と終了日
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // 太郎の支出を取得
        $taroExpenses = Expense::where('user_id', $taro->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // 花子の支出を取得
        $hanakoExpenses = Expense::where('user_id', $hanako->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // 太郎が払った金額の合計（通常の割り勘分）
        $taroNormalAmount = $taroExpenses->where('is_full_settlement', false)->sum('amount');
        // 太郎が払った金額の合計（全額精算分）
        $taroFullAmount = $taroExpenses->where('is_full_settlement', true)->sum('amount');

        // 花子が払った金額の合計（通常の割り勘分）
        $hanakoNormalAmount = $hanakoExpenses->where('is_full_settlement', false)->sum('amount');
        // 花子が払った金額の合計（全額精算分）
        $hanakoFullAmount = $hanakoExpenses->where('is_full_settlement', true)->sum('amount');

        // 精算計算
        // 太郎が払った金額の半分を花子が支払う必要がある
        $hanakoShouldPayToTaro = $taroNormalAmount / 2;
        // 花子が払った金額の半分を太郎が支払う必要がある
        $taroShouldPayToHanako = $hanakoNormalAmount / 2;
        // 太郎が払った全額精算分を花子が支払う必要がある
        $hanakoShouldPayToTaroFull = $taroFullAmount;
        // 花子が払った全額精算分を太郎が支払う必要がある
        $taroShouldPayToHanakoFull = $hanakoFullAmount;

        // 最終的な精算額を計算
        // 花子が太郎に支払うべき金額
        $hanakoToTaro = $hanakoShouldPayToTaro + $hanakoShouldPayToTaroFull;
        // 太郎が花子に支払うべき金額
        $taroToHanako = $taroShouldPayToHanako + $taroShouldPayToHanakoFull;

        // 差額を計算
        $settlementAmount = $hanakoToTaro - $taroToHanako;

        // 結果を整形
        $result = [
            'year' => $year,
            'month' => $month,
            'taro' => [
                'name' => '太郎',
                'total_paid' => $taroExpenses->sum('amount'),
                'normal_amount' => $taroNormalAmount,
                'full_settlement_amount' => $taroFullAmount,
            ],
            'hanako' => [
                'name' => '花子',
                'total_paid' => $hanakoExpenses->sum('amount'),
                'normal_amount' => $hanakoNormalAmount,
                'full_settlement_amount' => $hanakoFullAmount,
            ],
            'settlement' => [
                'amount' => abs($settlementAmount),
                'payer' => $settlementAmount > 0 ? '花子' : ($settlementAmount < 0 ? '太郎' : null),
                'payee' => $settlementAmount > 0 ? '太郎' : ($settlementAmount < 0 ? '花子' : null),
                'message' => $settlementAmount == 0
                    ? "精算は完了しています。"
                    : ($settlementAmount > 0 
                        ? "花子が太郎に" . number_format(abs($settlementAmount)) . "円を払えば精算が完了します。"
                        : "太郎が花子に" . number_format(abs($settlementAmount)) . "円を払えば精算が完了します。"),
            ],
        ];

        return response()->json($result);
    }
}

