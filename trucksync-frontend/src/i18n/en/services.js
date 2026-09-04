export default {
  title: 'Services',
  eyebrow: 'Admin controls',
  description: 'Manage the service options available across TruckSync.',
  serviceCount: 'Total services: {count}',
  actions: {
    refresh: 'Refresh'
  },
  form: {
    title: 'Add service',
    ariaLabel: 'Create service form',
    name: {
      label: 'Service name',
      placeholder: 'Parking, shower, repair...'
    },
    submit: 'Add service'
  },
  table: {
    title: 'All services',
    id: 'ID',
    name: 'Name',
    actions: 'Actions',
    delete: 'Delete',
    deleteAria: 'Delete {name}',
    emptyTitle: 'No services yet',
    emptyDescription: 'Add the first service to make it available.'
  },
  deleteDialog: {
    title: 'Delete service?',
    message: 'Delete "{name}" from the service list?',
    warning: 'This action cannot be undone.',
    fallbackName: 'this service',
    cancel: 'Cancel',
    confirm: 'Delete service'
  }
};
