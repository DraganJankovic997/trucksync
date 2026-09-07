<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
</script>

<template>
  <q-page class="q-pa-lg">
    {{ route.params.id }}
  </q-page>
</template>
