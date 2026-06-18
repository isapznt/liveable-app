<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/navigation'
import { Navigation } from 'swiper/modules'

import { PhCaretLeft, PhCaretRight } from '@phosphor-icons/vue'

import CardEmAlta from './Card-em-alta.vue'
import CardEmAltaSkeleton from './CardEmAltaSkeleton.vue'
import { useRouter } from 'vue-router'

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
  clicks: number
}

const properties = ref<Property[]>([])

onMounted(async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/properties/featured')
    const data = await response.json()
    properties.value = Array.isArray(data) ? data : []
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
        <p class="titulo">Propriedades em <span>Alta</span></p>
        <div class="button-circle" @click="verTodas()">
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

    <!-- Nenhuma propriedade em alta -->
    <div v-if="!carregando && properties.length === 0" class="vazio">
      <p>Nenhuma propriedade em destaque no momento.</p>
    </div>

    <Swiper
      v-else
      :modules="[Navigation]"
      :space-between="24"
      :slides-per-view="1"
      :loop="properties.length > 1"
      :breakpoints="{
        '768':  { slidesPerView: 2.1, spaceBetween: 20 },
        '1024': { slidesPerView: 2.4, spaceBetween: 24 },
        '1440': { slidesPerView: 3.5, spaceBetween: 30 },
        '1920': { slidesPerView: 3.5, spaceBetween: 40 },
      }"
      @swiper="onSwiper"
      class="mySwiper"
    >
      <template v-if="carregando">
        <SwiperSlide v-for="n in 4" :key="'skeleton-' + n">
          <CardEmAltaSkeleton />
        </SwiperSlide>
      </template>

      <template v-else>
        <SwiperSlide v-for="casa in properties" :key="casa.id">
          <CardEmAlta :casa="casa" />
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

.button-circle:hover { opacity: 0.85; }

p {
  position: relative;
  display: inline-block;
  font-size: 1.3rem;
  font-weight: 600;
}

p span { color: var(--color-primary); }

.titulo::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: -3px;
  height: 3px;
  width: 75%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

.titulo::after {
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

.vazio {
  padding: 3rem 0;
  text-align: center;
  opacity: 0.4;
  font-size: 0.9rem;
  color: var(--color-black-text);
}

.mySwiper {
  width: 100%;
  border-radius: 16px;
  overflow-x: hidden;
  overflow-y: initial;
  padding: 1rem 0;
}

.swiper-button-next::after,
.swiper-button-prev::after { display: none; }

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
.custom-next:hover { box-shadow: var(--shadow-hover-blue); }

.custom-prev { right: 50px; }
.custom-next { right: 10px; }

:deep(.swiper-slide) {
  height: auto;
  box-sizing: border-box;
}
</style>
