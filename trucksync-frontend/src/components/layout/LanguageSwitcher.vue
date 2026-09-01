<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n({ useScope: 'global' });
const isOpen = ref(false);
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
  <div class="language-switcher-wrapper q-mr-sm">
    <q-select
      v-model="locale"
      :options="localeOptions"
      class="language-switcher text-weight-bold"
      :class="isOpen ? 'text-grey-4' : 'text-white'"
      dense
      borderless
      emit-value
      map-options
      options-dense
      dark
      hide-dropdown-icon
      hide-bottom-space
      id="interaction-header-languageSwitcher"
      popup-content-class="language-switcher-menu bg-white text-black"
      :display-value="locale.toUpperCase()"
      @popup-show="isOpen = true"
      @popup-hide="isOpen = false"
    />
  </div>
</template>
