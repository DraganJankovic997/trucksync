<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from '@/boot/toast.js';
import { useAuthStore } from '@/stores/auth.js';
import { useDispatcherStore } from '@/stores/dispatcher.js';
import { useRouteStore } from '@/stores/route.js';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const dispatcherStore = useDispatcherStore();
const routeStore = useRouteStore();
const { user } = storeToRefs(authStore);

const routeId = computed(() => route.params.routeId);
const isRouteAllowed = ref(false);

async function redirectToDashboardWithEditError() {
  await router.replace({ name: 'dashboard' });
  toast.error(t('messages.route.editForbidden'));
}

async function validateRouteOwnership() {
  if (user.value?.profile_type !== 'dispatcher') {
    await redirectToDashboardWithEditError();
    return;
  }

  const currentDispatcher = await dispatcherStore.fetchDispatcher();

  if (!currentDispatcher?.id) {
    await redirectToDashboardWithEditError();
    return;
  }

  const currentRoute = await routeStore.fetchRoute(routeId.value);
  const ownsRoute =
    String(currentRoute?.dispatcher_id) === String(currentDispatcher.id);

  if (!ownsRoute) {
    await redirectToDashboardWithEditError();
    return;
  }

  isRouteAllowed.value = true;
}

onMounted(() => {
  void validateRouteOwnership();
});
</script>

<template>
  <q-page
    v-if="isRouteAllowed"
    class="dispatcher-route-edit-page q-pa-lg"
    :aria-label="t('dispatcherRouteEdit.title', { route_id: routeId })"
  >
    <div class="dispatcher-route-edit-shell">
      <header class="dispatcher-route-edit-header">
        <h1 class="text-h4 text-weight-bold q-my-none">
          {{ t('dispatcherRouteEdit.title', { route_id: routeId }) }}
        </h1>
      </header>
    </div>
  </q-page>
</template>
