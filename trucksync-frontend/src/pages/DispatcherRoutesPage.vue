<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <q-page
    class="dispatcher-routes-page q-pa-lg"
    :aria-label="t('dispatcherRoutes.title')"
  />
</template>
