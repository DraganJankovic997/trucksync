export default {
  title: 'Rute dispecera',
  eyebrow: 'Upravljanje rutama',
  routeCount: 'Rute: {count}',
  actions: {
    create: 'Nova ruta',
    refresh: 'Osvezi'
  },
  form: {
    ariaLabel: 'Kreiranje rute dispecera',
    title: 'Nova ruta',
    actions: {
      cancel: 'Otkazi',
      create: 'Kreiraj rutu'
    },
    fields: {
      origin: {
        label: 'Polaziste',
        placeholder: 'Unesite polaziste rute'
      },
      destination: {
        label: 'Odrediste',
        placeholder: 'Unesite odrediste rute'
      },
      convoySize: {
        label: 'Velicina konvoja',
        placeholder: 'Unesite velicinu konvoja'
      },
      startDate: {
        label: 'Datum pocetka'
      },
      endDate: {
        label: 'Datum zavrsetka'
      },
      plannedTravelDetails: {
        label: 'Planirani detalji puta',
        placeholder: 'Dodajte napomene za planiranu rutu'
      }
    }
  },
  table: {
    title: 'Kreirane rute',
    id: 'ID',
    origin: 'Polaziste',
    destination: 'Odrediste',
    convoySize: 'Velicina konvoja',
    startDate: 'Datum pocetka',
    endDate: 'Datum zavrsetka',
    closedAt: 'Zatvoreno',
    plannedTravelDetails: 'Planirani detalji puta',
    open: 'Otvorena',
    emptyValue: '-',
    emptyTitle: 'Nema kreiranih ruta',
    emptyDescription: 'Kreirane rute ce biti prikazane ovde.'
  }
};
