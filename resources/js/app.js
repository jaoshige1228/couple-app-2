import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { RouterView } from 'vue-router';
import router from './router';
import { useAuth } from './composables/useAuth';

console.log('App.js loaded');

// ルートコンポーネント
const App = {
    setup() {
        console.log('App component setup');
        return () => {
            console.log('App render, rendering RouterView');
            return h(RouterView);
        };
    }
};

const app = createApp(App);
console.log('Vue app created');

// ルーターを登録
app.use(router);
console.log('Router registered');

// アプリケーションをマウント
app.mount('#app');
console.log('App mounted to #app');

// ルーターの準備が完了したことを確認
router.isReady().then(() => {
    console.log('Router is ready, current route:', router.currentRoute.value.path);
}).catch((error) => {
    console.error('Router error:', error);
});

// アプリ起動時にユーザー情報を取得（トークンがある場合のみ）
const token = localStorage.getItem('token');
console.log('Token from localStorage:', token ? 'exists' : 'not found');
if (token) {
    const { fetchUser } = useAuth();
    fetchUser().catch(() => {
        // エラーが発生した場合は無視（未認証の可能性）
        console.log('ユーザー情報の取得に失敗しました。ログインが必要です。');
    });
}
