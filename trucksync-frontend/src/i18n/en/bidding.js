export default {
  emptyValue: '-',
  page: {
    ariaLabel: 'Route stop bidding request {id}',
    eyebrow: 'Bidding request',
    title: 'Route stop {id}',
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
    title: 'Route {id}',
    routeSection: 'Route',
    dispatcherSection: 'Dispatcher',
    status: {
      open: 'Open',
      closed: 'Closed'
    },
    fields: {
      origin: 'Origin',
      destination: 'Destination',
      convoySize: 'Convoy size',
      startDate: 'Start date',
      endDate: 'End date',
      plannedTravelDetails: 'Planned travel details'
    },
    dispatcher: {
      companyName: 'Company',
      city: 'City',
      address: 'Address',
      postCode: 'Post code',
      registrationNumber: 'Registration number'
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
