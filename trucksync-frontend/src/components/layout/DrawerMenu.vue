<script setup>
import { computed } from 'vue';
import EssentialLink from '@/components/EssentialLink.vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);

const drawerOpen = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const linksList = [
  {
    label: 'Dashboard',
    link: '/dashboard',
    icon: 'dashboard'
  },
  {
    label: 'Profile',
    link: '/profile',
    icon: 'o_person'
  }
];
</script>

<template>
  <q-drawer v-model="drawerOpen" show-if-above bordered>
    <q-list>
      <q-item-label header> Essential Links </q-item-label>

      <EssentialLink
        v-for="link in linksList"
        :key="link.label"
        v-bind="link"
      />
    </q-list>
  </q-drawer>
</template>
