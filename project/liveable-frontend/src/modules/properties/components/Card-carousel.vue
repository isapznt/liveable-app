<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/navigation'
import { Navigation } from 'swiper/modules'

import CardCasa from './CardCasa.vue'
import CardCasaSkeleton from './CardCasaSkeleton.vue'

const router = useRouter()

const prevButton = ref(null)
const nextButton = ref(null)

const onSwiper = (swiper: any) => {
  setTimeout(() => {
    if (swiper.params.navigation && typeof swiper.params.navigation !== 'boolean') {
      swiper.params.navigation.prevEl = prevButton.value
      swiper.params.navigation.nextEl = nextButton.value
    }
    swiper.navigation.destroy()
    swiper.navigation.init()
    swiper.navigation.update()
  })
}

const carregando = ref<boolean>(true)

interface Property {
  id: number
  property_title: string
  pricePerDay: number
  avaliation: number
  images: { url: string }[]
}

const properties = ref<Property[]>([])

onMounted(async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/properties')
    const data = await response.json()
    properties.value = data
  } catch (error) {
    console.error(error)
  } finally {
    carregando.value = false
  }
})

function verTodas() {
  router.push('/imoveis')
}
</script>

<template>
  <div class="carousel-wrapper">
    <div class="escrita-cima">
      <div class="escrita">
        <p>Sugestões para <span>Você</span></p>
        <!-- Botão que vai pra /imoveis sem filtro -->
        <div class="button-circle" @click="verTodas">
          <i class="fa-solid fa-angle-right"></i>
        </div>
      </div>

      <div class="arrows">
        <button ref="prevButton" class="custom-prev">
          <PhCaretLeft :size="32" />
        </button>
        <button ref="nextButton" class="custom-next">
          <PhCaretRight :size="32" />
        </button>
      </div>
    </div>

    <Swiper
      :modules="[Navigation]"
      :slides-per-view="1"
      :space-between="24"
      :loop="true"
      :breakpoints="{
        480: { slidesPerView: 1.7, spaceBetween: 16 },
        768: { slidesPerView: 2.8, spaceBetween: 20 },
        1280: { slidesPerView: 3.9, spaceBetween: 24 },
        1600: { slidesPerView: 5, spaceBetween: 24 },
      }"
      @swiper="onSwiper"
      class="mySwiper"
    >
      <template v-if="carregando">
        <SwiperSlide v-for="n in 6" :key="n">
          <CardCasaSkeleton />
        </SwiperSlide>
      </template>

      <template v-else>
        <SwiperSlide v-for="casa in properties" :key="casa.id">
          <CardCasa v-if="casa" :casa="casa" />
        </SwiperSlide>
      </template>
    </Swiper>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

.carousel-wrapper {
  position: relative;
  width: 100%;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

.escrita-cima {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.escrita {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  color: var(--color-black-text);
}

.button-circle {
  height: 90%;
  aspect-ratio: 1/1;
  border-radius: 50%;
  background-color: var(--color-primary);
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  box-sizing: border-box;
  padding: 0.2rem;
  transition: opacity 0.2s;
}

.button-circle:hover {
  opacity: 0.85;
}

p {
  position: relative;
  display: inline-block;
  font-size: 1.3rem;
  font-weight: 600;
}

p span {
  color: var(--color-primary);
}

p::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: -3px;
  height: 3px;
  width: 75%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

p::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: -3px;
  height: 3px;
  width: 20%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

.arrows {
  display: flex;
  gap: 10px;
}

.mySwiper {
  width: 100%;
  border-radius: 16px;
  overflow-x: hidden;
  overflow-y: initial;
  padding: 1rem 0;
}

.swiper-button-next::after,
.swiper-button-prev::after {
  display: none;
}

.custom-prev,
.custom-next {
  z-index: 20;
  width: 29px;
  height: 29px;
  box-shadow: var(--shadow-sm);
  border: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.7);
  color: rgb(0, 0, 0);
  font-size: 24px;
  cursor: pointer;
  transition: 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.custom-prev:hover,
.custom-next:hover {
  box-shadow: var(--shadow-hover-blue);
}

.custom-prev { right: 50px; }
.custom-next { right: 10px; }

:deep(.swiper-slide) {
  height: auto;
  box-sizing: border-box;
}
</style>
