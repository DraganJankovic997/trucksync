<template>
  <q-page class="register-page">
    <div class="register-page__shell">
      <section
        class="register-page__intro"
        aria-labelledby="register-page-title"
      >
        <div class="register-page__mark" aria-hidden="true">
          <q-icon name="local_shipping" />
        </div>

        <p class="register-page__eyebrow">TruckSync</p>
        <h1 id="register-page-title">Create account</h1>
        <p class="register-page__copy">
          Set up your account to start coordinating dispatch work with a clean
          shared workspace.
        </p>
      </section>

      <section
        class="register-page__form-panel"
        aria-label="Create a new account"
      >
        <RegisterForm />
      </section>
    </div>
  </q-page>
</template>

<script setup>
import RegisterForm from '@/components/register/RegisterForm.vue'
</script>
