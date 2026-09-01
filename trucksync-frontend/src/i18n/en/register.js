export default {
  title: 'Create account',
  description:
    'Set up your account to start coordinating dispatch work with a clean shared workspace.',
  formAriaLabel: 'Create a new account',
  form: {
    title: 'Account details'
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
    password: {
      label: 'Password',
      placeholder: 'Minimum 8 characters'
    },
    passwordConfirmation: {
      label: 'Confirm password',
      placeholder: 'Repeat your password'
    }
  },
  submit: 'Create account'
};
