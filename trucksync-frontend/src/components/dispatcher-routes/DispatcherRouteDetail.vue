<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    default: ''
  },
  format: {
    type: String,
    default: 'value',
    validator: value => ['value', 'date'].includes(value)
  },
  wide: {
    type: Boolean,
    default: false
  }
});

const { t } = useI18n();

const formattedValue = computed(() =>
  props.format === 'date' ? formatDate(props.value) : formatValue(props.value)
);

function formatValue(value) {
  return value === undefined || value === null || value === ''
    ? t('dispatcherRouteEdit.details.emptyValue')
    : value;
}

function formatDate(value) {
  if (!value) {
    return t('dispatcherRouteEdit.details.emptyValue');
  }

  const dateValue = new Date(`${value}T00:00:00`);

  if (Number.isNaN(dateValue.getTime())) {
    return formatValue(value);
  }

  return new Intl.DateTimeFormat(undefined, {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }).format(dateValue);
}
</script>

<template>
  <div
    class="dispatcher-route-edit-detail"
    :class="{ 'dispatcher-route-edit-detail-wide': wide }"
  >
    <span class="dispatcher-route-edit-detail-label">
      {{ label }}
    </span>
    <strong>{{ formattedValue }}</strong>
  </div>
</template>
