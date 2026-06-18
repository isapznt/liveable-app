<script setup lang="ts">
import { PhPhone, PhCalendar, PhCheck, PhX } from '@phosphor-icons/vue'

interface Requester {
  id: number
  name: string
  avatar: string
}

interface Property {
  id: number
  title: string
  price_per_day: number
  image: string
}

interface PendingRent {
  rent_id: number
  checkin: string
  checkout: string
  guests_count: number
  has_pet: boolean
  details: string
  confirmed: boolean
  property: Property
  requester: Requester
}

const props = defineProps<{ rent: PendingRent }>()
const emit = defineEmits<{
  confirm: [rentId: number]
  reject: [rentId: number]
}>()

function formatDate(dateStr: string): string {
  const soData = dateStr.split('T')[0]
  return new Date(soData + 'T12:00:00').toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function totalNoites(checkin: string, checkout: string): number {
  const a = new Date(checkin.split('T')[0] + 'T12:00:00')
  const b = new Date(checkout.split('T')[0] + 'T12:00:00')
  return Math.round((b.getTime() - a.getTime()) / (1000 * 60 * 60 * 24))
}
</script>

<template>
  <div class="card">
    <!-- Imagem -->
    <div
      class="cima"
      :style="
        rent.property.image ? { backgroundImage: `url('${encodeURI(rent.property.image)}')` } : {}
      "
    >
      <div class="badge-noites">{{ totalNoites(rent.checkin, rent.checkout) }} noites</div>
    </div>

    <!-- Conteúdo inferior -->
    <div class="baixo">
      <!-- Título e preço -->
      <div class="textos">
        <p class="titulo">{{ rent.property.title }}</p>
        <div class="subtexto">
          <p>R${{ rent.property.price_per_day }} p/noite</p>
          <p>•</p>
          <p>{{ rent.guests_count }} hóspede{{ rent.guests_count > 1 ? 's' : '' }}</p>
          <span v-if="rent.has_pet">• 🐾</span>
        </div>
      </div>

      <!-- Datas -->
      <div class="info-row">
        <PhCalendar :size="15" class="info-icon" />
        <span>{{ formatDate(rent.checkin) }} → {{ formatDate(rent.checkout) }}</span>
      </div>

      <!-- Detalhes extras -->
      <p v-if="rent.details" class="details-text">"{{ rent.details }}"</p>

      <!-- Quem solicitou -->
      <div class="pendencies-infos">
        <p class="label-solicitou">Quem solicitou:</p>
        <div class="card-contato">
          <div
            class="img"
            :style="
              rent.requester.avatar
                ? { backgroundImage: `url('${encodeURI(rent.requester.avatar)}')` }
                : {}
            "
          ></div>
          <p class="owner-name">{{ rent.requester.name }}</p>
          <PhPhone class="icon-phone" :size="20" />
        </div>
      </div>

      <!-- Ações -->
      <div class="actions">
        <button class="btn-recusar" @click="emit('reject', rent.rent_id)">
          <PhX :size="15" /> Recusar
        </button>
        <button class="btn-confirm" @click="emit('confirm', rent.rent_id)">
          <PhCheck :size="15" /> Confirmar
        </button>
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
  background-color: #e8e8e8;
}

.badge-noites {
  position: absolute;
  bottom: 12px;
  left: 12px;
  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
  backdrop-filter: blur(4px);
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
  gap: 8px;
  font-size: clamp(0.7rem, 0.81vw, 0.85rem);
  flex-wrap: wrap;
}
.subtexto p {
  margin: 0;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  opacity: 0.6;
}
.info-icon {
  flex-shrink: 0;
}

.details-text {
  margin: 0;
  font-size: 12px;
  opacity: 0.55;
  font-style: italic;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.label-solicitou {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
}

.card-contato {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 14px;
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(0, 0, 0, 0.08));
  background-color: var(--color-bg-secondary, #fff);
}

.card-contato .img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background-position: center;
  background-size: cover;
  flex-shrink: 0;
  background-color: #ddd;
}

.owner-name {
  margin: 0;
  flex: 1;
  font-size: 14px;
  font-weight: 500;
}

.icon-phone {
  color: var(--color-black-text, #1a1a1a);
  cursor: pointer;
  flex-shrink: 0;
}

.actions {
  display: flex;
  gap: 8px;
  margin-top: 4px;
}

.btn-recusar {
  flex: 1;
  height: 42px;
  border-radius: 14px;
  border: 1px solid #e0e0e0;
  background: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 14px;
  color: var(--color-black-text, #1a1a1a);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  transition:
    background 0.2s,
    border-color 0.2s,
    color 0.2s;
}
.btn-recusar:hover {
  background: #fef2f2;
  border-color: #fca5a5;
  color: #dc2626;
}

.btn-confirm {
  flex: 1;
  height: 42px;
  border-radius: 14px;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 14px;
  background-color: var(--color-primary, #3b82f6);
  color: var(--color-primary-text, #ffffff);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  transition: background-color 0.3s;
}
.btn-confirm:hover {
  background-color: var(--color-primary-hover, #2563eb);
}
</style>
