<script setup>
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { toast } from '@/boot/toast.js';
import { useDispatcherStore } from '@/stores/dispatcher.js';
import { useRouteStore } from '@/stores/route.js';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const dispatcherStore = useDispatcherStore();
const routeStore = useRouteStore();

const routeId = computed(() => route.params.routeId);
const isRouteAllowed = ref(false);

async function redirectToDashboardWithEditError() {
  toast.error(t('messages.route.editForbidden'));
  await router.replace({ name: 'dashboard' });
}

async function validateRouteOwnership() {
  const currentDispatcher = await dispatcherStore.fetchDispatcher();

  if (!currentDispatcher?.id) {
    await redirectToDashboardWithEditError();
    return;
  }

  const dispatcherRoutes = await routeStore.fetchRoutesForDispatcher(
    currentDispatcher.id
  );

  const ownsRoute = dispatcherRoutes.some(
    route => String(route.id) === String(routeId.value)
  );

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
