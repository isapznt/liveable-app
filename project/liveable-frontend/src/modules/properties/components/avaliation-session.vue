<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/pagination'

import ReviewCard from './avaliation-card.vue'

const BASE_URL = 'http://127.0.0.1:8000/api'

interface Review {
  id: number
  rating: number
  title?: string
  comment?: string
  text?: string
  user?: {
    id: number
    name: string
  }
}

const props = defineProps<{
  propertyId: number
  pricePerNight?: number
}>()

const emit = defineEmits<{
  (e: 'review-sent', review: Review): void
}>()

const swiperModules = [Pagination]

// Dados carregados da API
const localReviews = ref<Review[]>([])
const carregando = ref(false)
const erroCarregamento = ref<string | null>(null)

// Drawer
const drawerOpen = ref(false)
const hoverRating = ref(0)
const enviando = ref(false)
const erroEnvio = ref<string | null>(null)
const sucessoEnvio = ref(false)

const form = ref({
  rating: 0,
  title: '',
  comment: '',
})

const averageRating = computed(() => {
  if (!localReviews.value.length) return 0

  return (
    localReviews.value.reduce((sum, review) => sum + review.rating, 0) / localReviews.value.length
  )
})

const recommendPercent = computed(() => {
  if (!localReviews.value.length) return 0

  const good = localReviews.value.filter((review) => review.rating >= 4).length

  return Math.round((good / localReviews.value.length) * 100)
})

const ratingLabel = computed(() => {
  const active = hoverRating.value || form.value.rating

  return (
    ['', 'Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'][active] ??
    'Toque nas estrelas para avaliar'
  )
})

async function carregarAvaliacoes() {
  carregando.value = true
  erroCarregamento.value = null

  try {
    const res = await fetch(`${BASE_URL}/properties/${props.propertyId}/reviews`)

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.message ?? `Erro ${res.status}`)
    }

    localReviews.value = data.data
  } catch (e) {
    erroCarregamento.value = 'Não foi possível carregar as avaliações.'
    console.error('[carregarAvaliacoes]', e)
  } finally {
    carregando.value = false
  }
}

async function enviarAvaliacao() {
  if (!form.value.rating) {
    erroEnvio.value = 'Selecione uma nota antes de enviar.'
    return
  }

  if (!form.value.comment.trim()) {
    erroEnvio.value = 'Escreva um comentário sobre sua estadia.'
    return
  }

  enviando.value = true
  erroEnvio.value = null
  sucessoEnvio.value = false

  try {
    const res = await fetch(`${BASE_URL}/properties/${props.propertyId}/reviews`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
      body: JSON.stringify({
        rating: form.value.rating,
        title: form.value.title,
        comment: form.value.comment,
      }),
    })

    const data = await res.json()

    if (!res.ok) {
      throw new Error(data.message ?? `Erro ${res.status}`)
    }

    localReviews.value.unshift(data.data)

    sucessoEnvio.value = true

    emit('review-sent', data.data)

    setTimeout(() => {
      drawerOpen.value = false
      sucessoEnvio.value = false

      form.value = {
        rating: 0,
        title: '',
        comment: '',
      }
    }, 1800)
  } catch (e: any) {
    erroEnvio.value = e.message ?? 'Erro ao enviar avaliação.'
    console.error('[enviarAvaliacao]', e)
  } finally {
    enviando.value = false
  }
}

onMounted(() => {
  carregarAvaliacoes()
})
</script>

