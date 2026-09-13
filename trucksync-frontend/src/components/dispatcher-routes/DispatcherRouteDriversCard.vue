<script setup>
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  drivers: {
    type: Array,
    default: () => []
  },
  assignedDrivers: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  routeClosed: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['save']);
const { t } = useI18n();

const selectedDriverIds = ref([]);
const convoyLeaderId = ref(null);
const originalSignature = ref('');

const assignedCount = computed(() => selectedDriverIds.value.length);
const hasDrivers = computed(() => props.drivers.length > 0);
const hasChanges = computed(
  () => assignmentSignature(currentAssignments()) !== originalSignature.value
);
const canSave = computed(
  () =>
    !props.routeClosed && !props.loading && !props.saving && hasChanges.value
);

watch(
  () => props.assignedDrivers,
  () => {
    syncFormFromAssignments();
  },
  {
    immediate: true,
    deep: true
  }
);

watch(
  selectedDriverIds,
  driverIds => {
    if (
      convoyLeaderId.value !== null &&
      !driverIds.map(Number).includes(Number(convoyLeaderId.value))
    ) {
      convoyLeaderId.value = null;
    }
  },
  { deep: true }
);

function syncFormFromAssignments() {
  selectedDriverIds.value = props.assignedDrivers.map(driver => driver.id);
  convoyLeaderId.value =
    props.assignedDrivers.find(driver => driver.is_convoy_leader)?.id ?? null;
  originalSignature.value = assignmentSignature(currentAssignments());
}

function driverName(driver) {
  const firstName = driver.user?.first_name ?? '';
  const lastName = driver.user?.last_name ?? '';
  const fullName = `${firstName} ${lastName}`.trim();

  return (
    fullName ||
    driver.user?.email ||
    t('dispatcherRouteEdit.drivers.unknownDriver')
  );
}

function driverLicense(driver) {
  return driver.license_number
    ? t('dispatcherRouteEdit.drivers.licenseNumber', {
        licenseNumber: driver.license_number
      })
    : t('dispatcherRouteEdit.drivers.emptyValue');
}

function isDriverSelected(driverId) {
  return selectedDriverIds.value.map(Number).includes(Number(driverId));
}

function currentAssignments() {
  return selectedDriverIds.value.map(driverId => ({
    driver_id: Number(driverId),
    is_convoy_leader: Number(driverId) === Number(convoyLeaderId.value)
  }));
}

function assignmentSignature(assignments) {
  return assignments
    .map(assignment => ({
      driver_id: Number(assignment.driver_id),
      is_convoy_leader: Boolean(assignment.is_convoy_leader)
    }))
    .sort((first, second) => first.driver_id - second.driver_id)
    .map(
      assignment =>
        `${assignment.driver_id}:${assignment.is_convoy_leader ? 'leader' : 'driver'}`
    )
    .join('|');
}

function saveDrivers() {
  if (!canSave.value) {
    return;
  }

  emit('save', currentAssignments());
}

function clearConvoyLeader() {
  convoyLeaderId.value = null;
}
</script>

<template>
  <q-card class="dispatcher-route-drivers-card q-mb-lg" bordered flat>
    <q-card-section
      class="row items-start justify-between q-col-gutter-md q-pa-lg q-pb-md"
    >
      <div class="col-12 col-md">
        <h2 class="text-h6 text-weight-bold q-my-none">
          {{ t('dispatcherRouteEdit.drivers.title') }}
        </h2>
      </div>

      <div
        class="col-auto dispatcher-route-drivers-muted text-caption text-weight-bold"
      >
        {{
          t('dispatcherRouteEdit.drivers.assignedCount', {
            count: assignedCount
          })
        }}
      </div>
    </q-card-section>

    <q-separator />

    <q-card-section class="q-pa-none">
      <div
        v-if="!hasDrivers && !loading"
        class="dispatcher-route-drivers-empty-state row items-center justify-center q-gutter-md q-pa-xl"
      >
        <q-icon
          class="dispatcher-route-drivers-empty-icon"
          name="groups"
          size="34px"
        />
        <div class="column">
          <strong>{{ t('dispatcherRouteEdit.drivers.emptyTitle') }}</strong>
          <span>{{ t('dispatcherRouteEdit.drivers.emptyDescription') }}</span>
        </div>
      </div>

      <q-list v-else separator>
        <q-item
          v-for="driver in drivers"
          :key="driver.id"
          class="dispatcher-route-drivers-row"
        >
          <q-item-section avatar>
            <q-checkbox
              v-model="selectedDriverIds"
              :val="driver.id"
              :disable="routeClosed || loading || saving"
              :aria-label="
                t('dispatcherRouteEdit.drivers.assignAriaLabel', {
                  driver: driverName(driver)
                })
              "
            />
          </q-item-section>

          <q-item-section>
            <q-item-label class="text-weight-bold">
              {{ driverName(driver) }}
            </q-item-label>
            <q-item-label caption>
              {{ driver.user?.email }}
            </q-item-label>
            <q-item-label caption>
              {{ driverLicense(driver) }}
            </q-item-label>
          </q-item-section>

          <q-item-section class="dispatcher-route-drivers-leader" side>
            <q-radio
              v-model="convoyLeaderId"
              :val="driver.id"
              :disable="
                routeClosed || loading || saving || !isDriverSelected(driver.id)
              "
              :label="t('dispatcherRouteEdit.drivers.convoyLeader')"
            />
          </q-item-section>
        </q-item>
      </q-list>
    </q-card-section>

    <q-separator v-if="hasDrivers" />

    <q-card-actions v-if="hasDrivers" align="right" class="q-pa-lg">
      <q-btn
        flat
        color="primary"
        icon="person_remove"
        no-caps
        class="text-weight-bold"
        :disable="routeClosed || loading || saving || convoyLeaderId === null"
        :label="t('dispatcherRouteEdit.drivers.actions.clearLeader')"
        @click="clearConvoyLeader"
      />

      <q-btn
        color="primary"
        icon="save"
        no-caps
        unelevated
        class="text-weight-bold"
        :disable="!canSave"
        :loading="saving"
        :label="t('dispatcherRouteEdit.drivers.actions.save')"
        @click="saveDrivers"
      />
    </q-card-actions>
  </q-card>
</template>
