import { ref } from 'vue';
import api from '../api';

export function useSettlement() {
    const settlement = ref(null);
    const loading = ref(false);
    const error = ref(null);

    const calculateSettlement = async (year, month) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await api.get('/settlement', {
                params: { year, month }
            });
            settlement.value = response.data;
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || '精算計算に失敗しました。';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        settlement,
        loading,
        error,
        calculateSettlement,
    };
}

