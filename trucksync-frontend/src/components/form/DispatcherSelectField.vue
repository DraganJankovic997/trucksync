<script setup>
import { storeToRefs } from 'pinia';
import { computed, onMounted } from 'vue';
import { useDispatcherStore } from '@/stores/dispatcher.js';

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
  default: null
});

const dispatcherStore = useDispatcherStore();
const { dispatchers } = storeToRefs(dispatcherStore);

const dispatcherOptions = computed(() =>
  (dispatchers.value ?? []).map(dispatcher => ({
    label: dispatcher.company_name,
    value: dispatcher.id
  }))
);

onMounted(async () => {
  await dispatcherStore.fetchDispatchers();
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
    :options="dispatcherOptions"
  />
</template>
