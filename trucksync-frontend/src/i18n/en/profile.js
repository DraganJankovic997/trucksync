export default {
  title: 'Profile',
  formAriaLabel: 'Update profile',
  form: {
    title: 'Profile details'
  },
  fields: {
    firstName: {
      label: 'First name',
      placeholder: 'Jane'
    },
    lastName: {
      label: 'Last name',
      placeholder: 'Cooper'
    },
    email: {
      label: 'Email',
      placeholder: "name{'@'}example.com"
    },
    country: {
      label: 'Country',
      placeholder: 'Serbia'
    },
    phoneNumber: {
      label: 'Phone number',
      placeholder: '+381601234567'
    },
    profileType: {
      label: 'Profile type'
    }
  },
  profileTypes: {
    driver: 'Driver',
    dispatcher: 'Dispatcher',
    restStop: 'Rest stop'
  },
  typeForms: {
    driver: {
      title: 'Driver profile',
      formAriaLabel: 'Update driver profile',
      fields: {
        licenseNumber: {
          label: 'License number',
          placeholder: 'DL-123456'
        },
        dispatcherId: {
          label: 'Dispatcher ID',
          placeholder: 'Optional'
        }
      }
    },
    dispatcher: {
      title: 'Dispatcher profile',
      formAriaLabel: 'Update dispatcher profile',
      fields: {
        companyName: {
          label: 'Company name',
          placeholder: 'Acme Dispatch'
        },
        country: {
          label: 'Country',
          placeholder: 'Serbia'
        },
        city: {
          label: 'City',
          placeholder: 'Belgrade'
        },
        address: {
          label: 'Address',
          placeholder: 'Main Street 1'
        },
        postCode: {
          label: 'Post code',
          placeholder: '11000'
        },
        registrationNumber: {
          label: 'Registration number',
          placeholder: 'REG-1234'
        }
      }
    },
    restStop: {
      title: 'Rest stop profile'
    }
  },
  submit: 'Save changes'
};
