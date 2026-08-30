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

<template>
  <q-form ref="formRef" greedy @submit.prevent="handleSubmit">
    <q-card class="register-form" bordered flat>
      <q-card-section class="form-header">
        <h2>Account details</h2>
      </q-card-section>

      <q-card-section class="form-body">
        <div class="name-grid">
          <TextField
            v-model="form.firstName"
            label="First name"
            name="first_name"
            placeholder="Jane"
            :rules="nameRules('First name')"
            :maxlength="255"
            @update:model-value="clearServerState"
          />

          <TextField
            v-model="form.lastName"
            label="Last name"
            name="last_name"
            placeholder="Cooper"
            :rules="nameRules('Last name')"
            :maxlength="255"
            @update:model-value="clearServerState"
          />
        </div>

        <EmailField
          v-model="form.email"
          placeholder="jane@example.com"
          :rules="emailRules"
          :maxlength="255"
          @update:model-value="clearServerState"
        />

        <PasswordField
          v-model="form.password"
          label="Password"
          name="password"
          placeholder="Minimum 8 characters"
          :rules="passwordRules"
          @update:model-value="clearServerState"
        />

        <PasswordField
          v-model="form.passwordConfirmation"
          label="Confirm password"
          name="password_confirmation"
          placeholder="Repeat your password"
          :rules="confirmPasswordRules"
          @update:model-value="clearServerState"
        />

        <q-banner
          v-if="authStore.error"
          class="form-banner form-banner-error"
          rounded
        >
          {{ authStore.error }}
        </q-banner>

        <q-banner
          v-if="createdUser"
          class="form-banner form-banner-success"
          rounded
        >
          Account created for {{ createdUser.email }}.
        </q-banner>
      </q-card-section>

      <q-card-actions class="form-actions">
        <q-btn
          class="register-form-submit"
          type="submit"
          color="primary"
          icon="person_add"
          label="Create account"
          no-caps
          unelevated
          :disable="authStore.loading"
          :loading="authStore.loading"
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>
