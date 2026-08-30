import { Notify } from 'quasar';

const DEFAULT_SUCCESS_MESSAGE = 'Action completed successfully.';
const DEFAULT_ERROR_MESSAGE = 'Something went wrong.';

function filledString(value) {
  return typeof value === 'string' && value.trim().length > 0;
}

function success(message = DEFAULT_SUCCESS_MESSAGE) {
  Notify.create({
    message: filledString(message) ? message.trim() : DEFAULT_SUCCESS_MESSAGE,
    color: 'positive',
    position: 'top',
    timeout: 2000
  });
}

function error(message = DEFAULT_ERROR_MESSAGE) {
  Notify.create({
    message: filledString(message) ? message.trim() : DEFAULT_ERROR_MESSAGE,
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
