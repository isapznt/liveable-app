<script setup lang="ts">
import { onMounted, ref } from 'vue'

const theme = ref<'light' | 'dark'>('light')

onMounted(() => {
  const saved = localStorage.getItem('theme') as 'light' | 'dark' | null
  theme.value = saved ?? 'light'
  document.documentElement.setAttribute('data-theme', theme.value)
})

function setTheme(t: 'light' | 'dark') {
  theme.value = t
  document.documentElement.setAttribute('data-theme', t)
  localStorage.setItem('theme', t)
}

// Exporta para uso nos filhos via provide (opcional)
// provide('theme', { theme, setTheme })
</script>

<template>
  <router-view />
</template>
