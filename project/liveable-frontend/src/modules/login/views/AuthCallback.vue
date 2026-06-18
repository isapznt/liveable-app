<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { setToken } from '@/services/auth.js'

const router = useRouter()

onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const token = params.get('token')
  const error = params.get('error')

  if (token) {
    setToken(token)
    router.push('/') // troca pelo caminho da sua página principal
  } else {
    console.error('Erro no login social:', error)
    router.push('/login')
  }
})
</script>

<template>
  <div>Autenticando...</div>
</template>
