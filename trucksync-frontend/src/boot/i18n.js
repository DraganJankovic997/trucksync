import { boot } from 'quasar/wrappers';
import { i18n } from '../i18n/instance.js';

export default boot(({ app }) => {
  app.use(i18n);
});

export { i18n };
