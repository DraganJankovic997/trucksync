export default {
  title: '{origin} -> {destination}',
  fallbackTitle: 'Izmena rute',
  actions: {
    back: 'Nazad na rute',
    closeRoute: 'Zatvori rutu'
  },
  details: {
    title: 'Detalji rute',
    origin: 'Polaziste',
    destination: 'Odrediste',
    convoySize: 'Velicina konvoja',
    startDate: 'Datum pocetka',
    endDate: 'Datum zavrsetka',
    acceptedBidsTotal: 'Ukupno prihvacene ponude',
    plannedTravelDetails: 'Planirani detalji puta',
    open: 'Otvorena',
    closed: 'Zatvorena',
    emptyValue: '-'
  },
  routeStops: {
    title: 'Stajalista rute',
    stopCount: 'Stajalista: {count}',
    actions: {
      add: 'Dodaj novo'
    },
    form: {
      createTitle: 'Kreiraj stajaliste rute',
      editTitle: 'Izmeni stajaliste rute',
      createAriaLabel: 'Forma za kreiranje stajalista rute',
      editAriaLabel: 'Forma za izmenu stajalista rute',
      servicesTitle: 'Usluge',
      noServices: 'Nema dostupnih usluga.',
      fields: {
        location: {
          label: 'Lokacija',
          placeholder: 'Stajaliste za gorivo u Becu'
        },
        description: {
          label: 'Opis',
          placeholder: 'Dodajte detalje stajalista'
        },
        stopAt: {
          label: 'Vreme stajanja'
        },
        numberOfTrucks: {
          label: 'Broj kamiona',
          placeholder: '3'
        },
        numberOfDrivers: {
          label: 'Broj vozaca',
          placeholder: '4'
        },
        service: {
          label: 'Usluga',
          placeholder: 'Izaberite uslugu',
          duplicate: 'Usluga je vec izabrana'
        },
        quantity: {
          label: 'Kolicina',
          placeholder: '200',
          defaultUnit: 'komada'
        }
      },
      actions: {
        close: 'Zatvori',
        addService: 'Dodaj uslugu',
        removeService: 'Ukloni uslugu',
        save: 'Sacuvaj'
      }
    },
    table: {
      id: 'ID',
      location: 'Lokacija',
      stopAt: 'Vreme stajanja',
      description: 'Opis',
      numberOfTrucks: 'Kamioni',
      numberOfDrivers: 'Vozaci',
      bids: 'Ponude',
      acceptedBidPrice: 'Prihvacena ponuda',
      bidsButton: 'Ponude: {count}',
      bidsAriaLabel: 'Ponude za stajaliste {route_stop_id}: {count}',
      fulfiled: 'Ispunjeno',
      services: 'Usluge',
      emptyValue: '-',
      emptyTitle: 'Nema dodatih stajalista',
      emptyDescription: 'Stajalista rute ce biti prikazana ovde.'
    },
    bidsDialog: {
      bidCount: 'Ponude: {count}',
      actions: {
        close: 'Zatvori',
        refresh: 'Osvezi ponude',
        submit: 'Potvrdi'
      },
      table: {
        contact: 'Kontakt',
        email: 'Email',
        phone: 'Telefon',
        country: 'Drzava',
        city: 'Grad',
        address: 'Adresa',
        postCode: 'Postanski broj',
        originalPrice: 'Originalna cena',
        price: 'Cena ponude',
        emptyValue: '-'
      },
      emptyTitle: 'Jos nema ponuda',
      emptyDescription: 'Poslate ponude ce biti prikazane ovde.'
    }
  }
};
