import { defineStore, acceptHMRUpdate } from 'pinia'
import { api } from '@/boot/axios.js'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    error: null,
    loading: false,
    user: null,
    validationErrors: {}
  }),

  actions: {
    clearErrors() {
      this.error = null
      this.validationErrors = {}
    },

    async register(payload) {
      this.loading = true
      this.clearErrors()

      try {
        const { data } = await api.post('/auth/register', payload)

        this.user = data?.data?.user ?? null

        return data?.data ?? null
      } catch (error) {
        const response = error.response

        this.error =
          response?.data?.message ?? 'Unable to reach the registration service.'

        if (response?.status === 422 && response.data?.errors) {
          this.validationErrors = response.data.errors
        }

        console.error('Registration request failed.', error)

        return null
      } finally {
        this.loading = false
      }
    }
  }
})

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useAuthStore, import.meta.hot))
}
