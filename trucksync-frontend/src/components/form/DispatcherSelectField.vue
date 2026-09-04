<script setup>
import { computed } from 'vue';

defineProps({
  label: {
    type: String,
    required: true
  },
  name: {
    type: String,
    default: 'dispatcher_id'
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
  type: [String, Number],
  default: ''
});

const dispatcherOptions = computed(() => []);
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
    :options="dispatcherOptions"
  />
</template>
