<script setup>
import { useI18n } from 'vue-i18n';
import AdminUsageReportsTable from '@/components/admin-reports/AdminUsageReportsTable.vue';

const { t } = useI18n();
</script>

<template>
  <q-page class="admin-reports-page q-pa-lg">
    <div class="admin-reports-shell">
      <header class="q-mb-lg">
        <p class="admin-reports-eyebrow text-caption text-weight-bold q-mb-xs">
          {{ t('adminReports.eyebrow') }}
        </p>
        <h1 class="text-h4 text-weight-bold q-my-none">
          {{ t('adminReports.title') }}
        </h1>
        <p class="admin-reports-description q-mt-sm q-mb-none">
          {{ t('adminReports.description') }}
        </p>
      </header>

      <AdminUsageReportsTable />
    </div>
  </q-page>
</template>
