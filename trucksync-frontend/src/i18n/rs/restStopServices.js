export default {
  title: 'Usluge mog odmorista',
  eyebrow: 'Usluge profila',
  description: 'Izaberite koje usluge su dostupne na vasem odmoristu.',
  actions: {
    refresh: 'Osvezi',
    completeProfile: 'Otvori profil'
  },
  profileMissing: {
    title: 'Potreban je profil odmorista',
    description: 'Kreirajte profil odmorista pre dodavanja usluga.'
  },
  form: {
    title: 'Dodaj uslugu',
    ariaLabel: 'Forma za dodavanje usluge odmorista',
    service: {
      label: 'Usluga',
      placeholder: 'Izaberite uslugu'
    },
    noOptions: 'Sve dostupne usluge su vec dodate.',
    submit: 'Dodaj uslugu'
  },
  table: {
    title: 'Izabrane usluge',
    serviceCount: 'Izabrane usluge: {count}',
    name: 'Naziv',
    actions: 'Akcije',
    remove: 'Ukloni',
    removeAria: 'Ukloni {name}',
    emptyTitle: 'Nema izabranih usluga',
    emptyDescription:
      'Dodajte usluge koje vozaci mogu da pronadju na ovom odmoristu.'
  },
  removeDialog: {
    title: 'Ukloniti uslugu?',
    message: 'Ukloniti "{name}" sa vaseg odmorista?',
    warning: 'Usluga ostaje dostupna u katalogu.',
    fallbackName: 'ovu uslugu',
    cancel: 'Odustani',
    confirm: 'Ukloni uslugu'
  }
};
