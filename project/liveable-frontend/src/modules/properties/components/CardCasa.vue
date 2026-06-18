<script setup lang="ts">
import { ref } from 'vue'
import { PhHeart } from '@phosphor-icons/vue'
import { useRouter } from 'vue-router'
import { useFavorites } from '@/modules/favorites/composables/useFavorites'

interface Property {
  id: number
  property_title: string
  pricePerDay: number
  avaliation: number
  images: { url: string }[]
}

defineProps<{ casa: Property }>()

const router = useRouter()
const { isFavorite, toggleFavorite } = useFavorites()

const imagemCarregada = ref(false)

function goToDetails(id: number) {
  router.push(`/property-details/${id}`)
}

async function handleFav(e: Event, id: number) {
  e.stopPropagation()
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }
  await toggleFavorite(id)
}
</script>

<template>
  <div class="card">
    <img
      v-if="casa.images?.[0]?.url"
      :src="casa.images[0].url"
      @load="imagemCarregada = true"
      style="display: none"
    />

    <div
      class="cima"
      :class="{ 'skeleton-loading': !imagemCarregada }"
      @click="goToDetails(casa.id)"
      :style="
        casa.images?.[0]?.url && imagemCarregada
          ? { backgroundImage: `url('${encodeURI(casa.images[0].url)}')` }
          : {}
      "
    >
      <div
        v-if="imagemCarregada"
        class="fav"
        @click="handleFav($event, casa.id)"
        :class="{ ativo: isFavorite(casa.id) }"
      >
        <PhHeart weight="fill" class="icon-fav" :size="20" />
      </div>
    </div>

    <div class="baixo">
      <div class="textos">
        <p class="titulo">{{ casa?.property_title }}</p>
        <div class="subtexto">
          <p>R${{ casa?.pricePerDay }} p/ dia</p>
          <p>•</p>
          <p>★ {{ casa?.avaliation }}</p>
        </div>
      </div>
      <div class="actions" @click="goToDetails(casa.id)">
        <button class="btn-confirm">Ver mais</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

.card {
  width: 300px;
  background-color: var(--color-bg-secondary, #ffffff);
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  font-family: 'Poppins', sans-serif;
  box-shadow: var(--shadow-sm, 0 4px 16px rgba(0, 0, 0, 0.1));
  color: var(--color-black-text, #1a1a1a);
  overflow: hidden;
}

.cima {
  width: 100%;
  height: 300px;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
  border-radius: 20px;
  position: relative;
  cursor: pointer;
  transition: background-image 0.3s ease-in-out;
}

.skeleton-loading {
  background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite linear;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.fav {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 36px;
  height: 36px;
  background-color: #fff;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  transition:
    box-shadow 0.3s,
    transform 0.2s;
  cursor: pointer;
}

.fav:hover {
  box-shadow: var(--shadow-hover-blue);
  transform: scale(1.1);
}

.icon-fav {
  color: #d1d5db;
  transition: color 0.2s;
}

.fav.ativo .icon-fav {
  color: var(--color-primary);
}

.baixo {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 15px;
}

.textos {
  display: flex;
  flex-direction: column;
}

.titulo {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
}

.subtexto {
  opacity: 0.6;
  display: flex;
  gap: 10px;
  font-size: clamp(0.7rem, 0.81vw, 0.85rem);
}

.actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 4px;
}

.btn-confirm {
  width: 100%;
  height: 42px;
  border-radius: 14px;
  cursor: pointer;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 14px;
  background-color: var(--color-primary, #3b82f6);
  color: var(--color-primary-text, #ffffff);
  transition: background-color 0.3s;
}

.btn-confirm:hover {
  background-color: var(--color-primary-hover, #2563eb);
}
</style>
