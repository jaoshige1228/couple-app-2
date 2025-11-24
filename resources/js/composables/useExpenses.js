import { ref } from 'vue';
import api from '../api';

export function useExpenses() {
    const expenses = ref([]);
    const loading = ref(false);
    const error = ref(null);

    const fetchExpenses = async (year, month) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get('/expenses', {
                params: { year, month }
            });
            expenses.value = response.data;
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || '支出一覧の取得に失敗しました。';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const createExpense = async (expenseData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.post('/expenses', expenseData);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || '支出の登録に失敗しました。';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const updateExpense = async (id, expenseData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.put(`/expenses/${id}`, expenseData);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || '支出の更新に失敗しました。';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteExpense = async (id) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.delete(`/expenses/${id}`);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || '支出の削除に失敗しました。';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        expenses,
        loading,
        error,
        fetchExpenses,
        createExpense,
        updateExpense,
        deleteExpense,
    };
}

