export default {
  action: 'Open profile',
  roles: {
    driver: {
      title: 'Driver profile',
      description: 'driver profile'
    },
    dispatcher: {
      title: 'Dispatcher profile',
      description: 'dispatcher profile'
    },
    restStop: {
      title: 'Rest stop profile',
      description: 'rest stop profile'
    },
    fallback: {
      title: 'Role profile',
      description: 'role profile'
    }
  },
  missingProfileAndRole: {
    title: 'Profile setup required',
    description:
      'Complete your profile information and {roleProfile} before continuing.'
  },
  missingProfileInformation: {
    title: 'Profile information required',
    description: 'Complete your profile information before continuing.'
  },
  missingRoleProfile: {
    title: '{roleProfileTitle} required',
    description: 'Create your {roleProfile} before continuing.'
  }
};
