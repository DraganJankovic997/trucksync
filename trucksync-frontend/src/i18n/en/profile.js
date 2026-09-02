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
  submit: 'Save changes'
};
