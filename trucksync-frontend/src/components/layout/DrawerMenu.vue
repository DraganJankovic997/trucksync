<script setup>
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import DrawerMenuLink from '@/components/layout/DrawerMenuLink.vue';
import { useAuthStore } from '@/stores/auth.js';
import { hasRole } from '@/utils/authorization.js';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);
const { t } = useI18n();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

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
    labelKey: 'layout.navigation.dashboard',
    link: '/dashboard',
    icon: 'dashboard'
  },
  {
    labelKey: 'layout.navigation.profile',
    link: '/profile',
    icon: 'o_person'
  },
  {
    labelKey: 'layout.navigation.profileServices',
    link: '/profile/services',
    icon: 'miscellaneous_services',
    restStopOnly: true
  },
  {
    labelKey: 'layout.navigation.adminServices',
    link: '/admin/services',
    icon: 'miscellaneous_services',
    adminOnly: true
  }
];

const visibleLinksList = computed(() =>
  linksList.filter(link => {
    if (link.adminOnly && !hasRole(user.value, 'admin')) {
      return false;
    }

    if (link.restStopOnly && user.value?.profile_type !== 'rest_stop') {
      return false;
    }

    return true;
  })
);
</script>

<template>
  <q-drawer v-model="drawerOpen" show-if-above bordered>
    <q-list>
      <q-item-label header>
        {{ t('layout.navigation.header') }}
      </q-item-label>

      <DrawerMenuLink
        v-for="link in visibleLinksList"
        :key="link.labelKey"
        :label="t(link.labelKey)"
        :link="link.link"
        :icon="link.icon"
      />
    </q-list>
  </q-drawer>
</template>
