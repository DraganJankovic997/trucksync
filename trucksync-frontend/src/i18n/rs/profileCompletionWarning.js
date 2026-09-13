export default {
  action: 'Otvori profil',
  roles: {
    driver: {
      title: 'Profil vozača',
      description: 'profil vozača'
    },
    dispatcher: {
      title: 'Profil dispečera',
      description: 'profil dispečera'
    },
    restStop: {
      title: 'Profil odmorišta',
      description: 'profil odmorišta'
    },
    fallback: {
      title: 'Profil uloge',
      description: 'profil uloge'
    }
  },
  missingProfileAndRole: {
    title: 'Potrebno je podešavanje profila',
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
