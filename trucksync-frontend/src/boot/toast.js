import { Notify } from 'quasar';
import { i18n } from '@/i18n/instance.js';

const DEFAULT_SUCCESS_MESSAGE_KEY = 'messages.common.success';
const DEFAULT_ERROR_MESSAGE_KEY = 'messages.common.error';

function filledString(value) {
  return typeof value === 'string' && value.trim().length > 0;
}

function getDefaultMessage(key) {
  return i18n.global.t(key);
}

function success(message = getDefaultMessage(DEFAULT_SUCCESS_MESSAGE_KEY)) {
  Notify.create({
    message: filledString(message)
      ? message.trim()
      : getDefaultMessage(DEFAULT_SUCCESS_MESSAGE_KEY),
    color: 'positive',
    position: 'top',
    timeout: 2000
  });
}

function error(message = getDefaultMessage(DEFAULT_ERROR_MESSAGE_KEY)) {
  Notify.create({
    message: filledString(message)
      ? message.trim()
      : getDefaultMessage(DEFAULT_ERROR_MESSAGE_KEY),
    color: 'negative',
    position: 'top',
    timeout: 2000
  });
}

const toast = {
  success: success,
  error: error
};

export { toast };
