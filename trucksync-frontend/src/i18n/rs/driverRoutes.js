export default {
  title: 'Sve rute',
  eyebrow: 'Rute vozača',
  description: 'Rute od vašeg izabranog dispečera.',
  routeCount: 'Rute: {count}',
  actions: {
    refresh: 'Osveži'
  },
  profileRequired: {
    title: 'Dispečer nije dodeljen',
    description: 'Izaberite dispečera u profilu da biste videli rute dispečera.'
  },
  my: {
    title: 'Moje rute',
    eyebrow: 'Dodeljene rute',
    description: 'Rute koje su dodeljene baš vama.',
    table: {
      title: 'Dodeljene rute',
      otherTitle: 'Ostale dodeljene rute',
      emptyTitle: 'Nema dodeljenih ruta',
      emptyDescription: 'Rute dodeljene vama će biti prikazane ovde.',
      emptyOtherTitle: 'Nema ostalih dodeljenih ruta',
      emptyOtherDescription: 'Dodatne dodeljene rute će biti prikazane ovde.'
    }
  },
  current: {
    badge: 'Trenutna ruta',
    routeTitle: '{origin} do {destination}',
    dateRange: '{start} - {end}',
    nextStop: 'Sledeće stajalište',
    stopsTitle: 'Stajališta rute',
    stopCount: 'Stajališta: {count}',
    stopCapacity: 'Kamioni: {trucks} | Vozači: {drivers}',
    stopFulfilled: 'Iskorišćeno',
    stopPending: 'Na čekanju',
    unnamedStop: 'Stajalište #{id}',
    emptyValue: '-',
    emptyStopsTitle: 'Nema planiranih stajališta',
    emptyStopsDescription: 'Stajališta za ovu rutu će biti prikazana ovde.',
    fields: {
      schedule: 'Vremenski plan',
      convoySize: 'Veličina konvoja',
      assignedDrivers: 'Dodeljeni vozači',
      yourRole: 'Vaša uloga'
    },
    roles: {
      convoyLeader: 'Vođa konvoja',
      driver: 'Vozač'
    }
  },
  table: {
    title: 'Rute dispečera',
    id: 'ID',
    origin: 'Polazište',
    destination: 'Odredište',
    convoySize: 'Veličina konvoja',
    startDate: 'Datum početka',
    endDate: 'Datum završetka',
    status: 'Status',
    plannedTravelDetails: 'Planirani detalji puta',
    open: 'Otvorena',
    closed: 'Zatvorena',
    emptyValue: '-',
    emptyTitle: 'Nema dostupnih ruta',
    emptyDescription: 'Rute vašeg dispečera će biti prikazane ovde.'
  }
};
