<script setup>
//
</script>

<template>
  <q-page class="flex flex-center">
    <div class="column items-center">
      <img
        alt="Quasar logo"
        src="~@/assets/quasar-logo-vertical.svg"
        style="width: 200px; height: 200px"
      />

      <h1 class="text-h4 q-mt-md">Dashboard page</h1>

      <q-btn
        class="q-mt-md"
        color="primary"
        to="/second"
        label="Go to Second Page"
        no-caps
      />
    </div>
  </q-page>
</template>
