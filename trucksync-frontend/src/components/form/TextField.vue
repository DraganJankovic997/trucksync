<script setup>
defineProps({
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
  placeholder: {
    type: String,
    default: ''
  },
  rules: {
    type: Array,
    default: () => []
  },
  type: {
    type: String,
    default: 'text'
  },
  min: {
    type: [Number, String],
    default: undefined
  },
  max: {
    type: [Number, String],
    default: undefined
  },
  step: {
    type: [Number, String],
    default: undefined
  },
  autogrow: {
    type: Boolean,
    default: false
  },
  stackLabel: {
    type: Boolean,
    default: false
  },
  rows: {
    type: [Number, String],
    default: undefined
  },
  inputStyle: {
    type: [Object, String],
    default: undefined
  }
});

const model = defineModel({
  type: [Number, String],
  default: ''
});
</script>

<template>
  <q-input
    v-model="model"
    class="form-field"
    outlined
    lazy-rules
    :type="type"
    :label="label"
    :placeholder="placeholder"
    :rules="rules"
    :name="name"
    :maxlength="maxlength"
    :min="min"
    :max="max"
    :step="step"
    :autogrow="autogrow"
    :stack-label="stackLabel"
    :rows="rows"
    :input-style="inputStyle"
  />
</template>
