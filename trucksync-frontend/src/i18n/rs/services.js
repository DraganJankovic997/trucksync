export default {
  title: 'Usluge',
  eyebrow: 'Admin kontrole',
  description: 'Upravljajte opcijama usluga dostupnim u TruckSync-u.',
  serviceCount: 'Ukupno usluga: {count}',
  actions: {
    refresh: 'Osveži'
  },
  form: {
    title: 'Dodaj uslugu',
    ariaLabel: 'Forma za kreiranje usluge',
    name: {
      label: 'Naziv usluge',
      placeholder: 'Parking, tuš, popravka...'
    },
    measurementUnit: {
      label: 'Jedinica mere',
      placeholder: 'kamion, vozač, sat...'
    },
    submit: 'Dodaj uslugu'
  },
  table: {
    title: 'Sve usluge',
    id: 'ID',
    name: 'Naziv',
    measurementUnit: 'Jedinica mere',
    actions: 'Akcije',
    delete: 'Obriši',
    deleteAria: 'Obriši {name}',
    emptyTitle: 'Nema usluga',
    emptyDescription: 'Dodajte prvu uslugu da bi bila dostupna.'
  },
  deleteDialog: {
    title: 'Obrisati uslugu?',
    message: 'Obrisati "{name}" iz liste usluga?',
    warning: 'Ova radnja ne može da se opozove.',
    fallbackName: 'ovu uslugu',
    cancel: 'Odustani',
    confirm: 'Obriši uslugu'
  }
};
