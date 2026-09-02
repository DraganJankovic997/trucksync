<script setup>
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
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
    option-label="name"
    option-value="name"
    :label="label"
    :placeholder="placeholder"
    :rules="rules"
    :name="name"
    :options="countries ?? []"
  />
</template>
