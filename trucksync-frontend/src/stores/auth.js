import { defineStore, acceptHMRUpdate } from 'pinia'

const REGISTER_ENDPOINT = '/api/auth/register'

async function readResponseBody(response) {
  const text = await response.text()

  if (!text) {
    return null
  }

  try {
    return JSON.parse(text)
  } catch (error) {
    console.error('Unable to parse API response.', error)

    return null
  }
}

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
        const response = await fetch(REGISTER_ENDPOINT, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })

        const data = await readResponseBody(response)

        if (!response.ok) {
          this.error = data?.message ?? 'Unable to create account.'

          if (response.status === 422 && data?.errors) {
            this.validationErrors = data.errors
          }

          console.error('Registration failed.', {
            status: response.status,
            data
          })

          return null
        }

        this.user = data?.data?.user ?? null

        return data?.data ?? null
      } catch (error) {
        this.error = 'Unable to reach the registration service.'
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
