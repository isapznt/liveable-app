<script setup lang="ts">
import LoginBanner from '../components/loginBanner.vue'
import LoginInputs from '../components/loginInputs.vue'
</script>

<template>
  <div class="container-large">
    <div class="banner"><LoginBanner /></div>
    <LoginInputs />
  </div>
</template>

<style scoped>
.container-large {
  display: flex;
  justify-content: space-around;

  width: 100%;
  min-height: 90vh;
}

@media (max-width: 768px) {
  .container-large {
    flex-direction: column;
    width: 90%;
  }

  .banner {
    display: none;
  }
}
</style>
