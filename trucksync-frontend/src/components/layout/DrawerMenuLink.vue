<script setup>
const props = defineProps({
  label: {
    type: String,
    required: true
  },

  link: {
    type: [String, Object],
    required: true
  },

  icon: {
    type: String,
    default: ''
  }
});
</script>

<template>
  <q-item clickable :to="props.link">
    <q-item-section v-if="props.icon" avatar>
      <q-icon :name="props.icon" />
    </q-item-section>

    <q-item-section>
      <q-item-label>{{ props.label }}</q-item-label>
    </q-item-section>
  </q-item>
</template>
