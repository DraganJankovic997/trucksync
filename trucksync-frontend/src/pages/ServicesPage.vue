<script setup>
import { onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import ServiceCreateForm from '@/components/services/ServiceCreateForm.vue';
import ServiceDeleteDialog from '@/components/services/ServiceDeleteDialog.vue';
import ServiceTable from '@/components/services/ServiceTable.vue';
import { useServiceStore } from '@/stores/service.js';

const { t } = useI18n();
const serviceStore = useServiceStore();
const { services } = storeToRefs(serviceStore);

const serviceName = ref('');
const isFetching = ref(false);
const isCreating = ref(false);
const isDeleting = ref(false);
const deleteDialogOpen = ref(false);
const serviceToDelete = ref(null);
const deletingServiceId = ref(null);

async function loadServices() {
  isFetching.value = true;

  try {
    await serviceStore.fetchServices();
  } finally {
    isFetching.value = false;
  }
}

onMounted(() => {
  void loadServices();
});

async function handleCreate(name) {
  isCreating.value = true;

  try {
    const createdService = await serviceStore.createService(name);

    if (createdService) {
      serviceName.value = '';
    }
  } finally {
    isCreating.value = false;
  }
}

function requestDelete(service) {
  serviceToDelete.value = service;
  deleteDialogOpen.value = true;
}

async function handleDelete() {
  if (
    serviceToDelete.value?.id === undefined ||
    serviceToDelete.value?.id === null
  ) {
    return;
  }

  isDeleting.value = true;
  deletingServiceId.value = serviceToDelete.value.id;

  try {
    const wasDeleted = await serviceStore.deleteService(
      serviceToDelete.value.id
    );

    if (wasDeleted) {
      deleteDialogOpen.value = false;
      serviceToDelete.value = null;
    }
  } finally {
    isDeleting.value = false;
    deletingServiceId.value = null;
  }
}
</script>

<template>
  <q-page class="services-page q-pa-lg">
    <div class="services-shell">
      <header class="row items-start justify-between q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md">
          <p class="services-eyebrow text-caption text-weight-bold q-mb-xs">
            {{ t('services.eyebrow') }}
          </p>
          <h1 class="text-h4 text-weight-bold q-my-none">
            {{ t('services.title') }}
          </h1>
          <p class="services-description q-mt-sm q-mb-none">
            {{ t('services.description') }}
          </p>
        </div>

        <div class="col-12 col-md-auto">
          <div class="row items-center">
            <q-btn
              color="primary"
              icon="refresh"
              outline
              no-caps
              class="text-weight-bold"
              :label="t('services.actions.refresh')"
              :loading="isFetching"
              @click="loadServices"
            />
          </div>
        </div>
      </header>

      <div class="row q-col-gutter-lg items-start">
        <div class="col-12 col-md-4">
          <ServiceCreateForm
            v-model="serviceName"
            :loading="isCreating"
            @create="handleCreate"
          />
        </div>

        <div class="col-12 col-md-8">
          <ServiceTable
            :services="services"
            :loading="isFetching"
            :deleting-id="deletingServiceId"
            @delete="requestDelete"
          />
        </div>
      </div>

      <ServiceDeleteDialog
        v-model="deleteDialogOpen"
        :service="serviceToDelete"
        :loading="isDeleting"
        @confirm="handleDelete"
      />
    </div>
  </q-page>
</template>
