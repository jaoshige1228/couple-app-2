# セットアップ手順

## 1. マイグレーションの実行

データベーステーブルを作成します。

```bash
php artisan migrate
```

## 2. シーダーの実行

初期ユーザー（太郎と花子）を作成します。

```bash
php artisan db:seed
```

または、特定のシーダーのみ実行する場合：

```bash
php artisan db:seed --class=DatabaseSeeder
```

### 初期ユーザー情報

- **太郎**: パスワード `123456`
- **花子**: パスワード `123456`

## 3. 環境変数の設定

`.env` ファイルに以下の設定を追加してください（必要に応じて）。

```env
# フロントエンドのURL（Vue3のデフォルトポート）
FRONTEND_URL=http://localhost:5173

# Sanctumのステートフルドメイン
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,localhost:5173,127.0.0.1,127.0.0.1:8000,::1
```

## 4. APIエンドポイント

### 認証

- `POST /api/login` - ログイン
  - リクエストボディ: `{ "name": "太郎", "password": "123456" }`
  - レスポンス: `{ "user": {...}, "token": "..." }`

- `POST /api/logout` - ログアウト（認証必要）
  - ヘッダー: `Authorization: Bearer {token}`

- `GET /api/user` - 現在のユーザー情報取得（認証必要）
  - ヘッダー: `Authorization: Bearer {token}`

### 支出管理

- `GET /api/expenses?year=2024&month=11` - 月ごとの支出一覧（認証必要）
  - ヘッダー: `Authorization: Bearer {token}`
  - クエリパラメータ: `year` (必須), `month` (必須)

- `POST /api/expenses` - 支出の登録（認証必要）
  - ヘッダー: `Authorization: Bearer {token}`
  - リクエストボディ:
    ```json
    {
      "date": "2024-11-01",
      "item": "食費",
      "amount": 5000,
      "is_full_settlement": false
    }
    ```

### 精算計算

- `GET /api/settlement?year=2024&month=11` - 月ごとの精算計算結果（認証必要）
  - ヘッダー: `Authorization: Bearer {token}`
  - クエリパラメータ: `year` (必須), `month` (必須)
  - レスポンス例:
    ```json
    {
      "year": 2024,
      "month": 11,
      "taro": {
        "name": "太郎",
        "total_paid": 5000,
        "normal_amount": 5000,
        "full_settlement_amount": 0
      },
      "hanako": {
        "name": "花子",
        "total_paid": 2000,
        "normal_amount": 2000,
        "full_settlement_amount": 0
      },
      "settlement": {
        "amount": 1500,
        "payer": "花子",
        "payee": "太郎",
        "message": "花子が太郎に1,500円を払えば精算が完了します。"
      }
    }
    ```

## 5. フロントエンド側の設定

Vue3からAPIを呼び出す際は、以下のように設定してください。

### Axiosの設定例

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  withCredentials: true,
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

export default api;
```

### ログイン処理の例

```javascript
import api, { setAuthToken } from './api';

const login = async (name, password) => {
  try {
    const response = await api.post('/login', { name, password });
    setAuthToken(response.data.token);
    return response.data.user;
  } catch (error) {
    console.error('ログインエラー:', error);
    throw error;
  }
};
```

## 6. 開発サーバーの起動

### Laravel（バックエンド）

```bash
php artisan serve
```

または、Laravel Sailを使用する場合：

```bash
./vendor/bin/sail up
```

### Vue3（フロントエンド）

```bash
npm run dev
```

## 注意事項

- パスワードは数字6文字固定です
- ユーザーは「太郎」と「花子」の2人のみです
- 全額精算チェックがオンの場合、その金額は全額を相手が支払う必要があります
- 通常の割り勘の場合、金額の半分を相手が支払う必要があります

