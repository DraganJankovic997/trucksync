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
      pricePerUnit: 'Postojeca cena po jedinici',
      lineTotal: 'Ukupno po stavci',
      unavailable: 'Nedostupno',
      emptyTitle: 'Nema navedenih usluga',
      emptyDescription: 'Potrebne usluge ce biti prikazane ovde.'
    }
  },
  bidForm: {
    ariaLabel: 'Forma za ponudu',
    fullPriceTotal: 'Ukupna puna cena',
    missingPrices:
      'Dodajte ove usluge svom odmoristu pre slanja ponude: {services}',
    unavailable: 'Trenutno ne mozete poslati ponudu za ovaj zahtev.',
    unavailableMissingServices:
      'Ne mozete poslati ponudu dok vase odmoriste ne nudi sve trazene usluge.',
    unavailableNoServices:
      'Ne mozete poslati ponudu jer zahtev nema trazene usluge.',
    unavailableProfile: 'Popunite profil odmorista pre slanja ponude.',
    fields: {
      customPrice: {
        label: 'Prilagodjena cena ponude',
        placeholder: '0.00',
        decimal: 'Cena ponude mora imati najvise 2 decimale'
      }
    },
    actions: {
      submit: 'Posalji ponudu',
      update: 'Azuriraj ponudu'
    }
  }
};
