<script setup lang="ts">
import TheExib from '@/modules/properties/components/TheExib.vue';
import { exibir } from '@/modules/properties/composables/useConfirmSolicitation.ts'
import ConfirmSolicitation from '../components/ConfirmSolicitation.vue';
</script>

<template>
  <TheExib />
  <ConfirmSolicitation v-if="exibir" />
</template>
