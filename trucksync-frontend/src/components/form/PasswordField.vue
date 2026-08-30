<script setup>
defineProps({
  label: {
    type: String,
    default: 'Password'
  },
  maxlength: {
    type: Number,
    default: undefined
  },
  name: {
    type: String,
    default: 'password'
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
    class="form-field"
    outlined
    lazy-rules
    type="password"
    :label="label"
    :placeholder="placeholder"
    :rules="rules"
    :name="name"
    :maxlength="maxlength"
  />
</template>
