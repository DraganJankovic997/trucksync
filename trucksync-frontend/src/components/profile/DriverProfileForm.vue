<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <q-card class="profile-type-form" bordered flat>
    <q-card-section class="form-header">
      <h2>{{ t('profile.typeForms.driver.title') }}</h2>
    </q-card-section>
  </q-card>
</template>
