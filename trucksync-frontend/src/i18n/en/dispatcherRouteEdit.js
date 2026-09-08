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
      servicesTitle: 'Services',
      noServices: 'No services available.',
      fields: {
        location: {
          label: 'Location',
          placeholder: 'Vienna fuel stop'
        },
        description: {
          label: 'Description',
          placeholder: 'Add stop details'
        },
        stopAt: {
          label: 'Stop at'
        },
        numberOfTrucks: {
          label: 'Number of trucks',
          placeholder: '3'
        },
        numberOfDrivers: {
          label: 'Number of drivers',
          placeholder: '4'
        },
        service: {
          label: 'Service',
          placeholder: 'Select service',
          duplicate: 'Service already selected'
        },
        quantity: {
          label: 'Quantity',
          placeholder: '200',
          defaultUnit: 'units'
        }
      },
      actions: {
        close: 'Close',
        addService: 'Add service',
        removeService: 'Remove service',
        save: 'Save'
      }
    },
    table: {
      id: 'ID',
      location: 'Location',
      stopAt: 'Stop at',
      description: 'Description',
      numberOfTrucks: 'Trucks',
      numberOfDrivers: 'Drivers',
      bids: 'Bids',
      bidsButton: 'Bids: {count}',
      bidsAriaLabel: 'Bids for route stop {route_stop_id}: {count}',
      fulfiled: 'Fulfiled',
      services: 'Services',
      emptyValue: '-',
      emptyTitle: 'No route stops added',
      emptyDescription: 'Route stops will appear here.'
    },
    bidsDialog: {
      bidCount: 'Bids: {count}',
      actions: {
        close: 'Close',
        refresh: 'Refresh bids',
        submit: 'Submit'
      },
      table: {
        contact: 'Contact',
        email: 'Email',
        phone: 'Phone',
        country: 'Country',
        city: 'City',
        address: 'Address',
        postCode: 'Post code',
        originalPrice: 'Original price',
        price: 'Bid price',
        emptyValue: '-'
      },
      emptyTitle: 'No bids yet',
      emptyDescription: 'Submitted bids will appear here.'
    }
  }
};
