<template>
  <TextField
    v-model="model"
    class="form-email-field"
    type="email"
    inputmode="email"
    icon="alternate_email"
    :label="label"
    :name="name"
    :autocomplete="autocomplete"
    :rules="rules"
    v-bind="$attrs"
  />
</template>

<script setup>
import TextField from '@/components/form/TextField.vue'

defineOptions({
  inheritAttrs: false
})

defineProps({
  autocomplete: {
    type: String,
    default: 'email'
  },
  label: {
    type: String,
    default: 'Email'
  },
  name: {
    type: String,
    default: 'email'
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
</script>
