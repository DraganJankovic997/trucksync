<script setup>
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import LanguageSwitcher from '@/components/layout/LanguageSwitcher.vue';
import { useAuthStore } from '@/stores/auth.js';

const emit = defineEmits(['toggle-left-drawer']);
const authStore = useAuthStore();
const router = useRouter();
const { t } = useI18n();

async function handleLogout() {
  const didLogout = await authStore.logout();

  if (didLogout) {
    await router.push('/login');
  }
}
</script>

<template>
  <q-header elevated>
    <q-toolbar>
      <q-btn
        flat
        dense
        round
        icon="menu"
        :aria-label="t('layout.menu')"
        @click="emit('toggle-left-drawer')"
      />

      <q-toolbar-title> {{ t('layout.brand') }} </q-toolbar-title>

      <LanguageSwitcher />
      <div>
        <q-btn
          icon="logout"
          :label="t('layout.logout')"
          no-caps
          dark
          outline
          @click="handleLogout"
        />
      </div>
    </q-toolbar>
  </q-header>
</template>
