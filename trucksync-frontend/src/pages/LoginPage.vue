<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import EmailField from '@/components/form/EmailField.vue';
import PasswordField from '@/components/form/PasswordField.vue';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const formRef = ref(null);

const form = reactive({
  email: '',
  password: ''
});

const required = label => value =>
  Boolean(String(value ?? '').trim()) || `${label} is required`;

const maxLength = (label, length) => value =>
  String(value ?? '').length <= length ||
  `${label} must be ${length} characters or fewer`;

const emailRules = [
  required('Email'),
  value =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value ?? '')) ||
    'Enter a valid email address',
  maxLength('Email', 255)
];

const passwordRules = [required('Password')];

function getRedirectPath() {
  const redirect = route.query.redirect;

  if (
    typeof redirect === 'string' &&
    redirect.startsWith('/') &&
    !redirect.startsWith('//')
  ) {
    return redirect;
  }

  return '/profile';
}

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  const result = await authStore.login(form.email, form.password);

  if (result) {
    await router.push(getRedirectPath());
  }
}
</script>

<template>
  <q-page class="login-page">
    <div class="shell">
      <section class="intro" aria-labelledby="login-page-title">
        <div class="mark" aria-hidden="true">
          <q-icon name="local_shipping" />
        </div>

        <p class="eyebrow">TruckSync</p>
        <h1 id="login-page-title">Log in</h1>
        <p class="copy">Access your dispatch workspace.</p>
      </section>

      <section class="form-area" aria-label="Log in to your account">
        <q-form ref="formRef" greedy @submit.prevent="handleSubmit">
          <q-card class="login-form" bordered flat>
            <q-card-section class="form-header">
              <h2>Account access</h2>
            </q-card-section>

            <q-card-section class="form-body">
              <EmailField
                v-model="form.email"
                placeholder="jane@example.com"
                :rules="emailRules"
                :maxlength="255"
              />

              <PasswordField
                v-model="form.password"
                label="Password"
                name="password"
                placeholder="Enter your password"
                :rules="passwordRules"
              />
            </q-card-section>

            <q-card-actions class="form-actions">
              <q-btn
                class="login-form-submit"
                type="submit"
                color="primary"
                icon="login"
                label="Log in"
                no-caps
                unelevated
              />
            </q-card-actions>
          </q-card>
        </q-form>
      </section>
    </div>
  </q-page>
</template>
