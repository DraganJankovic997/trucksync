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
      pricePerUnit: 'Existing price per unit',
      lineTotal: 'Line total',
      unavailable: 'Unavailable',
      emptyTitle: 'No services listed',
      emptyDescription: 'Required services will appear here.'
    }
  },
  bidForm: {
    ariaLabel: 'Bid form',
    fullPriceTotal: 'Full price total',
    missingPrices:
      'Add these services to your rest stop before bidding: {services}',
    unavailable: "You can't bid on this request right now.",
    unavailableMissingServices:
      "You can't bid on this request until all required services are offered by your rest stop.",
    unavailableNoServices:
      "You can't bid on this request because it has no required services.",
    unavailableProfile: 'Complete your rest stop profile before bidding.',
    fields: {
      customPrice: {
        label: 'Custom bid price',
        placeholder: '0.00',
        decimal: 'Custom bid price must use up to 2 decimal places'
      }
    },
    actions: {
      submit: 'Submit bid',
      update: 'Update bid'
    }
  }
};
