<template>
    <div class="max-w-6xl mx-auto p-2 sm:p-6">
        <div class="bg-white shadow-md rounded-lg p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
                <h2 class="text-lg sm:text-2xl font-bold text-gray-800 whitespace-nowrap">履歴</h2>
                <div class="flex flex-wrap gap-2 sm:space-x-4">
                    <select
                        v-model="selectedYear"
                        @change="loadData"
                        class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">すべて</option>
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}年
                        </option>
                    </select>
                    <select
                        v-model="selectedMonth"
                        @change="loadData"
                        class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        :disabled="!selectedYear"
                    >
                        <option value="">すべて</option>
                        <option v-for="month in 12" :key="month" :value="month">
                            {{ month }}月
                        </option>
                    </select>
                    <select
                        v-model="filterAction"
                        @change="applyFilter"
                        class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">すべて</option>
                        <option value="登録">登録</option>
                        <option value="編集">編集</option>
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
                <div v-if="filteredHistory.length === 0" class="text-center py-8 text-gray-500">
                    履歴がありません。
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="item in filteredHistory"
                        :key="`${item.id}-${item.updated_at}`"
                        @click="viewDetail(item.id)"
                        class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer border-l-4"
                        :class="item.action_type === '登録' ? 'border-green-500' : 'border-blue-500'"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span 
                                        class="px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="item.action_type === '登録' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'"
                                    >
                                        {{ item.action_type }}
                                    </span>
                                    <span class="text-xs sm:text-sm text-gray-500">
                                        {{ formatDateTime(item.action_type === '登録' ? item.created_at : item.updated_at) }}
                                    </span>
                                </div>
                                <div class="text-sm sm:text-base font-medium text-gray-900">
                                    {{ formatDate(item.date) }} - {{ item.item }}
                                </div>
                                <div class="text-xs sm:text-sm text-gray-600 mt-1">
                                    {{ item.user?.name }} / {{ formatCurrency(item.amount) }}円
                                </div>
                            </div>
                            <div class="text-right">
                                <button
                                    @click.stop="viewDetail(item.id)"
                                    class="text-indigo-600 hover:text-indigo-900 text-xs sm:text-sm font-medium"
                                >
                                    詳細を見る →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();

const history = ref([]);
const selectedYear = ref('');
const selectedMonth = ref('');
const filterAction = ref('');
const loading = ref(false);
const error = ref('');

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

// フィルタリング後の履歴
const filteredHistory = computed(() => {
    let result = history.value;
    
    if (filterAction.value) {
        result = result.filter(item => item.action_type === filterAction.value);
    }
    
    return result;
});

const loadData = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const params = {};
        if (selectedYear.value) {
            params.year = selectedYear.value;
        }
        if (selectedMonth.value) {
            params.month = selectedMonth.value;
        }
        
        const response = await api.get('/expenses/history', { params });
        history.value = response.data;
    } catch (err) {
        console.error('履歴データの読み込みエラー:', err);
        error.value = 'データの読み込みに失敗しました。';
        history.value = [];
    } finally {
        loading.value = false;
    }
};

const applyFilter = () => {
    // フィルターはcomputedで自動的に適用される
};

const viewDetail = (id) => {
    router.push(`/expenses/${id}`);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日`;
};

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return `${date.getFullYear()}/${date.getMonth() + 1}/${date.getDate()} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ja-JP').format(amount);
};

onMounted(() => {
    loadData();
});
</script>

