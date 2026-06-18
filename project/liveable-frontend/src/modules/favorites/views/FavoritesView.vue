<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFavorites } from '../composables/useFavorites'
import CardCasa from '@/modules/properties/components/CardCasa.vue'
import { PhHeart } from '@phosphor-icons/vue'

interface Property {
  id: number
  property_title: string
  pricePerDay: number
  avaliation: number
  images: { url: string }[]
}

const router = useRouter()
const properties = ref<Property[]>([])
const carregando = ref(true)
const { carregar } = useFavorites()

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }

  try {
    const res = await fetch('http://127.0.0.1:8000/api/favorites', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    })
    properties.value = await res.json()
    await carregar()
  } catch (e) {
    console.error('[FavoritesView]', e)
  } finally {
    carregando.value = false
  }
})
</script>

<template>
  <div class="favorites">
    <div class="favorites-header">
      <div class="escrita">
        <p class="title">Meus <span>Favoritos</span></p>
        <div class="button-circle">
          <PhHeart :size="14" weight="fill" />
        </div>
      </div>
    </div>

    <div v-if="carregando" class="estado">
      <p>Carregando...</p>
    </div>

    <div v-else-if="properties.length === 0" class="estado vazio">
      <div class="vazio-icon">♡</div>
      <p class="vazio-titulo">Nenhum favorito ainda</p>
      <p class="vazio-sub">Clique no coração de um imóvel para salvá-lo aqui.</p>
      <button class="btn-explorar" @click="router.push('/')">Explorar imóveis</button>
    </div>

    <div v-else class="grid">
      <CardCasa v-for="p in properties" :key="p.id" :casa="p" />
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.favorites {
  width: 100%;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  font-family: 'Poppins', sans-serif;
  padding: 2rem 0;
  color: var(--color-black-text);
}

.favorites-header {
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
  background-color: var(--color-primary);
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
  color: var(--color-primary);
}

.title::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: -3px;
  height: 3px;
  width: 75%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

.title::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: -3px;
  height: 3px;
  width: 20%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

/* ── Grid ── */
.grid {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
}

/* ── Estados ── */
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
  color: var(--color-primary);
  opacity: 0.4;
}

.vazio-titulo {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--color-black-text);
}

.vazio-sub {
  margin: 0;
  opacity: 0.55;
  font-size: 0.9rem;
}

.btn-explorar {
  margin-top: 8px;
  padding: 0.7rem 2rem;
  border-radius: 14px;
  border: 1.5px solid #1a2fa8;
  background: transparent;
  color: #1a2fa8;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition:
    background 0.18s,
    color 0.18s;
}

.btn-explorar:hover {
  background: #1a2fa8;
  color: #fff;
}

@media (max-width: 768px) {
  .grid {
    justify-content: center;
  }
}
</style>
