<script setup>
defineProps({
  label: {
    type: String,
    default: 'Email'
  },
  maxlength: {
    type: Number,
    default: undefined
  },
  name: {
    type: String,
    default: 'email'
  },
  placeholder: {
    type: String,
    default: ''
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

<template>
  <q-input
    v-model="model"
    class="form-field form-email-field"
    outlined
    lazy-rules
    type="email"
    :label="label"
    :placeholder="placeholder"
    :rules="rules"
    :name="name"
    :maxlength="maxlength"
  />
</template>