<template>
  <div class="reviews-section">
    <!-- Cabeçalho da seção -->
    <div class="reviews-section__header">
      <h2 class="reviews-section__title">Avaliações dos <span>Hóspedes</span></h2>
      <button class="reviews-section__avaliar-btn" @click="drawerOpen = true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 20h9" />
          <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
        </svg>
        Avaliar
      </button>
    </div>

    <!-- Loading / erro -->
    <p v-if="carregando" class="reviews-section__status">Carregando avaliações...</p>
    <p v-else-if="erroCarregamento" class="reviews-section__status reviews-section__status--erro">
      {{ erroCarregamento }}
    </p>

    <!-- Swiper de cards -->
    <template v-else>
      <p v-if="!localReviews.length" class="reviews-section__status">
        Nenhuma avaliação ainda. Seja o primeiro!
      </p>

      <Swiper
        v-else
        :slides-per-view="1"
        :space-between="16"
        :breakpoints="{
          600: { slidesPerView: 2 },
          900: { slidesPerView: 3 },
        }"
        :pagination="{ clickable: true }"
        :modules="swiperModules"
        class="reviews-swiper"
      >
        <SwiperSlide v-for="review in localReviews" :key="review.id">
          <ReviewCard :review="review" />
        </SwiperSlide>
      </Swiper>
    </template>

    <!-- Barra de resumo -->
    <div v-if="localReviews.length" class="reviews-summary">
      <div class="reviews-summary__item">
        <span class="reviews-summary__icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z" />
          </svg>
        </span>
        <div class="reviews-summary__divider" />
        <div class="reviews-summary__info">
          <span class="reviews-summary__label">Avaliação geral</span>
          <span class="reviews-summary__value"
            >{{ averageRating.toFixed(1) }} <small>/ 5</small></span
          >
        </div>
      </div>

      <div class="reviews-summary__item">
        <span class="reviews-summary__icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 2H4a2 2 0 00-2 2v18l4-4h14a2 2 0 002-2V4a2 2 0 00-2-2z" />
          </svg>
        </span>
        <div class="reviews-summary__divider" />
        <div class="reviews-summary__info">
          <span class="reviews-summary__label">Total de avaliações</span>
          <span class="reviews-summary__value">{{ localReviews.length }}</span>
        </div>
      </div>

      <div class="reviews-summary__item">
        <span class="reviews-summary__icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
            />
          </svg>
        </span>
        <div class="reviews-summary__divider" />
        <div class="reviews-summary__info">
          <span class="reviews-summary__label">Recomendam</span>
          <span class="reviews-summary__value">{{ recommendPercent }}%</span>
        </div>
      </div>
    </div>

    <!-- ── Drawer de avaliação ── -->
    <Transition name="drawer-overlay">
      <div v-if="drawerOpen" class="drawer-overlay" @click.self="drawerOpen = false" />
    </Transition>

    <Transition name="drawer">
      <div v-if="drawerOpen" class="drawer">
        <!-- Header do drawer -->
        <div class="drawer__header" @click="drawerOpen = false">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M15 18l-6-6 6-6" />
          </svg>
          <div class="drawer__header-icon">
            <svg viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
              />
            </svg>
          </div>
          <p>Enviar Avaliação</p>
        </div>

        <!-- Estrelas interativas -->
        <div class="drawer__stars-section">
          <p class="drawer__section-title">Sua nota</p>
          <p class="drawer__section-sub">Como foi sua experiência?</p>
          <div class="drawer__stars">
            <button
              v-for="i in 5"
              :key="i"
              class="drawer__star-btn"
              :class="{ 'drawer__star-btn--filled': i <= hoverRating || i <= form.rating }"
              @mouseenter="hoverRating = i"
              @mouseleave="hoverRating = 0"
              @click="form.rating = i"
            >
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"
                />
              </svg>
            </button>
          </div>
          <p class="drawer__rating-label">{{ ratingLabel }}</p>
        </div>

        <!-- Campos de texto -->
        <div class="drawer__fields">
          <div class="drawer__field">
            <label class="drawer__label">Título da avaliação</label>
            <input
              class="drawer__input"
              type="text"
              placeholder="Resumo da sua experiência..."
              v-model="form.title"
            />
          </div>

          <div class="drawer__field">
            <label class="drawer__label">Comentário</label>
            <textarea
              class="drawer__textarea"
              placeholder="Conte mais sobre sua estadia..."
              v-model="form.comment"
              rows="5"
            />
          </div>
        </div>

        <!-- Feedback -->
        <p v-if="erroEnvio" class="drawer__aviso drawer__aviso--erro">⚠️ {{ erroEnvio }}</p>
        <p v-if="sucessoEnvio" class="drawer__aviso drawer__aviso--sucesso">
          ✅ Avaliação enviada com sucesso!
        </p>

        <!-- Botão enviar -->
        <button class="drawer__confirm-btn" @click="enviarAvaliacao" :disabled="enviando">
          {{ enviando ? 'Enviando...' : 'Confirmar Avaliação' }}
        </button>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.reviews-section {
  display: flex;
  flex-direction: column;
  gap: 28px;
  font-family: 'Poppins', sans-serif;
  position: relative;
  margin-top: 3rem;
  color: var(--color-black-text);
}

.reviews-section__status {
  font-size: 14px;
  margin: 0;
  text-align: center;
  padding: 24px 0;
  min-height: 300px;
  align-items: center;
  justify-content: center;
  display: flex;
  min-height: 300px;
  font-size: 1rem;
  opacity: 0.5;
  font-weight: 600;
}

.reviews-section__status--erro {
  color: #dc2626;
}

/* ── Cabeçalho ── */
.reviews-section__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.reviews-section__title {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  position: relative;
}

.reviews-section__title span {
  color: #1a2fa8;
}

h2::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: -3px;
  height: 3px;
  width: 75%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

h2::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: -3px;
  height: 3px;
  width: 20%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

.reviews-section__avaliar-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 20px;
  background: #1a2fa8;
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  cursor: pointer;
  transition: background 0.18s ease;
}

.reviews-section__avaliar-btn:hover {
  background: #1527a0;
}
.reviews-section__avaliar-btn svg {
  width: 16px;
  height: 16px;
}

/* ── Swiper ── */
.reviews-swiper {
  width: 100%;
  padding-bottom: 36px !important;
}

.reviews-swiper :deep(.swiper-pagination-bullet) {
  background: #d1d5db;
  opacity: 1;
  width: 7px;
  height: 7px;
}

.reviews-swiper :deep(.swiper-pagination-bullet-active) {
  background: #1a2fa8;
  width: 20px;
  border-radius: 4px;
}

