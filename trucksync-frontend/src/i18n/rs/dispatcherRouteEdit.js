export default {
  title: 'Izmena rute: {route_id} - Izmeni rutu',
  actions: {
    back: 'Nazad na rute'
  },
  details: {
    title: 'Detalji rute',
    origin: 'Polaziste',
    destination: 'Odrediste',
    convoySize: 'Velicina konvoja',
    startDate: 'Datum pocetka',
    endDate: 'Datum zavrsetka',
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
      services: 'Usluge',
      emptyValue: '-',
      emptyTitle: 'Nema dodatih stajalista',
      emptyDescription: 'Stajalista rute ce biti prikazana ovde.'
    }
  }
};
