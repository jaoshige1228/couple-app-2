<template>
    <div class="max-w-4xl mx-auto p-2 sm:p-6">
        <div class="bg-white shadow-md rounded-lg p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
                <h2 class="text-lg sm:text-2xl font-bold text-gray-800 whitespace-nowrap">家計簿</h2>
                <div class="flex flex-wrap gap-2 sm:space-x-4">
                    <select
                        v-model="selectedYear"
                        @change="loadData"
                        class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}年
                        </option>
                    </select>
                    <select
                        v-model="selectedMonth"
                        @change="loadData"
                        class="px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option v-for="month in 12" :key="month" :value="month">
                            {{ month }}月
                        </option>
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
                <div v-if="!summary || summary.length === 0" class="text-center py-8 text-gray-500">
                    {{ selectedYear }}年{{ selectedMonth }}月の支出はありません。
                </div>

                <div v-else>
                    <!-- 項目別の支出一覧 -->
                    <div class="space-y-3 mb-6">
                        <div
                            v-for="item in summary"
                            :key="item.item"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex-1">
                                <h3 class="text-sm sm:text-base font-semibold text-gray-800">{{ item.item }}</h3>
                            </div>
                            <div class="text-right">
                                <p class="text-sm sm:text-lg font-bold text-gray-900">
                                    {{ formatCurrency(item.total) }}円
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ ((item.total / total) * 100).toFixed(1) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 合計金額 -->
                    <div class="border-t-2 border-gray-300 pt-4 mt-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-800">合計</h3>
                            <p class="text-xl sm:text-2xl font-bold text-indigo-600">
                                {{ formatCurrency(total) }}円
                            </p>
                        </div>
                    </div>

                    <!-- グラフ表示（オプション） -->
                    <div class="mt-6">
                        <h3 class="text-sm sm:text-base font-semibold text-gray-700 mb-3">項目別の割合</h3>
                        <div class="space-y-2">
                            <div
                                v-for="item in summary"
                                :key="item.item"
                                class="flex items-center"
                            >
                                <div class="w-20 sm:w-24 text-xs sm:text-sm text-gray-700 truncate">
                                    {{ item.item }}
                                </div>
                                <div class="flex-1 mx-2">
                                    <div class="w-full bg-gray-200 rounded-full h-4 sm:h-5">
                                        <div
                                            class="bg-indigo-600 h-4 sm:h-5 rounded-full flex items-center justify-end pr-2"
                                            :style="{ width: `${(item.total / total) * 100}%` }"
                                        >
                                            <span v-if="(item.total / total) * 100 > 10" class="text-xs text-white font-semibold">
                                                {{ ((item.total / total) * 100).toFixed(0) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-20 sm:w-24 text-right text-xs sm:text-sm font-medium text-gray-900">
                                    {{ formatCurrency(item.total) }}円
                                </div>
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
import api from '../api';

const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(new Date().getMonth() + 1);
const summary = ref([]);
const total = ref(0);
const loading = ref(false);
const error = ref('');

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

const loadData = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        const response = await api.get('/expenses/household-book', {
            params: {
                year: selectedYear.value,
                month: selectedMonth.value,
            },
        });
        
        summary.value = response.data.summary;
        total.value = response.data.total || 0;
    } catch (err) {
        console.error('家計簿データの読み込みエラー:', err);
        error.value = 'データの読み込みに失敗しました。';
        summary.value = [];
        total.value = 0;
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('ja-JP').format(amount);
};

onMounted(() => {
    loadData();
});
</script>

