<template>
    <div class="max-w-2xl mx-auto p-2 sm:p-6">
        <div class="bg-white shadow-md rounded-lg p-4 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg sm:text-2xl font-bold text-gray-800">支出詳細</h2>
                <button
                    @click="goBack"
                    class="text-gray-600 hover:text-gray-900 text-sm sm:text-base"
                >
                    ← 戻る
                </button>
            </div>

            <div v-if="loading" class="text-center py-8">
                <p class="text-gray-500">読み込み中...</p>
            </div>

            <div v-else-if="error" class="rounded-md bg-red-50 p-4 mb-4">
                <p class="text-sm text-red-800">{{ error }}</p>
            </div>

            <div v-else-if="expense" class="space-y-6">
                <!-- 日付 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        日付
                    </label>
                    <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                        {{ formatDate(expense.date) }}
                    </div>
                </div>

                <!-- 項目 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        項目
                    </label>
                    <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                        {{ expense.item }}
                    </div>
                </div>

                <!-- 金額 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        金額
                    </label>
                    <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900 text-lg font-semibold">
                        {{ formatCurrency(expense.amount) }}円
                    </div>
                </div>

                <!-- 払った人 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        払った人
                    </label>
                    <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900">
                        {{ expense.user?.name }}
                    </div>
                </div>

                <!-- 全額精算 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        精算方法
                    </label>
                    <div class="px-3 py-2">
                        <span v-if="expense.is_full_settlement" class="px-3 py-1.5 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            全額精算
                        </span>
                        <span v-else class="px-3 py-1.5 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                            割り勘
                        </span>
                    </div>
                </div>

                <!-- メモ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        メモ
                    </label>
                    <div class="px-3 py-2 bg-gray-50 rounded-md text-gray-900 min-h-[60px]">
                        <p v-if="expense.memo" class="whitespace-pre-wrap">{{ expense.memo }}</p>
                        <p v-else class="text-gray-400">メモはありません</p>
                    </div>
                </div>

                <!-- 操作ボタン（自分の支出のみ） -->
                <div v-if="expense.user_id === currentUserId" class="flex space-x-3 pt-4 border-t">
                    <button
                        @click="editExpense"
                        class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        編集
                    </button>
                    <button
                        @click="handleDelete"
                        :disabled="deleting"
                        class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="deleting">削除中...</span>
                        <span v-else>削除</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { useExpenses } from '../composables/useExpenses';
import api from '../api';

const router = useRouter();
const route = useRoute();
const { user } = useAuth();
const { deleteExpense } = useExpenses();

const expense = ref(null);
const loading = ref(true);
const error = ref('');
const deleting = ref(false);

const currentUserId = computed(() => user.value?.id);

const fetchExpense = async (id) => {
    try {
        const response = await api.get(`/expenses/${id}`);
        return response.data;
    } catch (err) {
        if (err.response?.status === 404) {
            throw new Error('支出が見つかりません。');
        }
        throw new Error('支出データの読み込みに失敗しました。');
    }
};

const loadData = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        expense.value = await fetchExpense(route.params.id);
    } catch (err) {
        error.value = err.message;
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日`;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ja-JP').format(amount);
};

const goBack = () => {
    // 戻る際に年月パラメータを保持
    const query = route.query;
    router.push({
        path: '/expenses',
        query: query.year && query.month ? { year: query.year, month: query.month } : {}
    });
};

const editExpense = () => {
    // 編集画面に遷移（年月パラメータを保持）
    const query = route.query;
    router.push({
        path: `/expenses/${route.params.id}/edit`,
        query: query.year && query.month ? { year: query.year, month: query.month } : {}
    });
};

const handleDelete = async () => {
    if (!confirm('本当にこの支出を削除しますか？')) {
        return;
    }
    
    deleting.value = true;
    error.value = '';
    
    try {
        await deleteExpense(route.params.id);
        
        // 削除後、支出一覧に戻る（年月パラメータを保持）
        const query = route.query;
        router.push({
            path: '/expenses',
            query: query.year && query.month ? { year: query.year, month: query.month } : {}
        });
    } catch (err) {
        error.value = '支出の削除に失敗しました。';
        deleting.value = false;
    }
};

onMounted(() => {
    loadData();
});
</script>

