<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
  <q-page class="flex flex-center">
    <div class="column items-center">
      <img
        :alt="t('dashboard.logoAlt')"
        src="~@/assets/quasar-logo-vertical.svg"
        style="width: 200px; height: 200px"
      />

      <h1 class="text-h4 q-mt-md">{{ t('dashboard.title') }}</h1>

      <q-btn
        class="q-mt-md"
        color="primary"
        to="/second"
        :label="t('dashboard.secondPageCta')"
        no-caps
      />
    </div>
  </q-page>
</template>
