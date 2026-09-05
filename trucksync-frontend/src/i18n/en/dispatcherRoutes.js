export default {
  title: 'Dispatcher routes',
  eyebrow: 'Route management',
  routeCount: 'Routes: {count}',
  actions: {
    create: 'New route',
    refresh: 'Refresh'
  },
  form: {
    ariaLabel: 'Create dispatcher route',
    title: 'New route',
    actions: {
      cancel: 'Cancel',
      create: 'Create route'
    },
    fields: {
      origin: {
        label: 'Origin',
        placeholder: 'Enter route origin'
      },
      destination: {
        label: 'Destination',
        placeholder: 'Enter route destination'
      },
      convoySize: {
        label: 'Convoy size',
        placeholder: 'Enter convoy size'
      },
      startDate: {
        label: 'Start date'
      },
      endDate: {
        label: 'End date'
      },
      plannedTravelDetails: {
        label: 'Planned travel details',
        placeholder: 'Add notes for the planned route'
      }
    }
  },
  table: {
    title: 'Created routes',
    id: 'ID',
    origin: 'Origin',
    destination: 'Destination',
    convoySize: 'Convoy size',
    startDate: 'Start date',
    endDate: 'End date',
    closedAt: 'Closed at',
    plannedTravelDetails: 'Planned travel details',
    open: 'Open',
    emptyValue: '-',
    emptyTitle: 'No routes created',
    emptyDescription: 'Created routes will appear here.'
  }
};
