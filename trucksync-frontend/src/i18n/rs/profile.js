export default {
  title: 'Profil',
  formAriaLabel: 'Ažuriranje profila',
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
      label: 'Država',
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
    driver: 'Vozač',
    dispatcher: 'Dispečer',
    restStop: 'Odmorište'
  },
  typeForms: {
    driver: {
      title: 'Profil vozača',
      formAriaLabel: 'Ažuriranje profila vozača',
      fields: {
        licenseNumber: {
          label: 'Broj vozačke dozvole',
          placeholder: 'DL-123456'
        },
        dispatcherId: {
          label: 'ID dispečera',
          placeholder: 'Opciono'
        }
      }
    },
    dispatcher: {
      title: 'Profil dispečera',
      formAriaLabel: 'Ažuriranje profila dispečera',
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
          label: 'Poštanski broj',
          placeholder: '11000'
        },
        registrationNumber: {
          label: 'Registracioni broj',
          placeholder: 'REG-1234'
        }
      }
    },
    restStop: {
      title: 'Profil odmorišta',
      formAriaLabel: 'Ažuriranje profila odmorišta',
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
          label: 'Poštanski broj',
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
  submit: 'Sačuvaj izmene'
};
