<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 払った人（太郎 or 花子）
            $table->date('date'); // 日付
            $table->string('item'); // 項目（食費、光熱費、医療費等）
            $table->integer('amount'); // 払った金額（円）
            $table->boolean('is_full_settlement')->default(false); // 全額精算チェック
            $table->timestamps();
            
            // インデックス追加（月ごとの検索を高速化）
            $table->index(['date', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

