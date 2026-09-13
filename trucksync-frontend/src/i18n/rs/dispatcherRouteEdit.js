export default {
  title: '{origin} -> {destination}',
  fallbackTitle: 'Izmena rute',
  actions: {
    back: 'Nazad na rute',
    closeRoute: 'Zatvori rutu'
  },
  details: {
    title: 'Detalji rute',
    origin: 'Polazište',
    destination: 'Odredište',
    convoySize: 'Veličina konvoja',
    startDate: 'Datum početka',
    endDate: 'Datum završetka',
    acceptedBidsTotal: 'Ukupno prihvaćene ponude',
    plannedTravelDetails: 'Planirani detalji puta',
    open: 'Otvorena',
    closed: 'Zatvorena',
    emptyValue: '-'
  },
  drivers: {
    title: 'Vozači',
    assignedCount: 'Dodeljeno: {count}',
    convoyLeader: 'Vođa konvoja',
    emptyValue: '-',
    unknownDriver: 'Nepoznat vozač',
    licenseNumber: 'Dozvola: {licenseNumber}',
    assignAriaLabel: 'Dodeli vozača {driver} ruti',
    emptyTitle: 'Nema dostupnih vozača',
    emptyDescription:
      'Vozači povezani sa vašim profilom dispečera će biti prikazani ovde.',
    actions: {
      clearLeader: 'Ukloni vođu',
      save: 'Sačuvaj vozače'
    }
  },
  routeStops: {
    title: 'Stajališta rute',
    stopCount: 'Stajališta: {count}',
    actions: {
      add: 'Dodaj novo'
    },
    form: {
      createTitle: 'Kreiraj stajalište rute',
      editTitle: 'Izmeni stajalište rute',
      createAriaLabel: 'Forma za kreiranje stajališta rute',
      editAriaLabel: 'Forma za izmenu stajališta rute',
      servicesTitle: 'Usluge',
      noServices: 'Nema dostupnih usluga.',
      fields: {
        location: {
          label: 'Lokacija',
          placeholder: 'Stajalište za gorivo u Beču'
        },
        description: {
          label: 'Opis',
          placeholder: 'Dodajte detalje stajališta'
        },
        stopAt: {
          label: 'Vreme stajanja'
        },
        numberOfTrucks: {
          label: 'Broj kamiona',
          placeholder: '3'
        },
        numberOfDrivers: {
          label: 'Broj vozača',
          placeholder: '4'
        },
        service: {
          label: 'Usluga',
          placeholder: 'Izaberite uslugu',
          duplicate: 'Usluga je već izabrana'
        },
        quantity: {
          label: 'Količina',
          placeholder: '200',
          defaultUnit: 'komada'
        }
      },
      actions: {
        close: 'Zatvori',
        addService: 'Dodaj uslugu',
        removeService: 'Ukloni uslugu',
        save: 'Sačuvaj'
      }
    },
    table: {
      id: 'ID',
      location: 'Lokacija',
      stopAt: 'Vreme stajanja',
      description: 'Opis',
      numberOfTrucks: 'Kamioni',
      numberOfDrivers: 'Vozači',
      bids: 'Ponude',
      acceptedBidPrice: 'Prihvaćena ponuda',
      bidsButton: 'Ponude: {count}',
      bidsAriaLabel: 'Ponude za stajalište {route_stop_id}: {count}',
      fulfilled: 'Ispunjeno',
      services: 'Usluge',
      emptyValue: '-',
      emptyTitle: 'Nema dodatih stajališta',
      emptyDescription: 'Stajališta rute će biti prikazana ovde.'
    },
    bidsDialog: {
      bidCount: 'Ponude: {count}',
      actions: {
        close: 'Zatvori',
        refresh: 'Osveži ponude',
        submit: 'Potvrdi'
      },
      table: {
        contact: 'Kontakt',
        email: 'Email',
        phone: 'Telefon',
        country: 'Država',
        city: 'Grad',
        address: 'Adresa',
        postCode: 'Poštanski broj',
        originalPrice: 'Originalna cena',
        price: 'Cena ponude',
        emptyValue: '-'
      },
      emptyTitle: 'Još nema ponuda',
      emptyDescription: 'Poslate ponude će biti prikazane ovde.'
    }
  }
};
