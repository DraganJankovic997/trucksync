<script setup>
const emit = defineEmits(['toggle-left-drawer']);
</script>

<template>
  <q-header elevated>
    <q-toolbar>
      <q-btn
        flat
        dense
        round
        icon="menu"
        aria-label="Menu"
        @click="emit('toggle-left-drawer')"
      />

      <q-toolbar-title> TruckSync </q-toolbar-title>

      <div>
        <q-btn
          icon="logout"
          label="Log out"
          no-caps
          dark
          outline
          @click="handleLogout"
        />
      </div>
    </q-toolbar>
  </q-header>
</template>
