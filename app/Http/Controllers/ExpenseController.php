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
        
        // 自分の支出のみアクセス可能
        if ($expense->user_id !== auth()->id()) {
            return response()->json(['error' => '権限がありません。'], 403);
        }

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
}

