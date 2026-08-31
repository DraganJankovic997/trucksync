<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth.js';

const authStore = useAuthStore();
const router = useRouter();
const { user } = storeToRefs(authStore);

const printedUser = computed(() => JSON.stringify(user.value, null, 2));

onMounted(() => {
  if (!user.value) {
    void authStore.me();
  }
});

async function handleLogout() {
  await authStore.logout();
  await router.push('/login');
}
</script>

<template>
  <q-page class="profile-page">
    <div class="profile-shell">
      <div class="profile-header">
        <div>
          <p class="eyebrow">TruckSync</p>
          <h1>Profile</h1>
        </div>

        <q-btn
          color="primary"
          icon="logout"
          label="Log out"
          no-caps
          outline
          @click="handleLogout"
        />
      </div>

      <q-card class="profile-user-card" bordered flat>
        <q-card-section>
          <pre>{{ printedUser }}</pre>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>
