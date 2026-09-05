export default {
  title: 'My rest stop services',
  eyebrow: 'Profile services',
  description: 'Choose which services are available at your rest stop.',
  actions: {
    refresh: 'Refresh'
  },
  form: {
    title: 'Add service',
    ariaLabel: 'Add rest stop service form',
    service: {
      label: 'Service',
      placeholder: 'Select a service'
    },
    noOptions: 'All available services are already added.',
    submit: 'Add service'
  },
  table: {
    title: 'Selected services',
    serviceCount: 'Selected services: {count}',
    name: 'Name',
    actions: 'Actions',
    remove: 'Remove',
    removeAria: 'Remove {name}',
    emptyTitle: 'No selected services',
    emptyDescription: 'Add services that drivers can find at this rest stop.'
  },
  removeDialog: {
    title: 'Remove service?',
    message: 'Remove "{name}" from your rest stop?',
    warning: 'The service will stay available in the catalog.',
    fallbackName: 'this service',
    cancel: 'Cancel',
    confirm: 'Remove service'
  }
};
