<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const { t } = useI18n();

const printedUser = computed(() => JSON.stringify(user.value, null, 2));

onMounted(() => {
  if (!user.value) {
    void authStore.me();
  }
});
</script>

<template>
  <q-page class="profile-page">
    <div class="profile-shell">
      <div class="profile-header">
        <div>
          <p class="eyebrow">{{ t('layout.brand') }}</p>
          <h1>{{ t('profile.title') }}</h1>
        </div>
      </div>

      <q-card class="profile-user-card" bordered flat>
        <q-card-section>
          <pre>{{ printedUser }}</pre>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
