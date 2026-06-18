<script setup lang="ts">
import { ref, onMounted } from 'vue'
import CardCasa from './CardCasa.vue'
import { getToken } from '@/services/auth'
import TheCreator from '../components/theCreator.vue';
import { exibir, exibirConfirm } from '@/modules/properties/composables/useConfirmSolicitation.ts'

interface Property {
  id: number
  property_title: string
  pricePerDay: number
  avaliation: number
  images: { url: string }[]
}

const properties = ref<Property[]>([])
const loading = ref(true)
const erro = ref(false)

onMounted(async () => {
  try {
    const token = getToken()

    const res = await fetch('http://127.0.0.1:8000/api/my-properties', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    })

    if (!res.ok) throw new Error()

    properties.value = await res.json()
  } catch (error) {
    console.error('[OrganizationView]', error)
    erro.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="organization">
    <div v-if="loading" class="estado">
      <p>Carregando...</p>
    </div>

    <div v-else-if="erro" class="estado erro">
      <p>Erro ao carregar propriedades. Tente novamente mais tarde.</p>
    </div>

    <div v-else-if="properties.length === 0" class="estado vazio">
      <div class="vazio-icon">🏠</div>
      <p class="vazio-titulo">Nenhum imóvel cadastrado</p>
      <p class="vazio-sub">Você ainda não tem nenhuma propriedade listada na plataforma.</p>
      <button class="btn-anunciar" @click="exibirConfirm">Anunciar Imóvel</button>
    </div>

    <div v-else class="grid">
      <CardCasa v-for="property in properties" :key="property.id" :casa="property" />
    </div>

    <TheCreator v-if="exibir" />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.organization {
  width: 100%;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  font-family: 'Poppins', sans-serif;
  padding: 2rem 0;
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
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background-color: var(--color-primary, #3b82f6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  flex-shrink: 0;
}

p {
  position: relative;
  display: inline-block;
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0;
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

.grid {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
}

.estado {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  font-size: 1rem;
  opacity: 0.5;
}

.vazio {
  flex-direction: column;
  gap: 10px;
  opacity: 1;
}

.vazio-icon {
  font-size: 3.5rem;
  line-height: 1;
  color: var(--color-primary, #3b82f6);
  opacity: 0.4;
}

.vazio-titulo {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--color-black-text, #1a1a1a);
}

.vazio-sub {
  margin: 0;
  opacity: 0.55;
  font-size: 0.9rem;
  text-align: center;
}

.btn-anunciar {
  margin-top: 8px;
  padding: 0.7rem 2rem;
  border-radius: 14px;
  border: 1.5px solid var(--color-primary, #3b82f6);
  background: transparent;
  color: var(--color-primary, #3b82f6);
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition:
    background 0.18s,
    color 0.18s;
}

.btn-anunciar:hover {
  background: var(--color-primary, #3b82f6);
  color: #fff;
}

.erro {
  color: #ef4444;
  opacity: 0.8;
  font-weight: 500;
}

@media (max-width: 768px) {
  .grid {
    justify-content: center;
  }
}
</style>
