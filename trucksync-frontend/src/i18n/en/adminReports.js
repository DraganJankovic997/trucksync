export default {
  title: 'Usage reports',
  eyebrow: 'Admin reports',
  description:
    'Review rest stop usage ratings and reports submitted by convoy leaders.',
  actions: {
    refresh: 'Refresh'
  },
  filters: {
    isReport: 'is_report',
    restStop: 'Rest stop',
    allRestStops: 'All rest stops'
  },
  table: {
    title: 'Rest stop usage',
    rowCount: 'Entries: {count}',
    emptyValue: '-',
    yes: 'Yes',
    no: 'No',
    ratingValue: '{rating} / 5',
    restStopFallback: 'Rest stop #{id}',
    emptyTitle: 'No usage reports found',
    emptyDescription: 'Matching usage reports will appear here.',
    columns: {
      id: 'ID',
      usedAt: 'Used at',
      restStop: 'Rest stop',
      routeStopId: 'Route stop',
      driverId: 'Driver',
      rating: 'Rating',
      isReport: 'is_report',
      report: 'Report',
      createdAt: 'Created',
      updatedAt: 'Updated'
    }
  }
};
