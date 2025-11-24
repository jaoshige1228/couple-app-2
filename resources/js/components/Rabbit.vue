<template>
    <div class="flex flex-col items-center justify-center py-8">
        <div class="text-center">
            <div class="text-6xl mb-4" :style="{ transform: `scale(${growthScale})` }">
                {{ rabbitEmoji }}
            </div>
            <p class="text-lg font-semibold text-gray-700 mb-2">
                {{ rabbitName }}
            </p>
            <p class="text-sm text-gray-500">
                登録数: {{ totalExpenses }}件
            </p>
            <div class="mt-4 w-64 bg-gray-200 rounded-full h-2.5">
                <div 
                    class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500"
                    :style="{ width: `${growthPercentage}%` }"
                ></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                次の成長まで: {{ nextLevelExpenses - totalExpenses }}件
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    totalExpenses: {
        type: Number,
        required: true,
        default: 0,
    },
});

// 成長レベルに応じた設定
const growthLevels = [
    { min: 0, max: 4, emoji: '🐰', name: '小さなウサギ', scale: 0.8 },
    { min: 5, max: 9, emoji: '🐇', name: '成長中のウサギ', scale: 1.0 },
    { min: 10, max: 19, emoji: '🐰', name: '元気なウサギ', scale: 1.2 },
    { min: 20, max: 39, emoji: '🐇', name: '立派なウサギ', scale: 1.4 },
    { min: 40, max: 99, emoji: '🐰', name: 'マスターウサギ', scale: 1.6 },
    { min: 100, max: Infinity, emoji: '🐇', name: '伝説のウサギ', scale: 1.8 },
];

// 現在のレベルを計算
const currentLevel = computed(() => {
    for (let i = growthLevels.length - 1; i >= 0; i--) {
        if (props.totalExpenses >= growthLevels[i].min) {
            return growthLevels[i];
        }
    }
    return growthLevels[0];
});

// 次のレベルを計算
const nextLevel = computed(() => {
    const currentIndex = growthLevels.findIndex(level => level === currentLevel.value);
    if (currentIndex < growthLevels.length - 1) {
        return growthLevels[currentIndex + 1];
    }
    return null;
});

const rabbitEmoji = computed(() => currentLevel.value.emoji);
const rabbitName = computed(() => currentLevel.value.name);
const growthScale = computed(() => currentLevel.value.scale);

const nextLevelExpenses = computed(() => {
    if (nextLevel.value) {
        return nextLevel.value.min;
    }
    return currentLevel.value.max + 1;
});

// 成長パーセンテージを計算（現在のレベル内での進捗）
const growthPercentage = computed(() => {
    const level = currentLevel.value;
    const range = level.max - level.min + 1;
    const progress = props.totalExpenses - level.min;
    const percentage = Math.min(100, (progress / range) * 100);
    return Math.max(0, percentage);
});
</script>

