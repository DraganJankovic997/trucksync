export default {
  common: {
    success: 'Radnja je uspešno završena.',
    error: 'Nešto je pošlo naopako.'
  },
  auth: {
    registerSuccess: 'Korisnik je uspešno kreiran',
    registerError: 'Kreiranje korisnika nije uspelo',
    loginSuccess: 'Uspešno ste prijavljeni',
    loginError: 'Prijava nije uspela',
    sessionExpired: 'Autentifikacija je istekla',
    logoutSuccess: 'Uspešno ste odjavljeni',
    logoutError: 'Odjava nije uspela'
  },
  profile: {
    updateSuccess: 'Profil je uspešno ažuriran',
    updateError: 'Ažuriranje profila nije uspelo'
  },
  approval: {
    fetchError: 'Preuzimanje profila za odobravanje nije uspelo',
    approveSuccess: 'Profil je uspešno odobren',
    approveError: 'Odobravanje profila nije uspelo'
  },
  country: {
    fetchError: 'Preuzimanje zemalja nije uspelo'
  },
  driver: {
    fetchError: 'Preuzimanje profila vozača nije uspelo',
    fetchDispatcherDriversError: 'Preuzimanje vozača nije uspelo',
    saveSuccess: 'Profil vozača je uspešno sačuvan',
    saveError: 'Čuvanje profila vozača nije uspelo'
  },
  dispatcher: {
    fetchError: 'Preuzimanje profila dispečera nije uspelo',
    fetchAllError: 'Preuzimanje dispečera nije uspelo',
    saveSuccess: 'Profil dispečera je uspešno sačuvan',
    saveError: 'Čuvanje profila dispečera nije uspelo'
  },
  restStop: {
    fetchError: 'Preuzimanje profila odmorišta nije uspelo',
    saveSuccess: 'Profil odmorišta je uspešno sačuvan',
    saveError: 'Čuvanje profila odmorišta nije uspelo'
  },
  restStopService: {
    fetchError: 'Preuzimanje usluga odmorišta nije uspelo',
    addSuccess: 'Usluga je uspešno dodata odmorištu',
    addError: 'Dodavanje usluge odmorištu nije uspelo',
    removeSuccess: 'Usluga je uspešno uklonjena sa odmorišta',
    removeError: 'Uklanjanje usluge sa odmorišta nije uspelo'
  },
  bid: {
    fetchError: 'Preuzimanje ponude nije uspelo',
    fetchBidsError: 'Preuzimanje ponuda nije uspelo',
    fetchRouteStopBidsError: 'Preuzimanje ponuda za stajalište nije uspelo',
    saveSuccess: 'Ponuda je uspešno sačuvana',
    saveError: 'Čuvanje ponude nije uspelo',
    deleteSuccess: 'Ponuda je uspešno obrisana',
    deleteError: 'Brisanje ponude nije uspelo'
  },
  route: {
    fetchError: 'Preuzimanje ruta nije uspelo',
    fetchOneError: 'Preuzimanje rute nije uspelo',
    createSuccess: 'Ruta je uspešno kreirana',
    createError: 'Kreiranje rute nije uspelo',
    assignDriversSuccess: 'Vozači rute su uspešno ažurirani',
    assignDriversError: 'Ažuriranje vozača rute nije uspelo',
    closeSuccess: 'Ruta je uspešno zatvorena',
    closeError: 'Zatvaranje rute nije uspelo',
    editForbidden: 'Nemate dozvolu da izmenite ovu rutu'
  },
  routeStop: {
    fetchError: 'Preuzimanje stajališta rute nije uspelo',
    createSuccess: 'Stajalište rute je uspešno kreirano',
    createError: 'Kreiranje stajališta rute nije uspelo',
    updateServicesSuccess: 'Usluge stajališta rute su uspešno ažurirane',
    updateServicesError: 'Ažuriranje usluga stajališta rute nije uspelo',
    fulfillSuccess: 'Stajalište rute je uspešno ispunjeno',
    fulfillError: 'Ispunjavanje stajališta rute nije uspelo'
  },
  routeStopUsage: {
    fetchRatingsError: 'Preuzimanje ocena korišćenja stajališta nije uspelo',
    saveSuccess: 'Recenzija korišćenja stajališta je uspešno poslata',
    saveError: 'Slanje recenzije korišćenja stajališta nije uspelo'
  },
  service: {
    fetchError: 'Preuzimanje usluga nije uspelo',
    fetchOneError: 'Preuzimanje usluge nije uspelo',
    createSuccess: 'Usluga je uspešno kreirana',
    createError: 'Kreiranje usluge nije uspelo',
    deleteSuccess: 'Usluga je uspešno obrisana',
    deleteError: 'Brisanje usluge nije uspelo'
  }
};
