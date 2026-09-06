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
    table: {
      id: 'ID',
      numberOfTrucks: 'Trucks',
      numberOfDrivers: 'Drivers',
      services: 'Services',
      emptyValue: '-',
      emptyTitle: 'No route stops added',
      emptyDescription: 'Route stops will appear here.'
    }
  }
};
