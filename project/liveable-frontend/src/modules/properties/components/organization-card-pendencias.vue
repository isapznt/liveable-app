<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PendingRentCard from '@/modules/properties/components/Card-imoveis-pendencia.vue'

const reservas = ref<any[]>([])
const carregando = ref(true)
const erro = ref<string | null>(null)

onMounted(async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/my-properties/pending-rents', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    })
    if (!response.ok) throw new Error(`Erro ${response.status}`)
    reservas.value = await response.json()
  } catch (e: any) {
    erro.value = e.message
  } finally {
    carregando.value = false
  }
})

async function handleConfirm(rentId: number) {
  await updateStatus(rentId, true)
}

async function handleReject(rentId: number) {
  await updateStatus(rentId, false)
}

async function updateStatus(rentId: number, confirmed: boolean) {
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/rents/${rentId}/status`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
      body: JSON.stringify({ confirmed }),
    })
    if (!response.ok) throw new Error(`Erro ${response.status}`)
    reservas.value = reservas.value.filter((r) => r.rent_id !== rentId)
  } catch (e: any) {
    console.error('[updateStatus]', e)
  }
}
</script>

<template>
  <div class="pendentes-wrapper">
    <p v-if="carregando" class="estado">Carregando...</p>
    <p v-else-if="erro" class="estado erro">{{ erro }}</p>
    <p v-else-if="reservas.length === 0" class="estado">Nenhuma solicitação pendente.</p>

    <div v-else class="cards-grid">
      <PendingRentCard
        v-for="rent in reservas"
        :key="rent.rent_id"
        :rent="rent"
        @confirm="handleConfirm"
        @reject="handleReject"
      />
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

.pendentes-wrapper {
  font-family: 'Poppins', sans-serif;
}

.titulo {
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0 0 1.5rem;
  color: var(--color-black-text, #1a1a1a);
}

.estado {
  font-size: 14px;
  opacity: 0.6;
}

.estado.erro {
  color: #dc2626;
  opacity: 1;
}

.cards-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}
</style>
