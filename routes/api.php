<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettlementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 認証関連（認証不要）
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// 認証が必要なルート
Route::middleware('auth:sanctum')->group(function () {
    // 現在のユーザー情報取得
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 支出関連
    Route::get('/expenses', [ExpenseController::class, 'index']); // 月ごとの支出一覧
    Route::get('/expenses/count', [ExpenseController::class, 'count']); // 支出の総数
    Route::post('/expenses', [ExpenseController::class, 'store']); // 支出の登録
    Route::get('/expenses/{id}', [ExpenseController::class, 'show']); // 支出の詳細取得
    Route::put('/expenses/{id}', [ExpenseController::class, 'update']); // 支出の更新
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']); // 支出の削除

    // 精算計算
    Route::get('/settlement', [SettlementController::class, 'calculate']); // 月ごとの精算計算結果
});

