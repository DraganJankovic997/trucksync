<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  services: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  removingId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits({
  remove: service => service?.id !== undefined && service?.id !== null
});

const { t } = useI18n();
const tablePagination = { rowsPerPage: 0 };

const columns = computed(() => [
  {
    name: 'name',
    label: t('restStopServices.table.name'),
    field: 'name',
    align: 'left',
    sortable: true
  },
  {
    name: 'actions',
    label: t('restStopServices.table.actions'),
    field: 'actions',
    align: 'right'
  }
]);

const rows = computed(() =>
  props.services.map(service => ({
    id: service.id,
    name: service.name ?? ''
  }))
);

const serviceCount = computed(() => props.services.length);

function isRemoving(service) {
  return String(props.removingId) === String(service.id);
}

function hasPendingRemove() {
  return props.removingId !== undefined && props.removingId !== null;
}
</script>

<template>
  <q-card class="service-card service-table-card" bordered flat>
    <q-card-section
      class="row items-center justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('restStopServices.table.title') }}
        </h2>
      </div>

      <div class="col-auto service-muted text-caption text-weight-bold">
        {{ t('restStopServices.table.serviceCount', { count: serviceCount }) }}
      </div>
    </q-card-section>

    <q-table
      flat
      hide-bottom
      row-key="id"
      :rows="rows"
      :columns="columns"
      :loading="props.loading"
      :pagination="tablePagination"
    >
      <template #body-cell-name="scope">
        <q-td :props="scope">
          <div class="text-weight-bold">
            <span>{{ scope.row.name }}</span>
          </div>
        </q-td>
      </template>

      <template #body-cell-actions="scope">
        <q-td :props="scope">
          <q-btn
            flat
            round
            color="negative"
            icon="delete_outline"
            :aria-label="
              t('restStopServices.table.removeAria', {
                name: scope.row.name
              })
            "
            :disable="props.loading || hasPendingRemove()"
            :loading="isRemoving(scope.row)"
            @click="emit('remove', scope.row)"
          >
            <q-tooltip>{{ t('restStopServices.table.remove') }}</q-tooltip>
          </q-btn>
        </q-td>
      </template>

      <template #no-data>
        <div
          class="services-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon
            class="services-empty-icon"
            name="miscellaneous_services"
            size="34px"
          />
          <div class="column">
            <strong>{{ t('restStopServices.table.emptyTitle') }}</strong>
            <span>{{ t('restStopServices.table.emptyDescription') }}</span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
