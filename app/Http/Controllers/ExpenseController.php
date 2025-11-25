<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * 月ごとの支出一覧を取得
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        $expenses = Expense::with('user')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($expenses);
    }

    /**
     * 支出の総数を取得
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        $totalCount = Expense::count();
        return response()->json(['count' => $totalCount]);
    }

    /**
     * 支出を登録
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'item' => 'required|string|max:255',
            'amount' => 'required|integer|min:1',
            'is_full_settlement' => 'boolean',
            'memo' => 'nullable|string|max:100',
        ]);

        $expense = Expense::create([
            'user_id' => $request->user()->id,
            'date' => $request->input('date'),
            'item' => $request->input('item'),
            'amount' => $request->input('amount'),
            'is_full_settlement' => $request->input('is_full_settlement', false),
            'memo' => $request->input('memo', null),
        ]);

        return response()->json($expense->load('user'), 201);
    }

    /**
     * 支出の詳細を取得
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $expense = Expense::with('user')->findOrFail($id);
        
        // 閲覧権限は誰でも可能（削除）

        return response()->json($expense);
    }

    /**
     * 支出を更新
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        
        // 自分の支出のみ更新可能
        if ($expense->user_id !== auth()->id()) {
            return response()->json(['error' => '権限がありません。'], 403);
        }

        $request->validate([
            'date' => 'required|date',
            'item' => 'required|string|max:255',
            'amount' => 'required|integer|min:1',
            'is_full_settlement' => 'boolean',
            'memo' => 'nullable|string|max:100',
        ]);

        $expense->update([
            'date' => $request->input('date'),
            'item' => $request->input('item'),
            'amount' => $request->input('amount'),
            'is_full_settlement' => $request->input('is_full_settlement', false),
            'memo' => $request->input('memo', null),
        ]);

        return response()->json($expense->load('user'));
    }

    /**
     * 支出を削除
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        
        // 自分の支出のみ削除可能
        if ($expense->user_id !== auth()->id()) {
            return response()->json(['error' => '権限がありません。'], 403);
        }

        $expense->delete();

        return response()->json(['message' => '支出を削除しました。']);
    }

    /**
     * 月ごとの項目別支出集計を取得（家計簿用）
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function householdBook(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = $request->input('month');

        // 項目別に集計
        $summary = Expense::select('item', DB::raw('SUM(amount) as total'))
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->groupBy('item')
            ->orderBy('total', 'desc')
            ->get();

        // 合計金額を計算
        $totalAmount = Expense::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        return response()->json([
            'summary' => $summary,
            'total' => $totalAmount,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * 履歴一覧を取得（登録と編集の履歴）
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $query = Expense::with('user')
            ->orderBy('updated_at', 'desc');

        // 年月でフィルタリング（指定がある場合）
        if ($request->has('year') && $request->has('month')) {
            $year = $request->input('year');
            $month = $request->input('month');
            $query->whereYear('date', $year)
                  ->whereMonth('date', $month);
        }

        $expenses = $query->get();

        // アクションタイプを判定（登録か編集か）
        $history = $expenses->map(function ($expense) {
            // created_atとupdated_atが同じ（または1秒以内の差）なら「登録」、それ以外は「編集」
            $createdTimestamp = $expense->created_at->timestamp;
            $updatedTimestamp = $expense->updated_at->timestamp;
            $actionType = abs($createdTimestamp - $updatedTimestamp) <= 1 ? '登録' : '編集';
            
            return [
                'id' => $expense->id,
                'date' => $expense->date,
                'item' => $expense->item,
                'amount' => $expense->amount,
                'user' => $expense->user,
                'user_id' => $expense->user_id,
                'is_full_settlement' => $expense->is_full_settlement,
                'memo' => $expense->memo,
                'action_type' => $actionType,
                'created_at' => $expense->created_at,
                'updated_at' => $expense->updated_at,
            ];
        });

        return response()->json($history);
    }
}

