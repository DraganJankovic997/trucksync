<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  profileRecord: {
    type: Object,
    default: undefined
  },
  profileRecordLoaded: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const { t } = useI18n();

const requiredProfileFields = [
  'first_name',
  'last_name',
  'email',
  'country',
  'phone_number',
  'profile_type'
];

const profileTypeRoleKeys = {
  driver: 'driver',
  dispatcher: 'dispatcher',
  rest_stop: 'restStop'
};

const isFilled = value => Boolean(String(value ?? '').trim());

const profileType = computed(() => props.user?.profile_type ?? null);

const roleKey = computed(() => profileTypeRoleKeys[profileType.value] ?? null);

const roleTitle = computed(() =>
  roleKey.value
    ? t(`profileCompletionWarning.roles.${roleKey.value}.title`)
    : t('profileCompletionWarning.roles.fallback.title')
);

const roleDescription = computed(() =>
  roleKey.value
    ? t(`profileCompletionWarning.roles.${roleKey.value}.description`)
    : t('profileCompletionWarning.roles.fallback.description')
);

const isProfileInformationComplete = computed(() =>
  requiredProfileFields.every(field => isFilled(props.user?.[field]))
);

const canCheckRoleProfile = computed(
  () =>
    props.profileRecord !== undefined &&
    props.profileRecordLoaded &&
    Boolean(roleKey.value)
);

const isRoleProfileComplete = computed(
  () => !canCheckRoleProfile.value || Boolean(props.profileRecord)
);

const missingProfileInformation = computed(
  () => !isProfileInformationComplete.value
);

const missingRoleProfile = computed(
  () => canCheckRoleProfile.value && !isRoleProfileComplete.value
);

const shouldShow = computed(
  () =>
    !props.loading &&
    Boolean(props.user) &&
    (missingProfileInformation.value || missingRoleProfile.value)
);

const messageKey = computed(() => {
  if (missingProfileInformation.value && missingRoleProfile.value) {
    return 'missingProfileAndRole';
  }

  if (missingRoleProfile.value) {
    return 'missingRoleProfile';
  }

  return 'missingProfileInformation';
});

const messageParams = computed(() => ({
  roleProfile: roleDescription.value,
  roleProfileTitle: roleTitle.value
}));

const title = computed(() =>
  t(`profileCompletionWarning.${messageKey.value}.title`, messageParams.value)
);

const description = computed(() =>
  t(
    `profileCompletionWarning.${messageKey.value}.description`,
    messageParams.value
  )
);
</script>

<template>
  <q-banner v-if="shouldShow" rounded class="profile-completion-warning">
    <template #avatar>
      <q-icon class="profile-completion-icon" name="warning" />
    </template>

    <div class="profile-completion-title">
      {{ title }}
    </div>
    <div class="profile-completion-description">
      {{ description }}
    </div>

    <template #action>
      <q-btn
        outline
        no-caps
        class="profile-completion-action"
        :to="{ name: 'profile' }"
        :label="t('profileCompletionWarning.action')"
      />
    </template>
  </q-banner>
</template>
