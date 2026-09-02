<script setup>
import { useI18n } from 'vue-i18n';
import ProfileForm from '@/components/profile/ProfileForm.vue';

const { t } = useI18n();
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

      <ProfileForm />
    </div>
  </q-page>
</template>
