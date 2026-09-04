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
      title: 'Dispatcher profile'
    },
    restStop: {
      title: 'Rest stop profile'
    }
  },
  submit: 'Save changes'
};
