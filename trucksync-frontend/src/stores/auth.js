import { defineStore } from 'pinia';
import { api } from '@/boot/axios.js';
import { toast } from '@/boot/toast.js';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);

  async function register(payload) {
    try {
      const { data } = await api.post('/auth/register', payload);

      user.value = data?.data?.user ?? null;
      toast.success('User created successfully');

      return data?.data ?? null;
    } catch (requestError) {
      toast.error('User creation failed');

      console.error('Registration request failed.', requestError.response);

      return null;
    }
  }

  return {
    user,
    register
  };
});
