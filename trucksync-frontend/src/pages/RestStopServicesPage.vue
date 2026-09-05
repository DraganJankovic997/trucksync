<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import RestStopServicePicker from '@/components/services/RestStopServicePicker.vue';
import RestStopServiceRemoveDialog from '@/components/services/RestStopServiceRemoveDialog.vue';
import RestStopServiceTable from '@/components/services/RestStopServiceTable.vue';
import { useRestStopStore } from '@/stores/rest-stop.js';
import { useRestStopServiceStore } from '@/stores/rest-stop-service.js';
import { useServiceStore } from '@/stores/service.js';

const { t } = useI18n();
const serviceStore = useServiceStore();
const restStopStore = useRestStopStore();
const restStopServiceStore = useRestStopServiceStore();

const { services: catalogServices } = storeToRefs(serviceStore);
const { services: restStopServices } = storeToRefs(restStopServiceStore);

const currentRestStopId = ref(null);
const selectedServiceId = ref(null);
const isFetching = ref(false);
const isAdding = ref(false);
const isRemoving = ref(false);
const removeDialogOpen = ref(false);
const serviceToRemove = ref(null);
const removingServiceId = ref(null);

const hasRestStopProfile = computed(() => currentRestStopId.value !== null);

const selectedServiceIds = computed(
  () => new Set(restStopServices.value.map(service => String(service.id)))
);

const availableServices = computed(() =>
  catalogServices.value.filter(
    service => !selectedServiceIds.value.has(String(service.id))
  )
);

async function loadServices() {
  isFetching.value = true;

  try {
    const [, currentRestStop] = await Promise.all([
      serviceStore.fetchServices(),
      restStopStore.fetchRestStop()
    ]);

    currentRestStopId.value = currentRestStop?.id ?? null;

    if (currentRestStopId.value) {
      await restStopServiceStore.fetchRestStopServices(currentRestStopId.value);
    } else {
      selectedServiceId.value = null;
      restStopServiceStore.clearRestStopServices();
    }
  } finally {
    isFetching.value = false;
  }
}

onMounted(() => {
  void loadServices();
});

async function handleAdd(serviceId) {
  if (!hasRestStopProfile.value) {
    return;
  }

  isAdding.value = true;

  try {
    const addedService =
      await restStopServiceStore.addRestStopService(serviceId);

    if (addedService) {
      selectedServiceId.value = null;
    }
  } finally {
    isAdding.value = false;
  }
}

function requestRemove(service) {
  serviceToRemove.value = service;
  removeDialogOpen.value = true;
}

async function handleRemove() {
  if (
    serviceToRemove.value?.id === undefined ||
    serviceToRemove.value?.id === null
  ) {
    return;
  }

  isRemoving.value = true;
  removingServiceId.value = serviceToRemove.value.id;

  try {
    const removedService = await restStopServiceStore.removeRestStopService(
      serviceToRemove.value.id
    );

    if (removedService) {
      removeDialogOpen.value = false;
      serviceToRemove.value = null;
    }
  } finally {
    isRemoving.value = false;
    removingServiceId.value = null;
  }
}
</script>

<template>
  <q-page class="services-page q-pa-lg">
    <div class="services-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p class="services-eyebrow text-caption text-weight-bold q-mb-xs">
            {{ t('restStopServices.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('restStopServices.title') }}
          </h1>
          <p class="services-description q-mt-sm q-mb-none">
            {{ t('restStopServices.description') }}
          </p>
        </div>

        <div class="col-12 col-md-auto">
          <q-btn
            color="primary"
            icon="refresh"
            outline
            no-caps
            class="text-weight-bold"
            :label="t('restStopServices.actions.refresh')"
            :loading="isFetching"
            @click="loadServices"
          />
        </div>
      </header>

      <q-banner
        v-if="!isFetching && !hasRestStopProfile"
        rounded
        class="bg-warning text-dark q-mb-lg"
      >
        <template #avatar>
          <q-icon name="warning" />
        </template>

        <div class="text-weight-bold">
          {{ t('restStopServices.profileMissing.title') }}
        </div>
        <div>
          {{ t('restStopServices.profileMissing.description') }}
        </div>

        <template #action>
          <q-btn
            flat
            no-caps
            :to="{ name: 'profile' }"
            :label="t('restStopServices.actions.completeProfile')"
          />
        </template>
      </q-banner>

      <div class="row q-col-gutter-lg items-start">
        <div class="col-12 col-md-4">
          <RestStopServicePicker
            v-model="selectedServiceId"
            :services="availableServices"
            :loading="isAdding"
            :options-loading="isFetching"
            :disable="!hasRestStopProfile"
            @add="handleAdd"
          />
        </div>

        <div class="col-12 col-md-8">
          <RestStopServiceTable
            :services="restStopServices"
            :loading="isFetching"
            :removing-id="removingServiceId"
            @remove="requestRemove"
          />
        </div>
      </div>

      <RestStopServiceRemoveDialog
        v-model="removeDialogOpen"
        :service="serviceToRemove"
        :loading="isRemoving"
        @confirm="handleRemove"
      />
    </div>
  </q-page>
</template>
