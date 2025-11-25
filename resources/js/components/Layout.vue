<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center min-w-0">
                        <h1 class="text-base sm:text-xl font-bold text-gray-900 whitespace-nowrap truncate">夫婦精算アプリ</h1>
                    </div>
                    <div class="flex items-center space-x-1 sm:space-x-4 flex-shrink-0">
                        <span class="hidden sm:inline text-xs sm:text-sm text-gray-700">
                            ログイン中: {{ user?.name }}
                        </span>
                        <router-link
                            to="/expenses"
                            class="px-2 sm:px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors"
                            :class="$route.path.startsWith('/expenses') && !$route.path.includes('/new') && !$route.path.includes('/edit') && !$route.path.includes('/history') 
                                ? 'bg-indigo-100 text-indigo-700 font-semibold' 
                                : 'text-gray-700 hover:text-gray-900'"
                        >
                            支出一覧
                        </router-link>
                        <router-link
                            to="/expenses/new"
                            class="px-2 sm:px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors"
                            :class="$route.path === '/expenses/new' 
                                ? 'bg-indigo-100 text-indigo-700 font-semibold' 
                                : 'text-gray-700 hover:text-gray-900'"
                        >
                            支出登録
                        </router-link>
                        <router-link
                            to="/household-book"
                            class="px-2 sm:px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors"
                            :class="$route.path === '/household-book' 
                                ? 'bg-indigo-100 text-indigo-700 font-semibold' 
                                : 'text-gray-700 hover:text-gray-900'"
                        >
                            家計簿
                        </router-link>
                        <router-link
                            to="/expenses/history"
                            class="px-2 sm:px-3 py-2 rounded-md text-xs sm:text-sm font-medium transition-colors"
                            :class="$route.path === '/expenses/history' 
                                ? 'bg-indigo-100 text-indigo-700 font-semibold' 
                                : 'text-gray-700 hover:text-gray-900'"
                        >
                            履歴
                        </router-link>
                        <button
                            @click="handleLogout"
                            class="text-gray-700 hover:text-gray-900 px-2 sm:px-3 py-2 rounded-md text-xs sm:text-sm font-medium"
                        >
                            ログアウト
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { user, logout } = useAuth();

const handleLogout = async () => {
    await logout();
    router.push('/login');
};
</script>

