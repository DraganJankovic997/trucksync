export default {
  title: 'Rute dispečera',
  eyebrow: 'Upravljanje rutama',
  routeCount: 'Rute: {count}',
  actions: {
    create: 'Nova ruta',
    refresh: 'Osveži'
  },
  form: {
    ariaLabel: 'Kreiranje rute dispečera',
    title: 'Nova ruta',
    actions: {
      cancel: 'Otkaži',
      create: 'Kreiraj rutu'
    },
    fields: {
      origin: {
        label: 'Polazište',
        placeholder: 'Unesite polazište rute'
      },
      destination: {
        label: 'Odredište',
        placeholder: 'Unesite odredište rute'
      },
      convoySize: {
        label: 'Veličina konvoja',
        placeholder: 'Unesite veličinu konvoja'
      },
      startDate: {
        label: 'Datum početka'
      },
      endDate: {
        label: 'Datum završetka'
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
    emptyTitle: 'Nema kreiranih ruta',
    emptyDescription: 'Kreirane rute će biti prikazane ovde.'
  }
};
