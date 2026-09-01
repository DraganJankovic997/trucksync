<script setup>
import { computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n({ useScope: 'global' });
const supportedLocales = ['en', 'rs'];

const localeOptions = computed(() => [
  { value: 'en', label: t('layout.language.english') },
  { value: 'rs', label: t('layout.language.serbian') }
]);

onMounted(() => {
  const initial = localStorage.getItem('lang');

  if (supportedLocales.includes(initial)) {
    locale.value = initial;
  }
});

watch(locale, newLanguage => {
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
