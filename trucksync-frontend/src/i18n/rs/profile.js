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
  typeForms: {
    driver: {
      title: 'Vozac profil',
      formAriaLabel: 'Azuriranje profila vozaca',
      fields: {
        licenseNumber: {
          label: 'Broj vozacke dozvole',
          placeholder: 'DL-123456'
        },
        dispatcherId: {
          label: 'ID dispecera',
          placeholder: 'Opciono'
        }
      }
    },
    dispatcher: {
      title: 'Dispecer profil',
      formAriaLabel: 'Azuriranje profila dispecera',
      fields: {
        companyName: {
          label: 'Naziv kompanije',
          placeholder: 'Acme Dispatch'
        },
        city: {
          label: 'Grad',
          placeholder: 'Beograd'
        },
        address: {
          label: 'Adresa',
          placeholder: 'Glavna ulica 1'
        },
        postCode: {
          label: 'Postanski broj',
          placeholder: '11000'
        },
        registrationNumber: {
          label: 'Registracioni broj',
          placeholder: 'REG-1234'
        }
      }
    },
    restStop: {
      title: 'Odmoriste profil',
      formAriaLabel: 'Azuriranje profila odmorista',
      fields: {
        city: {
          label: 'Grad',
          placeholder: 'Beograd'
        },
        address: {
          label: 'Adresa',
          placeholder: 'Autoput 1'
        },
        postCode: {
          label: 'Postanski broj',
          placeholder: '11000'
        },
        worksFrom: {
          label: 'Radi od'
        },
        worksTo: {
          label: 'Radi do'
        }
      }
    }
  },
  submit: 'Sacuvaj izmene'
};
