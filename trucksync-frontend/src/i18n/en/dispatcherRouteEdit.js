export default {
  title: 'Route edit: {route_id} - Edit route',
  actions: {
    back: 'Back to routes'
  },
  details: {
    title: 'Route details',
    origin: 'Origin',
    destination: 'Destination',
    convoySize: 'Convoy size',
    startDate: 'Start date',
    endDate: 'End date',
    plannedTravelDetails: 'Planned travel details',
    open: 'Open',
    closed: 'Closed',
    emptyValue: '-'
  },
  routeStops: {
    title: 'Route stops',
    stopCount: 'Stops: {count}',
    actions: {
      add: 'Add new'
    },
    form: {
      createTitle: 'Create route stop',
      editTitle: 'Edit route stop',
      createAriaLabel: 'Create route stop form',
      editAriaLabel: 'Edit route stop form',
      fields: {
        location: {
          label: 'Location',
          placeholder: 'Vienna fuel stop'
        },
        description: {
          label: 'Description',
          placeholder: 'Add stop details'
        },
        numberOfTrucks: {
          label: 'Number of trucks',
          placeholder: '3'
        },
        numberOfDrivers: {
          label: 'Number of drivers',
          placeholder: '4'
        }
      },
      actions: {
        close: 'Close',
        save: 'Save'
      }
    },
    table: {
      id: 'ID',
      location: 'Location',
      description: 'Description',
      numberOfTrucks: 'Trucks',
      numberOfDrivers: 'Drivers',
      services: 'Services',
      emptyValue: '-',
      emptyTitle: 'No route stops added',
      emptyDescription: 'Route stops will appear here.'
    }
  }
};
