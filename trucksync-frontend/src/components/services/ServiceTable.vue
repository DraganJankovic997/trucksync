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
  deletingId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits({
  delete: service => service?.id !== undefined && service?.id !== null
});

const { t } = useI18n();
const tablePagination = { rowsPerPage: 0 };

const columns = computed(() => [
  {
    name: 'id',
    label: t('services.table.id'),
    field: 'id',
    align: 'left',
    sortable: true
  },
  {
    name: 'name',
    label: t('services.table.name'),
    field: 'name',
    align: 'left',
    sortable: true
  },
  {
    name: 'actions',
    label: t('services.table.actions'),
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

function isDeleting(service) {
  return String(props.deletingId) === String(service.id);
}

function hasPendingDelete() {
  return props.deletingId !== undefined && props.deletingId !== null;
}
</script>

<template>
  <q-card class="service-card service-table-card" bordered flat>
    <q-card-section
      class="row items-center justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('services.table.title') }}
        </h2>
      </div>

      <div class="col-auto service-muted text-caption text-weight-bold">
        {{ t('services.serviceCount', { count: serviceCount }) }}
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
              t('services.table.deleteAria', {
                name: scope.row.name
              })
            "
            :disable="props.loading || hasPendingDelete()"
            :loading="isDeleting(scope.row)"
            @click="emit('delete', scope.row)"
          >
            <q-tooltip>{{ t('services.table.delete') }}</q-tooltip>
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
            <strong>{{ t('services.table.emptyTitle') }}</strong>
            <span>{{ t('services.table.emptyDescription') }}</span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
