<template>
  <TextField
    v-model="model"
    class="form-password-field"
    :type="inputType"
    :clearable="false"
    :icon="icon"
    :label="label"
    :name="name"
    :autocomplete="autocomplete"
    :rules="rules"
    v-bind="$attrs"
  >
    <template #append>
      <q-btn
        flat
        round
        dense
        type="button"
        :icon="toggleIcon"
        :aria-label="toggleLabel"
        @click="isVisible = !isVisible"
      >
        <q-tooltip>{{ toggleLabel }}</q-tooltip>
      </q-btn>
    </template>
  </TextField>
</template>

<script setup>
import { computed, ref } from 'vue'
import TextField from '@/components/form/TextField.vue'

defineOptions({
  inheritAttrs: false
})

defineProps({
  autocomplete: {
    type: String,
    default: 'new-password'
  },
  icon: {
    type: String,
    default: 'lock'
  },
  label: {
    type: String,
    default: 'Password'
  },
  name: {
    type: String,
    default: 'password'
  },
  rules: {
    type: Array,
    default: () => []
  }
})

const model = defineModel({
  type: String,
  default: ''
})

const isVisible = ref(false)
const inputType = computed(() => (isVisible.value ? 'text' : 'password'))
const toggleIcon = computed(() =>
  isVisible.value ? 'visibility_off' : 'visibility'
)
const toggleLabel = computed(() =>
  isVisible.value ? 'Hide password' : 'Show password'
)
</script>
