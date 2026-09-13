export default {
  emptyValue: '-',
  bidsTable: {
    title: 'Ponude',
    bidCount: 'Ponude: {count}',
    actions: {
      refresh: 'Osveži'
    },
    columns: {
      routeStopId: 'Stajalište rute',
      stopAt: 'Vreme stajanja',
      dispatcherCompany: 'Dispečer',
      dispatcherAddress: 'Adresa dispečera',
      contact: 'Kontakt',
      numberOfTrucks: 'Kamioni',
      numberOfDrivers: 'Vozači',
      status: 'Status',
      originalPrice: 'Originalna cena',
      price: 'Cena ponude'
    },
    status: {
      pending: 'Na čekanju',
      selected: 'Izabrana',
      rejected: 'Odbijena'
    },
    emptyTitle: 'Nema pronađenih ponuda',
    emptyDescription: 'Poslate ponude će biti prikazane ovde.'
  },
  page: {
    ariaLabel: 'Zahtev za ponudu za stajalište rute {id}',
    eyebrow: 'Zahtev za ponudu',
    title: '{dispatcherName} ({registrationNumber})',
    description:
      'Pregledajte rutu, dispečera i tražene usluge pre pripreme ponude.',
    emptyTitle: 'Stajalište rute nije dostupno',
    emptyDescription: 'Ovaj zahtev nije moguće učitati.',
    actions: {
      back: 'Nazad na zahteve',
      refresh: 'Osveži'
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
      convoySize: 'Veličina konvoja',
      startDate: 'Datum početka',
      endDate: 'Datum završetka',
      plannedTravelDetails: 'Planirani detalji puta'
    }
  },
  routeStopServices: {
    eyebrow: 'Stajalište rute',
    title: 'Potrebne usluge',
    serviceCount: 'Usluge: {count}',
    fields: {
      location: 'Lokacija',
      stopAt: 'Vreme stajanja',
      numberOfTrucks: 'Kamioni',
      numberOfDrivers: 'Vozači',
      description: 'Opis'
    },
    table: {
      name: 'Usluga',
      quantity: 'Količina',
      measurementUnit: 'Jedinica mere',
      pricePerUnit: 'Postojeća cena po jedinici',
      lineTotal: 'Ukupno po stavci',
      unavailable: 'Nedostupno',
      emptyTitle: 'Nema navedenih usluga',
      emptyDescription: 'Potrebne usluge će biti prikazane ovde.'
    }
  },
  bidForm: {
    ariaLabel: 'Forma za ponudu',
    fullPriceTotal: 'Ukupna puna cena',
    missingPrices:
      'Dodajte ove usluge svom odmorištu pre slanja ponude: {services}',
    unavailable: 'Trenutno ne možete poslati ponudu za ovaj zahtev.',
    unavailableMissingServices:
      'Ne možete poslati ponudu dok vaše odmorište ne nudi sve tražene usluge.',
    unavailableNoServices:
      'Ne možete poslati ponudu jer zahtev nema tražene usluge.',
    unavailableClosedRoute:
      'Ne možete poslati ponudu za ovaj zahtev jer je ruta zatvorena.',
    unavailableProfile: 'Popunite profil odmorišta pre slanja ponude.',
    fields: {
      customPrice: {
        label: 'Prilagođena cena ponude',
        placeholder: '0.00',
        decimal: 'Cena ponude mora imati najviše 2 decimale'
      }
    },
    actions: {
      submit: 'Pošalji ponudu',
      update: 'Ažuriraj ponudu'
    }
  }
};
