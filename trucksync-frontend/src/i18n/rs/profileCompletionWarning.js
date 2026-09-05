export default {
  action: 'Otvori profil',
  roles: {
    driver: {
      title: 'Vozac profil',
      description: 'profil vozaca'
    },
    dispatcher: {
      title: 'Dispecer profil',
      description: 'profil dispecera'
    },
    restStop: {
      title: 'Odmoriste profil',
      description: 'profil odmorista'
    },
    fallback: {
      title: 'Profil uloge',
      description: 'profil uloge'
    }
  },
  missingProfileAndRole: {
    title: 'Potrebno je podesavanje profila',
    description: 'Popunite informacije o profilu i {roleProfile} pre nastavka.'
  },
  missingProfileInformation: {
    title: 'Potrebne su informacije o profilu',
    description: 'Popunite informacije o profilu pre nastavka.'
  },
  missingRoleProfile: {
    title: 'Potreban je {roleProfileTitle}',
    description: 'Kreirajte {roleProfile} pre nastavka.'
  }
};
