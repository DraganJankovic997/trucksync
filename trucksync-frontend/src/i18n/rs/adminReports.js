export default {
  title: 'Prijave korišćenja',
  eyebrow: 'Admin prijave',
  description:
    'Pregled ocena i prijava odmorišta koje su poslali vođe konvoja.',
  actions: {
    refresh: 'Osveži'
  },
  filters: {
    isReport: 'is_report',
    restStop: 'Odmorište',
    allRestStops: 'Sva odmorišta'
  },
  table: {
    title: 'Korišćenje odmorišta',
    rowCount: 'Unosi: {count}',
    emptyValue: '-',
    yes: 'Da',
    no: 'Ne',
    ratingValue: '{rating} / 5',
    restStopFallback: 'Odmorište #{id}',
    emptyTitle: 'Nema pronađenih prijava',
    emptyDescription: 'Odgovarajuće prijave korišćenja će biti prikazane ovde.',
    columns: {
      id: 'ID',
      usedAt: 'Korišćeno',
      restStop: 'Odmorište',
      routeStopId: 'Stajalište',
      driverId: 'Vozač',
      rating: 'Ocena',
      isReport: 'is_report',
      report: 'Prijava',
      createdAt: 'Kreirano',
      updatedAt: 'Ažurirano'
    }
  }
};
