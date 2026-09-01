import { createI18n } from 'vue-i18n';
import messages from './index.js';

const DEFAULT_LOCALE = 'en';
const SUPPORTED_LOCALES = Object.keys(messages);

function getInitialLocale() {
  if (typeof window === 'undefined') {
    return DEFAULT_LOCALE;
  }

  const storedLocale = window.localStorage.getItem('lang');

  return SUPPORTED_LOCALES.includes(storedLocale)
    ? storedLocale
    : DEFAULT_LOCALE;
}

const i18n = createI18n({
  locale: getInitialLocale(),
  fallbackLocale: DEFAULT_LOCALE,
  allowComposition: true,
  legacy: false,
  messages
});

export { i18n };
