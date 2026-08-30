import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);

  async function register(payload) {
    try {
      const { data } = await api.post('/auth/register', payload);

      user.value = data?.data?.user ?? null;

      return data?.data ?? null;
    } catch (requestError) {
      const response = requestError.response;
      console.error('Registration request failed.', response);
      return null;
    }
  }

  return {
    user,
    register
  };
});