/* ── Barra de resumo ── */
.reviews-summary {
  display: flex;
  background: var(--color-bg-secondary);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 0 8px;
  overflow: hidden;
}

.reviews-summary__item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px 20px;
  flex: 1;
  min-width: 0;
  position: relative;
}

.reviews-summary__item:not(:last-child)::after {
  content: '';
  position: absolute;
  right: 0;
  top: 20%;
  height: 60%;
  width: 1px;
  background: #e5e7eb;
}

.reviews-summary__icon {
  color: #1a1a2e;
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.reviews-summary__icon svg {
  width: 22px;
  height: 22px;
  color: var(--color-black-text);
}
.reviews-summary__divider {
  width: 1px;
  height: 36px;
  background: #e5e7eb;
  flex-shrink: 0;
}
.reviews-summary__info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}
.reviews-summary__label {
  font-size: 12px;
  color: #9ca3af;
  white-space: nowrap;
  color: var(--color-black-text);
}
.reviews-summary__value {
  font-size: 16px;
  font-weight: 700;
  color: #1a1a2e;
  color: var(--color-black-text);
}
.reviews-summary__value small {
  font-size: 12px;
  font-weight: 400;
  color: #9ca3af;
}
.reviews-summary__item--price {
  justify-content: center;
  flex-direction: column;
  align-items: flex-start;
  gap: 0;
}
.reviews-summary__price {
  font-size: 18px;
  font-weight: 800;
  color: #1a1a2e;
  line-height: 1.1;
}
.reviews-summary__price-label {
  font-size: 12px;
  color: #9ca3af;
}

/* ── Overlay ── */
.drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  z-index: 998;
}

.drawer-overlay-enter-active,
.drawer-overlay-leave-active {
  transition: opacity 0.28s ease;
}
.drawer-overlay-enter-from,
.drawer-overlay-leave-to {
  opacity: 0;
}

/* ── Drawer ── */
.drawer {
  position: fixed;
  z-index: 999;
  top: 0;
  right: 0;
  height: 100%;
  width: 100%;
  max-width: 520px;
  background-color: var(--color-bg-secondary);
  overflow-y: auto;
  box-sizing: border-box;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
  box-shadow: var(--shadow-sm);
  font-family: 'Poppins', sans-serif;
  color: var(--color-primary-text);
}

.drawer-enter-active,
.drawer-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(100%);
}

.drawer__header {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
}

.drawer__header svg:first-child {
  width: 28px;
  height: 28px;
  flex-shrink: 0;
}

.drawer__header-icon {
  width: 40px;
  height: 40px;
  border: 1px solid #e5e7eb;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.drawer__header-icon svg {
  width: 18px;
  height: 18px;
  color: #1a2fa8;
}

.drawer__header p {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 650;
}

.drawer__stars-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.drawer__section-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 650;
}
.drawer__section-sub {
  margin: 0;
  font-size: 0.85rem;
  opacity: 0.6;
  font-weight: 500;
}

.drawer__stars {
  display: flex;
  gap: 8px;
  margin-top: 4px;
}

.drawer__star-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: transform 0.15s ease;
}

.drawer__star-btn:hover {
  transform: scale(1.15);
}

.drawer__star-btn svg {
  width: 36px;
  height: 36px;
  color: #d1d5db;
  transition: color 0.15s ease;
}

.drawer__star-btn--filled svg {
  color: #f59e0b;
}

.drawer__rating-label {
  margin: 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a2fa8;
  min-height: 20px;
}

.drawer__fields {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}
.drawer__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.drawer__label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a1a2e;
}

.drawer__input,
.drawer__textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  color: #1a1a2e;
  background: #fff;
  outline: none;
  transition: border-color 0.18s ease;
  box-shadow: var(--shadow-sm);
}

.drawer__input:focus,
.drawer__textarea:focus {
  border-color: #1a2fa8;
}
.drawer__textarea {
  resize: none;
}

.drawer__aviso {
  width: 100%;
  box-sizing: border-box;
  border-radius: 10px;
  padding: 10px 16px;
  font-size: 13px;
  margin: 0;
}

.drawer__aviso--erro {
  background: #fff8e1;
  border: 1px solid #ffe082;
  color: #7a5800;
}
.drawer__aviso--sucesso {
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  color: #2e7d32;
}

.drawer__confirm-btn {
  width: 100%;
  padding: 0.9rem 0;
  border: 0;
  background: #1a2fa8;
  color: #fff;
  border-radius: 15px;
  cursor: pointer;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  font-size: 1rem;
  transition:
    opacity 0.2s,
    background 0.18s;
  margin-top: auto;
}

.drawer__confirm-btn:hover:not(:disabled) {
  background: #1527a0;
}
.drawer__confirm-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 600px) {
  .reviews-summary {
    flex-wrap: wrap;
  }
  .reviews-summary__item {
    flex: 1 1 45%;
  }
  .reviews-summary__item::after {
    display: none;
  }
  .drawer {
    max-width: 100%;
  }
}
</style>
