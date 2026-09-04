<script setup>
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import DispatcherProfileForm from '@/components/profile/DispatcherProfileForm.vue';
import DriverProfileForm from '@/components/profile/DriverProfileForm.vue';
import ProfileForm from '@/components/profile/ProfileForm.vue';
import RestStopProfileForm from '@/components/profile/RestStopProfileForm.vue';
import { useAuthStore } from '@/stores/auth.js';

const { t } = useI18n();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const profileTypeForms = {
  driver: DriverProfileForm,
  dispatcher: DispatcherProfileForm,
  rest_stop: RestStopProfileForm
};

const profileTypeForm = computed(
  () => profileTypeForms[user.value?.profile_type] ?? null
);
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

      <div class="profile-content">
        <ProfileForm />

        <component :is="profileTypeForm" v-if="profileTypeForm" />
      </div>
    </div>
  </q-page>
</template>
