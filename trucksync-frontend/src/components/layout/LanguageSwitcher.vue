<script setup>
import { onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n({ useScope: 'global' });

const localeOptions = [
  { value: 'en', label: 'English' },
  { value: 'rs', label: 'Serbian' },
];

onMounted(() => {
  const initial = localStorage.getItem('lang');

  if (initial && localeOptions.some((item) => item.value === initial)) {
    locale.value = initial;
  }
});

watch(locale, (newLanguage) => {
  localStorage.setItem('lang', newLanguage);
});
</script>

<template>
  <div class="language-switcher-wrapper">
    <q-select
      v-model="locale"
      :options="localeOptions"
      dense
      borderless
      emit-value
      map-options
      options-dense
      dark
      hide-dropdown-icon
      id="interaction-header-languageSwitcher"
      popup-content-class="language-switcher"
      :display-value="locale.toUpperCase()"
    />
  </div>
</template>
