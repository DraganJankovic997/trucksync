<script setup>
import { useI18n } from 'vue-i18n';
import RegisterForm from '@/components/register/RegisterForm.vue';

const { t } = useI18n();
</script>

<template>
  <q-page class="register-page">
    <div class="shell">
      <section class="intro" aria-labelledby="register-page-title">
        <div class="mark" aria-hidden="true">
          <q-icon name="local_shipping" />
        </div>

        <p class="eyebrow">{{ t('layout.brand') }}</p>
        <h1 id="register-page-title">{{ t('register.title') }}</h1>
        <p class="copy">{{ t('register.description') }}</p>
      </section>

      <section class="form-area" :aria-label="t('register.formAriaLabel')">
        <RegisterForm />
      </section>
    </div>
  </q-page>
</template>
