<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  dispatchers: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  approvingUserId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits({
  approve: dispatcher =>
    dispatcher?.user_id !== undefined && dispatcher?.user_id !== null
});

const { t } = useI18n();
const tablePagination = { rowsPerPage: 0 };

const columns = computed(() => [
  {
    name: 'profileId',
    label: t('approval.table.profileId'),
    field: 'profileId',
    align: 'left',
    sortable: true
  },
  {
    name: 'userId',
    label: t('approval.table.userId'),
    field: 'userId',
    align: 'left',
    sortable: true
  },
  {
    name: 'firstName',
    label: t('approval.table.firstName'),
    field: 'firstName',
    align: 'left',
    sortable: true
  },
  {
    name: 'lastName',
    label: t('approval.table.lastName'),
    field: 'lastName',
    align: 'left',
    sortable: true
  },
  {
    name: 'email',
    label: t('approval.table.email'),
    field: 'email',
    align: 'left',
    sortable: true
  },
  {
    name: 'phoneNumber',
    label: t('approval.table.phoneNumber'),
    field: 'phoneNumber',
    align: 'left',
    sortable: true
  },
  {
    name: 'country',
    label: t('approval.table.country'),
    field: 'country',
    align: 'left',
    sortable: true
  },
  {
    name: 'companyName',
    label: t('approval.dispatchers.companyName'),
    field: 'companyName',
    align: 'left',
    sortable: true
  },
  {
    name: 'city',
    label: t('approval.table.city'),
    field: 'city',
    align: 'left',
    sortable: true
  },
  {
    name: 'address',
    label: t('approval.table.address'),
    field: 'address',
    align: 'left',
    sortable: true
  },
  {
    name: 'postCode',
    label: t('approval.table.postCode'),
    field: 'postCode',
    align: 'left',
    sortable: true
  },
  {
    name: 'registrationNumber',
    label: t('approval.dispatchers.registrationNumber'),
    field: 'registrationNumber',
    align: 'left',
    sortable: true
  },
  {
    name: 'actions',
    label: t('approval.table.actions'),
    field: 'actions',
    align: 'right'
  }
]);

const rows = computed(() =>
  props.dispatchers.map(dispatcher => ({
    id: dispatcher.id,
    user_id: dispatcher.user_id,
    profileId: dispatcher.id,
    userId: dispatcher.user_id,
    fullName: formatName(dispatcher.user),
    firstName: formatValue(dispatcher.user?.first_name),
    lastName: formatValue(dispatcher.user?.last_name),
    email: formatValue(dispatcher.user?.email),
    phoneNumber: formatValue(dispatcher.user?.phone_number),
    country: formatValue(dispatcher.user?.country),
    companyName: formatValue(dispatcher.company_name),
    city: formatValue(dispatcher.city),
    address: formatValue(dispatcher.address),
    postCode: formatValue(dispatcher.post_code),
    registrationNumber: formatValue(dispatcher.registration_number)
  }))
);

const dispatcherCount = computed(() => props.dispatchers.length);

function formatName(user) {
  return (
    [user?.first_name, user?.last_name]
      .filter(value => Boolean(value))
      .join(' ') || t('approval.table.emptyValue')
  );
}

function formatValue(value) {
  return value ?? t('approval.table.emptyValue');
}

function isApproving(dispatcher) {
  return String(props.approvingUserId) === String(dispatcher.user_id);
}

function hasPendingApproval() {
  return props.approvingUserId !== undefined && props.approvingUserId !== null;
}
</script>

<template>
  <q-card class="approval-card approval-table-card" bordered flat>
    <q-card-section
      class="row items-center justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('approval.dispatchers.title') }}
        </h2>
      </div>

      <div class="col-auto approval-muted text-caption text-weight-bold">
        {{ t('approval.dispatchers.count', { count: dispatcherCount }) }}
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
      <template #body-cell-actions="scope">
        <q-td :props="scope">
          <div class="row items-center no-wrap q-gutter-xs">
            <q-btn
              outline
              color="primary"
              class="approval-action-button"
              icon="o_check_circle"
              :aria-label="
                t('approval.table.approveAria', {
                  name: scope.row.fullName
                })
              "
              :disable="props.loading || hasPendingApproval()"
              :loading="isApproving(scope.row)"
              @click="emit('approve', scope.row)"
            >
              <q-tooltip>{{ t('approval.table.approve') }}</q-tooltip>
            </q-btn>

            <q-btn
              outline
              color="primary"
              class="approval-action-button"
              icon="o_block"
              :aria-label="
                t('approval.table.blockAria', {
                  name: scope.row.fullName
                })
              "
              disable
            >
              <q-tooltip>{{ t('approval.table.block') }}</q-tooltip>
            </q-btn>
          </div>
        </q-td>
      </template>

      <template #body-cell-firstName="scope">
        <q-td :props="scope">
          <div class="text-weight-bold">
            {{ scope.row.firstName }}
          </div>
        </q-td>
      </template>

      <template #no-data>
        <div
          class="approval-empty-state row items-center justify-center q-gutter-md q-pa-xl"
        >
          <q-icon
            class="approval-empty-icon"
            name="verified_user"
            size="34px"
          />
          <div class="column">
            <strong>{{ t('approval.dispatchers.emptyTitle') }}</strong>
            <span>{{ t('approval.dispatchers.emptyDescription') }}</span>
          </div>
        </div>
      </template>
    </q-table>
  </q-card>
</template>
