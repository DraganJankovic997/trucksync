export default {
  title: 'Kreiraj nalog',
  description:
    'Podesite nalog i počnite da koordinirate dispečerski posao u čistom zajedničkom radnom prostoru.',
  formAriaLabel: 'Kreirajte novi nalog',
  form: {
    title: 'Detalji naloga'
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
    password: {
      label: 'Lozinka',
      placeholder: 'Najmanje 8 karaktera'
    },
    passwordConfirmation: {
      label: 'Potvrda lozinke',
      placeholder: 'Ponovite lozinku'
    }
  },
  submit: 'Kreiraj nalog'
};
