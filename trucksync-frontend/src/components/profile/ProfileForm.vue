<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import CountrySelectField from '@/components/form/CountrySelectField.vue';
import EmailField from '@/components/form/EmailField.vue';
import TextField from '@/components/form/TextField.vue';
import { useAuthStore } from '@/stores/auth.js';
import { useUserStore } from '@/stores/user.js';

const authStore = useAuthStore();
const userStore = useUserStore();
const { user } = storeToRefs(authStore);
const formRef = ref(null);
const isSaving = ref(false);
const { t } = useI18n();

const form = reactive({
  firstName: '',
  lastName: '',
  email: '',
  country: '',
  phoneNumber: '',
  profileType: ''
});

const required = fieldKey => value =>
  Boolean(String(value ?? '').trim()) ||
  t('validation.required', { field: t(fieldKey) });

const maxLength = (fieldKey, length) => value =>
  String(value ?? '').length <= length ||
  t('validation.maxLength', { field: t(fieldKey), length: length });

const nameRules = fieldKey => [required(fieldKey), maxLength(fieldKey, 255)];

const countryRules = [required('validation.fields.country')];

const emailRules = [
  required('validation.fields.email'),
  value =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value ?? '').trim()) ||
    t('validation.email'),
  maxLength('validation.fields.email', 255)
];

const profileTypeOptions = computed(() => [
  {
    label: t('profile.profileTypes.driver'),
    value: 'driver'
  },
  {
    label: t('profile.profileTypes.dispatcher'),
    value: 'dispatcher'
  },
  {
    label: t('profile.profileTypes.restStop'),
    value: 'rest_stop'
  }
]);

function syncForm(currentUser) {
  if (!currentUser) {
    return;
  }

  form.firstName = currentUser.first_name ?? '';
  form.lastName = currentUser.last_name ?? '';
  form.email = currentUser.email ?? '';
  form.country = currentUser.country ?? '';
  form.phoneNumber = currentUser.phone_number ?? '';
  form.profileType = currentUser.profile_type ?? '';
}

onMounted(() => {
  if (!user.value) {
    void authStore.me();
  }
});

watch(
  user,
  currentUser => {
    syncForm(currentUser);
  },
  { immediate: true }
);

async function handleSubmit() {
  const isValid = await formRef.value?.validate();

  if (!isValid) {
    return;
  }

  isSaving.value = true;

  try {
    await userStore.update({
      first_name: form.firstName,
      last_name: form.lastName,
      email: form.email,
      country: form.country,
      phone_number: form.phoneNumber
    });
  } finally {
    isSaving.value = false;
  }
}
</script>

<template>
  <q-form
    ref="formRef"
    greedy
    class="profile-form-wrapper"
    :aria-label="t('profile.formAriaLabel')"
    @submit.prevent="handleSubmit"
  >
    <q-card class="profile-form" bordered flat>
      <q-card-section class="form-header">
        <h2>{{ t('profile.form.title') }}</h2>
      </q-card-section>

      <q-card-section class="form-body">
        <div class="name-grid">
          <TextField
            v-model="form.firstName"
            :label="t('profile.fields.firstName.label')"
            name="first_name"
            :placeholder="t('profile.fields.firstName.placeholder')"
            :rules="nameRules('validation.fields.firstName')"
            :maxlength="255"
          />

          <TextField
            v-model="form.lastName"
            :label="t('profile.fields.lastName.label')"
            name="last_name"
            :placeholder="t('profile.fields.lastName.placeholder')"
            :rules="nameRules('validation.fields.lastName')"
            :maxlength="255"
          />
        </div>

        <EmailField
          v-model="form.email"
          :label="t('profile.fields.email.label')"
          :placeholder="t('profile.fields.email.placeholder')"
          :rules="emailRules"
          :maxlength="255"
        />

        <div class="profile-grid">
          <CountrySelectField
            v-model="form.country"
            :label="t('profile.fields.country.label')"
            name="country"
            :placeholder="t('profile.fields.country.placeholder')"
            :rules="countryRules"
          />

          <TextField
            v-model="form.phoneNumber"
            :label="t('profile.fields.phoneNumber.label')"
            name="phone_number"
            :placeholder="t('profile.fields.phoneNumber.placeholder')"
            :rules="[
              required('validation.fields.phoneNumber'),
              maxLength('validation.fields.phoneNumber', 30)
            ]"
            :maxlength="30"
          />
        </div>

        <q-select
          v-model="form.profileType"
          class="form-field"
          outlined
          lazy-rules
          disable
          emit-value
          map-options
          name="profile_type"
          :label="t('profile.fields.profileType.label')"
          :options="profileTypeOptions"
        />
      </q-card-section>

      <q-card-actions class="form-actions">
        <q-btn
          class="profile-form-submit"
          type="submit"
          color="primary"
          icon="save"
          :label="t('profile.submit')"
          :loading="isSaving"
          no-caps
          unelevated
        />
      </q-card-actions>
    </q-card>
  </q-form>
</template>
