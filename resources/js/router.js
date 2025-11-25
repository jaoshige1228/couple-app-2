import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from './composables/useAuth';
import Login from './components/Login.vue';
import Layout from './components/Layout.vue';
import ExpenseList from './components/ExpenseList.vue';
import ExpenseForm from './components/ExpenseForm.vue';
import ExpenseDetail from './components/ExpenseDetail.vue';
import ExpenseHistory from './components/ExpenseHistory.vue';
import HouseholdBook from './components/HouseholdBook.vue';

console.log('Router.js loaded, components imported');

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { requiresAuth: false }
    },
    {
        path: '/',
        component: Layout,
        meta: { requiresAuth: true },
        redirect: '/expenses',
        children: [
            {
                path: 'expenses',
                name: 'ExpenseList',
                component: ExpenseList,
            },
            {
                path: 'expenses/new',
                name: 'ExpenseForm',
                component: ExpenseForm,
            },
            {
                path: 'expenses/history',
                name: 'ExpenseHistory',
                component: ExpenseHistory,
            },
            {
                path: 'expenses/:id/edit',
                name: 'ExpenseEdit',
                component: ExpenseForm,
            },
            {
                path: 'expenses/:id',
                name: 'ExpenseDetail',
                component: ExpenseDetail,
            },
            {
                path: 'household-book',
                name: 'HouseholdBook',
                component: HouseholdBook,
            },
        ]
    },
    // 404ページ（デバッグ用）
    {
        path: '/:pathMatch(.*)*',
        redirect: '/login'
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// ナビゲーションガード
router.beforeEach((to, from, next) => {
    const { isAuthenticated } = useAuth();
    
    console.log('Navigation guard:', {
        to: to.path,
        from: from.path,
        requiresAuth: to.meta.requiresAuth,
        isAuthenticated: isAuthenticated.value,
        component: to.component?.name || to.component,
    });
    
    if (to.meta.requiresAuth && !isAuthenticated.value) {
        console.log('Redirecting to login');
        next('/login');
    } else if (to.path === '/login' && isAuthenticated.value) {
        console.log('Redirecting to expenses');
        next('/expenses');
    } else {
        console.log('Allowing navigation to:', to.path);
        next();
    }
});

// ルーターのエラーハンドリング
router.onError((error) => {
    console.error('Router error:', error);
});

export default router;

