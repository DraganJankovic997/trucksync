export default {
  title: 'Approval page',
  eyebrow: 'Admin kontrole',
  actions: {
    refresh: 'Osvezi'
  },
  table: {
    actions: 'Akcije',
    approve: 'Odobri',
    block: 'Blokiraj',
    approveAria: 'Odobri {name}',
    blockAria: 'Blokiraj {name}',
    profileId: 'ID profila',
    userId: 'ID korisnika',
    firstName: 'Ime',
    lastName: 'Prezime',
    email: 'Email',
    phoneNumber: 'Telefon',
    country: 'Drzava',
    city: 'Grad',
    address: 'Adresa',
    postCode: 'Postanski broj',
    emptyValue: '-'
  },
  dispatchers: {
    title: 'Dispeceri',
    count: 'Dispecera na cekanju: {count}',
    companyName: 'Kompanija',
    registrationNumber: 'Registracioni broj',
    emptyTitle: 'Nema dispecera za odobravanje',
    emptyDescription:
      'Profili dispecera koji cekaju odobrenje ce se pojaviti ovde.'
  },
  restStops: {
    title: 'Odmorista',
    count: 'Odmorista na cekanju: {count}',
    worksFrom: 'Radi od',
    worksTo: 'Radi do',
    emptyTitle: 'Nema odmorista za odobravanje',
    emptyDescription:
      'Profili odmorista koji cekaju odobrenje ce se pojaviti ovde.'
  }
};
