export default {
  emptyValue: '-',
  page: {
    ariaLabel: 'Zahtev za ponudu za stajaliste rute {id}',
    eyebrow: 'Zahtev za ponudu',
    title: '{dispatcherName} ({registrationNumber})',
    description:
      'Pregledajte rutu, dispecera i trazene usluge pre pripreme ponude.',
    emptyTitle: 'Stajaliste rute nije dostupno',
    emptyDescription: 'Ovaj zahtev nije moguce ucitati.',
    actions: {
      back: 'Nazad na zahteve',
      refresh: 'Osvezi'
    }
  },
  routeDetails: {
    eyebrow: 'Detalji rute',
    title: '{origin} => {destination}',
    routeSection: 'Ruta',
    status: {
      open: 'Otvorena',
      closed: 'Zatvorena'
    },
    fields: {
      convoySize: 'Velicina konvoja',
      startDate: 'Datum pocetka',
      endDate: 'Datum zavrsetka',
      plannedTravelDetails: 'Planirani detalji puta'
    }
  },
  routeStopServices: {
    eyebrow: 'Stajaliste rute',
    title: 'Potrebne usluge',
    serviceCount: 'Usluge: {count}',
    fields: {
      location: 'Lokacija',
      stopAt: 'Vreme stajanja',
      numberOfTrucks: 'Kamioni',
      numberOfDrivers: 'Vozaci',
      description: 'Opis'
    },
    table: {
      name: 'Usluga',
      quantity: 'Kolicina',
      measurementUnit: 'Jedinica mere',
      emptyTitle: 'Nema navedenih usluga',
      emptyDescription: 'Potrebne usluge ce biti prikazane ovde.'
    }
  }
};
