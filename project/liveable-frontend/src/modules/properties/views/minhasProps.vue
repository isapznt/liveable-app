<script setup lang="ts">
import CardCasa from '../components/CardCasa.vue';
import OrganizationCardMinhasprops from '../components/organization-card-minhasprops.vue';
import TheCreator from '../components/theCreator.vue';
import { exibir, exibirConfirm } from '@/modules/properties/composables/useConfirmSolicitation.ts'
</script>

<template>
  <div class="container-organization">
    <div class="organization">
        <div class="escrita">
          <p class="title">Meus <span>Imóveis</span></p>
          <div class="button-circle" @click="exibirConfirm">
            +
          </div>
        </div>
      </div>
    <OrganizationCardMinhasprops />
    <TheCreator v-if="exibir" />
  </div>
</template>

<style scoped>
.container-organization {
  width: 100%;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  font-family: 'Poppins', sans-serif;
  padding: 2rem 0;
  color: var(--color-black-text);
}

.organization-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.escrita {
  display: flex;
  align-items: center;
  gap: 0.7rem;
}

.button-circle {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: var(--color-primary, #3b82f6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  font-weight: 500;
  cursor: pointer;
  flex-shrink: 0;
}

p {
  position: relative;
  display: inline-block;
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0;
  color: var(--color-black-text);
}

p span {
  color: var(--color-primary, #3b82f6);
}

.title::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: -3px;
  height: 3px;
  width: 75%;
  background-color: var(--color-primary, #3b82f6);
  border-radius: 15px;
}

.title::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: -3px;
  height: 3px;
  width: 20%;
  background-color: var(--color-primary, #3b82f6);
  border-radius: 15px;
}

@media (max-width: 768px) {
  .container-organization {
    grid-template-columns: 1fr;

    justify-items: center;
  }
}
</style>
