<script setup>
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import EmailField from '@/components/form/EmailField.vue';
import PasswordField from '@/components/form/PasswordField.vue';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const formRef = ref(null);
const { t } = useI18n();

const form = reactive({
  email: '',
  password: ''
});

const required = fieldKey => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t(fieldKey) });

const maxLength = (fieldKey, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', { field: t(fieldKey), length: length });

const emailRules = [
  required('validation.fields.email'),
  value =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value ?? '')) ||
    t('validation.email'),
  maxLength('validation.fields.email', 255)
];

const passwordRules = [required('validation.fields.password')];

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

        <p class="eyebrow">{{ t('layout.brand') }}</p>
        <h1 id="login-page-title">{{ t('login.title') }}</h1>
        <p class="copy">{{ t('login.description') }}</p>
      </section>

      <section class="form-area" :aria-label="t('login.formAriaLabel')">
        <q-form ref="formRef" greedy @submit.prevent="handleSubmit">
          <q-card class="login-form" bordered flat>
            <q-card-section class="form-header">
              <h2>{{ t('login.form.title') }}</h2>
            </q-card-section>

            <q-card-section class="form-body">
              <EmailField
                v-model="form.email"
                :label="t('login.fields.email.label')"
                :placeholder="t('login.fields.email.placeholder')"
                :rules="emailRules"
                :maxlength="255"
              />

              <PasswordField
                v-model="form.password"
                :label="t('login.fields.password.label')"
                name="password"
                :placeholder="t('login.fields.password.placeholder')"
                :rules="passwordRules"
              />
            </q-card-section>

            <q-card-actions class="form-actions">
              <q-btn
                class="login-form-submit"
                type="submit"
                color="primary"
                icon="login"
                :label="t('login.submit')"
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
