<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  service: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits({
  'update:modelValue': value => typeof value === 'boolean',
  confirm: () => true
});

const { t } = useI18n();

const dialogOpen = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const serviceName = computed(
  () => props.service?.name ?? t('services.deleteDialog.fallbackName')
);
</script>

<template>
  <q-dialog v-model="dialogOpen" :persistent="props.loading">
    <q-card class="service-delete-dialog" bordered flat>
      <q-card-section class="row no-wrap q-pa-lg q-pb-sm">
        <q-avatar
          square
          size="42px"
          class="service-delete-icon q-mr-md"
          aria-hidden="true"
        >
          <q-icon name="delete_outline" size="24px" />
        </q-avatar>

        <div>
          <h2 class="text-h6 text-weight-bold q-my-none">
            {{ t('services.deleteDialog.title') }}
          </h2>
          <p class="service-muted q-mt-sm q-mb-none">
            {{
              t('services.deleteDialog.message', {
                name: serviceName
              })
            }}
          </p>
        </div>
      </q-card-section>

      <q-card-section class="service-delete-warning q-mx-lg q-pa-sm">
        {{ t('services.deleteDialog.warning') }}
      </q-card-section>

      <q-card-actions class="q-pa-lg q-gutter-sm" align="right">
        <q-btn
          class="text-weight-bold"
          flat
          no-caps
          :label="t('services.deleteDialog.cancel')"
          :disable="props.loading"
          v-close-popup
        />

        <q-btn
          class="text-weight-bold"
          color="negative"
          icon="delete_outline"
          no-caps
          unelevated
          :label="t('services.deleteDialog.confirm')"
          :loading="props.loading"
          @click="emit('confirm')"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
