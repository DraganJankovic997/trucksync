export default {
  emptyValue: '-',
  page: {
    ariaLabel: 'Route stop bidding request {id}',
    eyebrow: 'Bidding request',
    title: '{dispatcherName} ({registrationNumber})',
    description:
      'Review the route, dispatcher, and required services before preparing an offer.',
    emptyTitle: 'Route stop unavailable',
    emptyDescription: 'This request could not be loaded.',
    actions: {
      back: 'Back to requests',
      refresh: 'Refresh'
    }
  },
  routeDetails: {
    eyebrow: 'Route details',
    title: '{origin} => {destination}',
    routeSection: 'Route',
    status: {
      open: 'Open',
      closed: 'Closed'
    },
    fields: {
      convoySize: 'Convoy size',
      startDate: 'Start date',
      endDate: 'End date',
      plannedTravelDetails: 'Planned travel details'
    }
  },
  routeStopServices: {
    eyebrow: 'Route stop',
    title: 'Required services',
    serviceCount: 'Services: {count}',
    fields: {
      location: 'Location',
      stopAt: 'Stop at',
      numberOfTrucks: 'Trucks',
      numberOfDrivers: 'Drivers',
      description: 'Description'
    },
    table: {
      name: 'Service',
      quantity: 'Quantity',
      measurementUnit: 'Measurement unit',
      emptyTitle: 'No services listed',
      emptyDescription: 'Required services will appear here.'
    }
  }
};
