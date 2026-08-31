<script setup>
import { ref } from 'vue';
import DrawerMenu from '@/components/layout/DrawerMenu.vue';
import Toolbar from '@/components/layout/Toolbar.vue';

const leftDrawerOpen = ref(false);

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value;
}
</script>

<template>
  <q-layout view="lHh Lpr lFf">
    <Toolbar @toggle-left-drawer="toggleLeftDrawer" />

    <DrawerMenu v-model="leftDrawerOpen" />

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>
