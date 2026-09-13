export default {
  title: 'Sve rute',
  eyebrow: 'Rute vozaca',
  description: 'Rute od vaseg izabranog dispecera.',
  routeCount: 'Rute: {count}',
  actions: {
    refresh: 'Osvezi'
  },
  profileRequired: {
    title: 'Dispecer nije dodeljen',
    description: 'Izaberite dispecera u profilu da biste videli rute dispecera.'
  },
  my: {
    title: 'Moje rute',
    eyebrow: 'Dodeljene rute',
    description: 'Rute koje su dodeljene bas vama.',
    table: {
      title: 'Dodeljene rute',
      otherTitle: 'Ostale dodeljene rute',
      emptyTitle: 'Nema dodeljenih ruta',
      emptyDescription: 'Rute dodeljene vama ce biti prikazane ovde.',
      emptyOtherTitle: 'Nema ostalih dodeljenih ruta',
      emptyOtherDescription: 'Dodatne dodeljene rute ce biti prikazane ovde.'
    }
  },
  current: {
    badge: 'Trenutna ruta',
    routeTitle: '{origin} do {destination}',
    dateRange: '{start} - {end}',
    nextStop: 'Sledece stajaliste',
    stopsTitle: 'Stajalista rute',
    stopCount: 'Stajalista: {count}',
    stopCapacity: 'Kamioni: {trucks} | Vozaci: {drivers}',
    stopFulfilled: 'Iskorisceno',
    stopPending: 'Na cekanju',
    unnamedStop: 'Stajaliste #{id}',
    emptyValue: '-',
    emptyStopsTitle: 'Nema planiranih stajalista',
    emptyStopsDescription: 'Stajalista za ovu rutu ce biti prikazana ovde.',
    fields: {
      schedule: 'Vremenski plan',
      convoySize: 'Velicina konvoja',
      assignedDrivers: 'Dodeljeni vozaci',
      yourRole: 'Vasa uloga'
    },
    roles: {
      convoyLeader: 'Vodja konvoja',
      driver: 'Vozac'
    }
  },
  table: {
    title: 'Rute dispecera',
    id: 'ID',
    origin: 'Polaziste',
    destination: 'Odrediste',
    convoySize: 'Velicina konvoja',
    startDate: 'Datum pocetka',
    endDate: 'Datum zavrsetka',
    status: 'Status',
    plannedTravelDetails: 'Planirani detalji puta',
    open: 'Otvorena',
    closed: 'Zatvorena',
    emptyValue: '-',
    emptyTitle: 'Nema dostupnih ruta',
    emptyDescription: 'Rute vaseg dispecera ce biti prikazane ovde.'
  }
};
