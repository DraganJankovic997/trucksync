<script setup>
import RegisterForm from '@/components/register/RegisterForm.vue'
</script>

<template>
  <q-page class="register-page">
    <div class="shell">
      <section class="intro" aria-labelledby="register-page-title">
        <div class="mark" aria-hidden="true">
          <q-icon name="local_shipping" />
        </div>

        <p class="eyebrow">TruckSync</p>
        <h1 id="register-page-title">Create account</h1>
        <p class="copy">
          Set up your account to start coordinating dispatch work with a clean
          shared workspace.
        </p>
      </section>

      <section class="form-area" aria-label="Create a new account">
        <RegisterForm />
      </section>
    </div>
  </q-page>
</template>
