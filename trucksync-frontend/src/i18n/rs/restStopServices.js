export default {
  title: 'Usluge mog odmorišta',
  eyebrow: 'Usluge profila',
  description: 'Izaberite koje usluge su dostupne na vašem odmorištu.',
  actions: {
    refresh: 'Osveži'
  },
  form: {
    title: 'Dodaj uslugu',
    ariaLabel: 'Forma za dodavanje usluge odmorišta',
    service: {
      label: 'Usluga',
      placeholder: 'Izaberite uslugu'
    },
    pricePerUnit: {
      label: 'Cena po jedinici',
      placeholder: '0.00',
      decimal: 'Cena po jedinici mora imati najviše 2 decimale'
    },
    noOptions: 'Sve dostupne usluge su već dodate.',
    submit: 'Dodaj uslugu'
  },
  table: {
    title: 'Izabrane usluge',
    serviceCount: 'Izabrane usluge: {count}',
    name: 'Naziv',
    measurementUnit: 'Jedinica mere',
    pricePerUnit: 'Cena po jedinici',
    actions: 'Akcije',
    remove: 'Ukloni',
    removeAria: 'Ukloni {name}',
    emptyTitle: 'Nema izabranih usluga',
    emptyDescription:
      'Dodajte usluge koje vozači mogu da pronađu na ovom odmorištu.'
  },
  removeDialog: {
    title: 'Ukloniti uslugu?',
    message: 'Ukloniti "{name}" sa vašeg odmorišta?',
    warning: 'Usluga ostaje dostupna u katalogu.',
    fallbackName: 'ovu uslugu',
    cancel: 'Odustani',
    confirm: 'Ukloni uslugu'
  }
};
