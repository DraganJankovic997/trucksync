<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useCountryStore } from '@/stores/country.js';

defineProps({
  label: {
    type: String,
    required: true
  },
  name: {
    type: String,
    default: 'country'
  },
  placeholder: {
    type: String,
    default: ''
  },
  rules: {
    type: Array,
    default: () => []
  }
});

const model = defineModel({
  type: String,
  default: ''
});

const countryStore = useCountryStore();
const { countries } = storeToRefs(countryStore);
const { t } = useI18n();

const countryOptions = computed(() =>
  (countries.value ?? []).map(country => ({
    label: t(`countries.${country.name}`),
    value: country.name
  }))
);

onMounted(async () => {
  await countryStore.fetchCountries();
});
</script>

<template>
  <q-select
    v-model="model"
    class="form-field"
    outlined
    lazy-rules
    emit-value
    map-options
    :label="label"
    :placeholder="placeholder"
    :rules="rules"
    :name="name"
    :options="countryOptions"
  />
</template>
