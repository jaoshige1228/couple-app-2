<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" style="background: #f9fafb;">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    夫婦精算アプリ
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    ログインしてください
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="name" class="sr-only">ユーザー名</label>
                        <select
                            id="name"
                            v-model="name"
                            required
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        >
                            <option value="">ユーザーを選択</option>
                            <option value="太郎">太郎</option>
                            <option value="花子">花子</option>
                        </select>
                    </div>
                    <div>
                        <label for="password" class="sr-only">パスワード</label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            required
                            maxlength="6"
                            pattern="[0-9]{6}"
                            placeholder="パスワード（数字6文字）"
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        />
                    </div>
                </div>

                <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                {{ errorMessage }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading">ログイン中...</span>
                        <span v-else>ログイン</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

console.log('Login component script setup');

const router = useRouter();
const { login } = useAuth();

const name = ref('');
const password = ref('');
const loading = ref(false);
const errorMessage = ref('');

onMounted(() => {
    console.log('Login component mounted');
});

const handleLogin = async () => {
    loading.value = true;
    errorMessage.value = '';
    
    const result = await login(name.value, password.value);
    
    if (result.success) {
        router.push('/expenses');
    } else {
        errorMessage.value = result.message;
    }
    
    loading.value = false;
};
</script>

