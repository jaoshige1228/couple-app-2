<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">夫婦精算アプリ</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">
                            ログイン中: {{ user?.name }}
                        </span>
                        <router-link
                            to="/expenses"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            支出一覧
                        </router-link>
                        <router-link
                            to="/expenses/new"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            支出登録
                        </router-link>
                        <button
                            @click="handleLogout"
                            class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
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

