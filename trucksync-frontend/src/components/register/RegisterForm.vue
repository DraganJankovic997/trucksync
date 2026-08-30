<template>
  <q-form
    ref="formRef"
    class="register-form"
    greedy
    @submit.prevent="handleSubmit"
  >
    <div class="register-form__header">
      <h2>Account details</h2>
    </div>

    <div class="register-form__name-grid">
      <TextField
        v-model="form.firstName"
        label="First name"
        name="first_name"
        autocomplete="given-name"
        icon="badge"
        :rules="nameRules('First name')"
        :error="hasFieldError('first_name')"
        :error-message="fieldError('first_name')"
        @update:model-value="clearServerState"
      />

      <TextField
        v-model="form.lastName"
        label="Last name"
        name="last_name"
        autocomplete="family-name"
        icon="badge"
        :rules="nameRules('Last name')"
        :error="hasFieldError('last_name')"
        :error-message="fieldError('last_name')"
        @update:model-value="clearServerState"
      />
    </div>

    <EmailField
      v-model="form.email"
      :rules="emailRules"
      :error="hasFieldError('email')"
      :error-message="fieldError('email')"
      @update:model-value="clearServerState"
    />

    <PasswordField
      v-model="form.password"
      label="Password"
      name="password"
      autocomplete="new-password"
      :rules="passwordRules"
      :error="hasFieldError('password')"
      :error-message="fieldError('password')"
      @update:model-value="clearServerState"
    />

    <PasswordField
      v-model="form.passwordConfirmation"
      label="Confirm password"
      name="password_confirmation"
      autocomplete="new-password"
      icon="lock_reset"
      :rules="confirmPasswordRules"
      :error="hasFieldError('password_confirmation')"
      :error-message="fieldError('password_confirmation')"
      @update:model-value="clearServerState"
    />

    <q-banner
      v-if="authStore.error"
      class="register-form__banner register-form__banner--error"
      rounded
    >
      {{ authStore.error }}
    </q-banner>

    <q-banner
      v-if="createdUser"
      class="register-form__banner register-form__banner--success"
      rounded
    >
      Account created for {{ createdUser.email }}.
    </q-banner>

    <q-btn
      class="register-form__submit"
      type="submit"
      color="primary"
      icon="person_add"
      label="Create account"
      no-caps
      unelevated
      :disable="authStore.loading"
      :loading="authStore.loading"
    />
  </q-form>
</template>

<script setup>
import { reactive, ref } from 'vue'
import EmailField from '@/components/form/EmailField.vue'
import PasswordField from '@/components/form/PasswordField.vue'
import TextField from '@/components/form/TextField.vue'
import { useAuthStore } from '@/stores/auth.js'

const authStore = useAuthStore()
const formRef = ref(null)
const createdUser = ref(null)

const form = reactive({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  passwordConfirmation: ''
})

const required = label => value =>
  Boolean(String(value ?? '').trim()) || `${label} is required`

const maxLength = (label, length) => value =>
  String(value ?? '').length <= length ||
  `${label} must be ${length} characters or fewer`

const nameRules = label => [required(label), maxLength(label, 255)]

const emailRules = [
  required('Email'),
  value =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value ?? '')) ||
    'Enter a valid email address',
  maxLength('Email', 255)
]

const passwordRules = [
  required('Password'),
  value =>
    String(value ?? '').length >= 8 || 'Password must be at least 8 characters'
]

const confirmPasswordRules = [
  required('Confirm password'),
  value => value === form.password || 'Passwords do not match'
]

function clearServerState() {
  authStore.clearErrors()
  createdUser.value = null
}

function fieldError(field) {
  return authStore.validationErrors[field]?.[0] ?? ''
}

function hasFieldError(field) {
  return Boolean(fieldError(field))
}

async function handleSubmit() {
  const isValid = await formRef.value?.validate()

  if (!isValid) {
    return
  }

  createdUser.value = null

  const result = await authStore.register({
    first_name: form.firstName,
    last_name: form.lastName,
    email: form.email,
    password: form.password,
    password_confirmation: form.passwordConfirmation
  })

  if (result?.user) {
    createdUser.value = result.user
  }
}
</script>
