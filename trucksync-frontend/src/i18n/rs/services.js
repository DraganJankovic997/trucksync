export default {
  title: 'Usluge',
  eyebrow: 'Admin kontrole',
  description: 'Upravljajte opcijama usluga dostupnim u TruckSync-u.',
  serviceCount: 'Ukupno usluga: {count}',
  actions: {
    refresh: 'Osvezi'
  },
  form: {
    title: 'Dodaj uslugu',
    ariaLabel: 'Forma za kreiranje usluge',
    name: {
      label: 'Naziv usluge',
      placeholder: 'Parking, tus, popravka...'
    },
    submit: 'Dodaj uslugu'
  },
  table: {
    title: 'Sve usluge',
    id: 'ID',
    name: 'Naziv',
    actions: 'Akcije',
    delete: 'Obrisi',
    deleteAria: 'Obrisi {name}',
    emptyTitle: 'Nema usluga',
    emptyDescription: 'Dodajte prvu uslugu da bi bila dostupna.'
  },
  deleteDialog: {
    title: 'Obrisati uslugu?',
    message: 'Obrisati "{name}" iz liste usluga?',
    warning: 'Ova radnja ne moze da se opozove.',
    fallbackName: 'ovu uslugu',
    cancel: 'Odustani',
    confirm: 'Obrisi uslugu'
  }
};
