export default {
  title: 'Profil',
  formAriaLabel: 'Azuriranje profila',
  form: {
    title: 'Detalji profila'
  },
  fields: {
    firstName: {
      label: 'Ime',
      placeholder: 'Jana'
    },
    lastName: {
      label: 'Prezime',
      placeholder: 'Kuper'
    },
    email: {
      label: 'Email',
      placeholder: "name{'@'}example.com"
    },
    country: {
      label: 'Drzava',
      placeholder: 'Srbija'
    },
    phoneNumber: {
      label: 'Broj telefona',
      placeholder: '+381601234567'
    },
    profileType: {
      label: 'Tip profila'
    }
  },
  profileTypes: {
    driver: 'Vozac',
    dispatcher: 'Dispecer',
    restStop: 'Odmoriste'
  },
  submit: 'Sacuvaj izmene'
};
