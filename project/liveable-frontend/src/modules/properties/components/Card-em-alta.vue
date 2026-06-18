<script setup lang="ts">
import { ref } from 'vue'
import { PhFire, PhArrowRight } from '@phosphor-icons/vue'
import { useRouter } from 'vue-router'

interface Property {
  id: number
  property_title: string
  pricePerDay: number
  avaliation: number
  images: { url: string }[]
  clicks: number
}

const props = defineProps<{ casa: Property }>()

const router = useRouter()
const imagemCarregada = ref(false)

function goToDetails() {
  router.push(`/property-details/${props.casa.id}`)
}
</script>

<template>
  <div class="card-container">
    <img
      v-if="casa.images?.[0]?.url"
      :src="casa.images[0].url"
      @load="imagemCarregada = true"
      style="display: none"
    />

    <div
      class="all"
      :class="{ 'skeleton-bg': !imagemCarregada }"
      :style="
        casa.images?.[0]?.url && imagemCarregada
          ? { backgroundImage: `url('${encodeURI(casa.images[0].url)}')` }
          : {}
      "
    >
      <div v-if="imagemCarregada" class="informs">
        <div class="informs-texts">
          <h3>{{ casa?.property_title }}</h3>
          <div class="subs">
            <p class="subtitle opacity">
              R${{ casa?.pricePerDay }} p/ dia • ★ {{ casa?.avaliation }}
            </p>
          </div>
        </div>

        <button @click="goToDetails">
          <PhArrowRight weight="bold" :size="26" />
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

.card-container {
  width: 100%;
}

.all {
  width: 100%;
  min-height: 350px;
  border-radius: 30px;
  display: flex;
  align-items: flex-end;
  contain: paint;
  font-family: 'Poppins', sans-serif;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  color: var(--color-primary-text);
  background-size: cover;
  background-position: center;
  transition: background-image 0.4s ease-in-out;
}

.skeleton-bg {
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite linear;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.informs {
  width: 100%;
  min-height: 120px;
  background: var(--color-overlay);
  backdrop-filter: blur(2px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  padding: 0 25px;
  align-items: center;
  border-radius: 22px;
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to   { opacity: 1; transform: translateY(0); }
}

.informs-texts {
  display: flex;
  flex-direction: column;
  gap: 5px;
  font-size: 18px;
}

.informs-texts p,
h3 { margin: 0; }

.informs-texts .fire { color: var(--color-primary); }

.subs {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.subtitle {
  font-size: 14px;
  font-weight: 700;
}

button {
  width: 4rem;
  max-height: 4rem;
  aspect-ratio: 1 / 1;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  background-color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary-text);
  transition: background-color 0.5s;
}

button:hover { background-color: var(--color-primary-hover); }

.opacity {
  opacity: 0.7;
  font-weight: 600;
}
</style>
