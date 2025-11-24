import { ref, computed } from 'vue';
import api, { setAuthToken } from '../api';

const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));
const isAuthenticated = computed(() => user.value !== null);

export function useAuth() {
    const login = async (name, password) => {
        try {
            const response = await api.post('/login', { name, password });
            const { user: userData, token } = response.data;
            
            user.value = userData;
            setAuthToken(token);
            localStorage.setItem('user', JSON.stringify(userData));
            
            return { success: true, user: userData };
        } catch (error) {
            const message = error.response?.data?.message || 'ログインに失敗しました。';
            return { success: false, message };
        }
    };

    const logout = async () => {
        try {
            await api.post('/logout');
        } catch (error) {
            console.error('ログアウトエラー:', error);
        } finally {
            user.value = null;
            setAuthToken(null);
            localStorage.removeItem('user');
        }
    };

    const fetchUser = async () => {
        try {
            const response = await api.get('/user');
            user.value = response.data;
            localStorage.setItem('user', JSON.stringify(response.data));
            return response.data;
        } catch (error) {
            console.error('ユーザー情報取得エラー:', error);
            return null;
        }
    };

    return {
        user,
        isAuthenticated,
        login,
        logout,
        fetchUser,
    };
}

