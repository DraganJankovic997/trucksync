<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import ProfileCompletionWarning from '@/components/profile/ProfileCompletionWarning.vue';
import RestStopServicePicker from '@/components/services/RestStopServicePicker.vue';
import RestStopServiceRemoveDialog from '@/components/services/RestStopServiceRemoveDialog.vue';
import RestStopServiceTable from '@/components/services/RestStopServiceTable.vue';
import { useAuthStore } from '@/stores/auth.js';
import { useRestStopStore } from '@/stores/rest-stop.js';
import { useRestStopServiceStore } from '@/stores/rest-stop-service.js';
import { useServiceStore } from '@/stores/service.js';

const { t } = useI18n();
const authStore = useAuthStore();
const serviceStore = useServiceStore();
const restStopStore = useRestStopStore();
const restStopServiceStore = useRestStopServiceStore();

const { user } = storeToRefs(authStore);
const { services: catalogServices } = storeToRefs(serviceStore);
const { services: restStopServices } = storeToRefs(restStopServiceStore);

const currentRestStop = ref(null);
const selectedServiceId = ref(null);
const isFetching = ref(false);
const isAdding = ref(false);
const isRemoving = ref(false);
const removeDialogOpen = ref(false);
const serviceToRemove = ref(null);
const removingServiceId = ref(null);

const currentRestStopId = computed(() => currentRestStop.value?.id ?? null);

const hasRestStopProfile = computed(() => Boolean(currentRestStopId.value));

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
    const [, fetchedRestStop] = await Promise.all([
      serviceStore.fetchServices(),
      restStopStore.fetchRestStop()
    ]);

    currentRestStop.value = fetchedRestStop ?? null;

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

      <ProfileCompletionWarning
        class="q-mb-lg"
        :user="user"
        :profile-record="currentRestStop"
        :loading="isFetching"
      />

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
