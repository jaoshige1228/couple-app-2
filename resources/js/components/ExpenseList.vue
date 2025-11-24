<template>
    <div class="max-w-6xl mx-auto p-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">月ごとの支出一覧</h2>
                <div class="flex space-x-4">
                    <select
                        v-model="selectedYear"
                        @change="loadData"
                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}年
                        </option>
                    </select>
                    <select
                        v-model="selectedMonth"
                        @change="loadData"
                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option v-for="month in 12" :key="month" :value="month">
                            {{ month }}月
                        </option>
                    </select>
                    <select
                        v-model="filterUser"
                        @change="applyFilter"
                        class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">すべて</option>
                        <option value="太郎">太郎</option>
                        <option value="花子">花子</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="text-center py-8">
                <p class="text-gray-500">読み込み中...</p>
            </div>

            <div v-else-if="error" class="rounded-md bg-red-50 p-4 mb-4">
                <p class="text-sm text-red-800">{{ error }}</p>
            </div>

            <div v-else>
                <div v-if="expenses.length === 0" class="text-center py-8 text-gray-500">
                    この月の支出はありません。
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    日付
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    項目
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    金額
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    払った人
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    全額精算
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    メモ
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    操作
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="expense in filteredExpenses" :key="expense.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(expense.date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ expense.item }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatCurrency(expense.amount) }}円
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ expense.user?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span v-if="expense.is_full_settlement" class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        全額精算
                                    </span>
                                    <span v-else class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        割り勘
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div v-if="expense.memo" class="max-w-xs">
                                        <button
                                            @click="toggleMemo(expense.id)"
                                            class="text-left hover:text-indigo-600 cursor-pointer"
                                        >
                                            <span v-if="expandedMemos[expense.id]">
                                                {{ expense.memo }}
                                            </span>
                                            <span v-else>
                                                {{ truncateMemo(expense.memo) }}
                                            </span>
                                        </button>
                                    </div>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button
                                        v-if="expense.user_id === currentUserId"
                                        @click="editExpense(expense.id)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        編集
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 精算計算結果（前月までのみ表示） -->
                <div v-if="settlement && isPastMonth" class="mt-8 bg-indigo-50 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">精算計算結果</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-md p-4">
                                <p class="text-sm text-gray-600">太郎の支払い合計</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(settlement.taro.total_paid) }}円</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    通常: {{ formatCurrency(settlement.taro.normal_amount) }}円
                                    <span v-if="settlement.taro.full_settlement_amount > 0">
                                        / 全額精算: {{ formatCurrency(settlement.taro.full_settlement_amount) }}円
                                    </span>
                                </p>
                            </div>
                            <div class="bg-white rounded-md p-4">
                                <p class="text-sm text-gray-600">花子の支払い合計</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(settlement.hanako.total_paid) }}円</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    通常: {{ formatCurrency(settlement.hanako.normal_amount) }}円
                                    <span v-if="settlement.hanako.full_settlement_amount > 0">
                                        / 全額精算: {{ formatCurrency(settlement.hanako.full_settlement_amount) }}円
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="bg-white rounded-md p-6 border-2 border-indigo-500">
                            <p class="text-lg font-semibold text-gray-800 mb-2">精算結果</p>
                            <p class="text-2xl font-bold text-indigo-600">
                                {{ settlement.settlement.message }}
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ settlement.settlement.payer }}が{{ settlement.settlement.payee }}に
                                {{ formatCurrency(settlement.settlement.amount) }}円を支払います。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onActivated, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';
import { useExpenses } from '../composables/useExpenses';
import { useSettlement } from '../composables/useSettlement';

const router = useRouter();
const { user } = useAuth();
const { expenses, loading, error, fetchExpenses } = useExpenses();
const { settlement, calculateSettlement } = useSettlement();

const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);
const filterUser = ref('');
const expandedMemos = ref({});

const currentUserId = computed(() => user.value?.id);

// 前月までかどうかを判定
const isPastMonth = computed(() => {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1;
    
    return selectedYear.value < currentYear || 
           (selectedYear.value === currentYear && selectedMonth.value < currentMonth);
});

// 絞り込み後の支出リスト
const filteredExpenses = computed(() => {
    if (!filterUser.value) {
        return expenses.value;
    }
    return expenses.value.filter(expense => expense.user?.name === filterUser.value);
});

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

const loadData = async () => {
    try {
        await fetchExpenses(selectedYear.value, selectedMonth.value);
        
        // 前月までの場合のみ精算計算を実行
        if (isPastMonth.value) {
            await calculateSettlement(selectedYear.value, selectedMonth.value);
        } else {
            // 現在の月以降の場合は精算結果をクリア
            settlement.value = null;
        }
    } catch (err) {
        console.error('データの読み込みエラー:', err);
    }
};

const applyFilter = () => {
    // フィルターはcomputedで自動的に適用される
};

const editExpense = (id) => {
    router.push(`/expenses/${id}/edit`);
};

const toggleMemo = (id) => {
    expandedMemos.value[id] = !expandedMemos.value[id];
};

const truncateMemo = (memo) => {
    if (!memo) return '';
    const maxLength = 20; // 最初の20文字を表示
    return memo.length > maxLength ? memo.substring(0, maxLength) + '...' : memo;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return `${date.getMonth() + 1}/${date.getDate()}`;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ja-JP').format(amount);
};

onMounted(() => {
    loadData();
});

// ページが表示されるたびにデータを再読み込み（支出登録後のリダイレクトに対応）
onActivated(() => {
    loadData();
});
</script>

