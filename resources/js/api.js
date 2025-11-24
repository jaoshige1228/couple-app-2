import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// トークンを保存・取得する関数
export const setAuthToken = (token) => {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        localStorage.setItem('token', token);
    } else {
        delete api.defaults.headers.common['Authorization'];
        localStorage.removeItem('token');
    }
};

// アプリ起動時にトークンを復元
const token = localStorage.getItem('token');
if (token) {
    setAuthToken(token);
}

// リクエストインターセプター（デバッグ用）
api.interceptors.request.use(
    (config) => {
        // デバッグ: リクエストヘッダーを確認
        console.log('API Request:', {
            url: config.url,
            method: config.method,
            hasAuth: !!config.headers.Authorization,
        });
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// レスポンスインターセプター（エラーハンドリング）
api.interceptors.response.use(
    (response) => response,
    (error) => {
        console.error('API Error:', {
            status: error.response?.status,
            url: error.config?.url,
            message: error.response?.data?.message || error.message,
        });
        
        if (error.response?.status === 401) {
            // 認証エラーの場合、トークンを削除してログイン画面へ
            setAuthToken(null);
            localStorage.removeItem('user');
            // ルーターを使用してリダイレクト（window.locationではなく）
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default api;

