<template>
    <div class="max-w-2xl mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">
                {{ isEdit ? '支出を編集' : '支出を登録' }}
            </h2>
            
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        日付
                    </label>
                    <input
                        id="date"
                        v-model="form.date"
                        type="date"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>

                <div>
                    <label for="item" class="block text-sm font-medium text-gray-700 mb-2">
                        項目
                    </label>
                    <select
                        id="item"
                        v-model="form.item"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">選択してください</option>
                        <option value="食費">食費</option>
                        <option value="光熱費">光熱費</option>
                        <option value="医療費">医療費</option>
                        <option value="交通費">交通費</option>
                        <option value="日用品">日用品</option>
                        <option value="外食">外食</option>
                        <option value="娯楽">娯楽</option>
                        <option value="衣服">衣服</option>
                        <option value="雑費">雑費</option>
                        <option value="その他">その他</option>
                    </select>
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        金額（円）
                    </label>
                    <input
                        id="amount"
                        v-model.number="form.amount"
                        type="number"
                        required
                        min="1"
                        placeholder="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        払った人
                    </label>
                    <div class="px-3 py-2 bg-gray-100 rounded-md text-gray-700">
                        {{ user?.name }}
                    </div>
                </div>

                <div class="flex items-center">
                    <input
                        id="is_full_settlement"
                        v-model="form.is_full_settlement"
                        type="checkbox"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    />
                    <label for="is_full_settlement" class="ml-2 block text-sm text-gray-700">
                        全額精算（チェックを入れると、相手に全額精算を求めます）
                    </label>
                </div>

                <div>
                    <label for="memo" class="block text-sm font-medium text-gray-700 mb-2">
                        メモ（任意、最大100文字）
                    </label>
                    <textarea
                        id="memo"
                        v-model="form.memo"
                        maxlength="100"
                        rows="3"
                        placeholder="メモを入力してください"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ (form.memo || '').length }}/100文字
                    </p>
                </div>

                <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-800">{{ errorMessage }}</p>
                </div>

                <div v-if="successMessage" class="rounded-md bg-green-50 p-4">
                    <p class="text-sm text-green-800">{{ successMessage }}</p>
                </div>

                <div class="flex space-x-4">
                    <button
                        type="submit"
                        :disabled="loading || deleting"
                        class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">{{ isEdit ? '更新中...' : '登録中...' }}</span>
                        <span v-else>{{ isEdit ? '更新' : '登録' }}</span>
                    </button>
                    <button
                        v-if="!isEdit"
                        type="button"
                        @click="resetForm"
                        class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        リセット
                    </button>
                    <button
                        v-if="isEdit"
                        type="button"
                        @click="handleDelete"
                        :disabled="loading || deleting"
                        class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="deleting">削除中...</span>
                        <span v-else>削除</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { useExpenses } from '../composables/useExpenses';
import api from '../api';

const router = useRouter();
const route = useRoute();
const { user } = useAuth();
const { createExpense, updateExpense, deleteExpense, loading } = useExpenses();

const isEdit = computed(() => !!route.params.id);

const form = ref({
    date: new Date().toISOString().split('T')[0],
    item: '',
    amount: null,
    is_full_settlement: false,
    memo: '',
});

const errorMessage = ref('');
const successMessage = ref('');
const deleting = ref(false);

onMounted(async () => {
    if (isEdit.value) {
        // 編集モードの場合、既存データを読み込む
        try {
            const expense = await fetchExpense(route.params.id);
            if (expense) {
                // 日付をYYYY-MM-DD形式に変換
                let formattedDate = expense.date;
                if (expense.date && expense.date.includes('T')) {
                    // ISO形式の場合
                    formattedDate = expense.date.split('T')[0];
                } else if (expense.date) {
                    // 既にYYYY-MM-DD形式の場合
                    formattedDate = expense.date;
                }
                
                console.log('Loaded expense date:', expense.date, '-> formatted:', formattedDate);
                
                form.value = {
                    date: formattedDate,
                    item: expense.item,
                    amount: expense.amount,
                    is_full_settlement: expense.is_full_settlement,
                    memo: expense.memo || '',
                };
            }
        } catch (error) {
            errorMessage.value = '支出データの読み込みに失敗しました。';
        }
    }
});

const fetchExpense = async (id) => {
    const response = await api.get(`/expenses/${id}`);
    return response.data;
};

const handleSubmit = async () => {
    errorMessage.value = '';
    successMessage.value = '';
    
    try {
        // 送信データを準備（空文字列のmemoはnullに変換）
        const submitData = {
            ...form.value,
            memo: form.value.memo && form.value.memo.trim() ? form.value.memo.trim() : null,
        };
        
        if (isEdit.value) {
            await updateExpense(route.params.id, submitData);
            successMessage.value = '支出を更新しました。';
        } else {
            await createExpense(submitData);
            successMessage.value = '支出を登録しました。';
        }
        
        // 成功後、登録した年月の支出一覧ページにリダイレクト
        setTimeout(() => {
            const expenseDate = new Date(form.value.date);
            const year = expenseDate.getFullYear();
            const month = expenseDate.getMonth() + 1;
            router.push({
                path: '/expenses',
                query: { year, month }
            });
        }, 1000);
    } catch (error) {
        console.error('Submit error:', error);
        errorMessage.value = isEdit.value 
            ? '支出の更新に失敗しました。'
            : '支出の登録に失敗しました。';
    }
};

const handleDelete = async () => {
    if (!confirm('本当にこの支出を削除しますか？')) {
        return;
    }
    
    deleting.value = true;
    errorMessage.value = '';
    
    try {
        await deleteExpense(route.params.id);
        successMessage.value = '支出を削除しました。';
        
        // 成功後、支出一覧ページにリダイレクト（年月パラメータを保持）
        setTimeout(() => {
            const query = route.query;
            router.push({
                path: '/expenses',
                query: query.year && query.month ? { year: query.year, month: query.month } : {}
            });
        }, 1000);
    } catch (error) {
        errorMessage.value = '支出の削除に失敗しました。';
        deleting.value = false;
    }
};

const resetForm = () => {
    form.value = {
        date: new Date().toISOString().split('T')[0],
        item: '',
        amount: null,
        is_full_settlement: false,
        memo: '',
    };
    errorMessage.value = '';
    successMessage.value = '';
};
</script>

