export default {
  title: 'All routes',
  eyebrow: 'Driver routes',
  description: 'Routes from your assigned dispatcher.',
  routeCount: 'Routes: {count}',
  actions: {
    refresh: 'Refresh'
  },
  profileRequired: {
    title: 'No dispatcher assigned',
    description: 'Choose a dispatcher in your profile to see dispatcher routes.'
  },
  my: {
    title: 'My routes',
    eyebrow: 'Assigned routes',
    description: 'Routes assigned specifically to you.',
    table: {
      title: 'Assigned routes',
      otherTitle: 'Other assigned routes',
      emptyTitle: 'No routes assigned',
      emptyDescription: 'Routes assigned to you will appear here.',
      emptyOtherTitle: 'No other routes assigned',
      emptyOtherDescription: 'Additional assigned routes will appear here.'
    }
  },
  current: {
    badge: 'Current route',
    routeTitle: '{origin} to {destination}',
    dateRange: '{start} - {end}',
    nextStop: 'Next stop',
    stopsTitle: 'Route stops',
    stopCount: 'Stops: {count}',
    stopCapacity: 'Trucks: {trucks} | Drivers: {drivers}',
    stopFulfilled: 'Used',
    stopPending: 'Pending',
    unnamedStop: 'Stop #{id}',
    emptyValue: '-',
    emptyStopsTitle: 'No stops planned',
    emptyStopsDescription: 'Stops for this route will appear here.',
    fields: {
      schedule: 'Schedule',
      convoySize: 'Convoy size',
      assignedDrivers: 'Assigned drivers',
      yourRole: 'Your role'
    },
    roles: {
      convoyLeader: 'Convoy leader',
      driver: 'Driver'
    }
  },
  table: {
    title: 'Dispatcher routes',
    id: 'ID',
    origin: 'Origin',
    destination: 'Destination',
    convoySize: 'Convoy size',
    startDate: 'Start date',
    endDate: 'End date',
    status: 'Status',
    plannedTravelDetails: 'Planned travel details',
    open: 'Open',
    closed: 'Closed',
    emptyValue: '-',
    emptyTitle: 'No routes available',
    emptyDescription: 'Routes from your dispatcher will appear here.'
  }
};
