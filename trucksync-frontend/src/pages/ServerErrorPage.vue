<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <q-page class="flex flex-center">
    <h1>{{ t('serverError.title') }}</h1>
  </q-page>
</template>
