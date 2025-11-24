<?php

use Illuminate\Support\Facades\Route;

// SPA用のルート - すべてのルートをVueアプリケーションにリダイレクト
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
