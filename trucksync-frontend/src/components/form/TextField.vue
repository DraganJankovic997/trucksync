<template>
  <q-input
    v-model="model"
    class="form-field form-text-field"
    outlined
    reactive-rules
    lazy-rules
    no-error-icon
    :clearable="clearable"
    :type="type"
    :name="name"
    :label="label"
    :autocomplete="autocomplete"
    :rules="rules"
    :error="error"
    :error-message="errorMessage"
    :maxlength="maxlength"
    v-bind="$attrs"
  >
    <template v-if="icon" #prepend>
      <q-icon :name="icon" />
    </template>

    <template v-if="$slots.append" #append>
      <slot name="append" />
    </template>
  </q-input>
</template>

<script setup>
defineOptions({
  inheritAttrs: false
})

defineProps({
  autocomplete: {
    type: String,
    default: undefined
  },
  clearable: {
    type: Boolean,
    default: true
  },
  error: {
    type: Boolean,
    default: false
  },
  errorMessage: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: undefined
  },
  label: {
    type: String,
    required: true
  },
  maxlength: {
    type: Number,
    default: undefined
  },
  name: {
    type: String,
    default: undefined
  },
  rules: {
    type: Array,
    default: () => []
  },
  type: {
    type: String,
    default: 'text'
  }
})

const model = defineModel({
  type: String,
  default: ''
})
</script>
