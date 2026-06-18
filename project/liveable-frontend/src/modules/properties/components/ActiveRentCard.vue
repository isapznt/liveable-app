<script setup lang="ts">
import { computed } from 'vue'
import { PhCalendar, PhUsers } from '@phosphor-icons/vue'

interface ActiveRent {
  rent_id: number
  checkin: string | null
  checkout: string | null
  guests_count: number
  has_pet: boolean
  is_owner: boolean
  property: { id: number; title: string; image: string | null }
  requester: { id: number; name: string; avatar: string | null }
}

const props = defineProps<{ rent: ActiveRent }>()

function formatDate(d: string | null) {
  if (!d) return '—'
  const date = new Date(d)
  if (isNaN(date.getTime())) return '—'
  return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

const diasRestantes = computed(() => {
  if (!props.rent.checkout) return null
  const hoje = new Date()
  hoje.setHours(0, 0, 0, 0)
  const fim = new Date(props.rent.checkout)
  if (isNaN(fim.getTime())) return null
  return Math.ceil((fim.getTime() - hoje.getTime()) / 86_400_000)
})

const badgeClass = computed(() => {
  if (diasRestantes.value === null) return 'badge--encerrado'
  if (diasRestantes.value < 0) return 'badge--encerrado'
  if (diasRestantes.value <= 3) return 'badge--urgente'
  return 'badge--ativo'
})

const badgeLabel = computed(() => {
  if (diasRestantes.value === null) return 'Data inválida'
  if (diasRestantes.value < 0) return 'Encerrado'
  if (diasRestantes.value === 0) return 'Último dia!'
  return `${diasRestantes.value} dias restantes`
})
</script>

<template>
  <div class="active-card">
    <div
      class="active-card__img"
      :style="
        rent.property.image ? { backgroundImage: `url('${encodeURI(rent.property.image)}')` } : {}
      "
    >
      <span class="active-card__badge" :class="badgeClass">{{ badgeLabel }}</span>
    </div>

    <div class="active-card__body">
      <p class="active-card__title">{{ rent.property.title }}</p>

      <div class="active-card__row">
        <PhCalendar :size="14" />
        <span>{{ formatDate(rent.checkin) }} → {{ formatDate(rent.checkout) }}</span>
      </div>

      <div class="active-card__row">
        <PhUsers :size="14" />
        <span>{{ rent.guests_count }} hóspede{{ rent.guests_count > 1 ? 's' : '' }}</span>
        <PhPaw v-if="rent.has_pet" :size="14" style="margin-left: 4px" />
      </div>

      <div v-if="rent.is_owner" class="active-card__requester">
        <div
          class="active-card__avatar"
          :style="
            rent.requester.avatar
              ? { backgroundImage: `url('${encodeURI(rent.requester.avatar)}')` }
              : {}
          "
        >
          <span v-if="!rent.requester.avatar">{{ rent.requester.name.charAt(0) }}</span>
        </div>
        <span class="active-card__requester-name">{{ rent.requester.name }}</span>
      </div>

      <div v-else class="active-card__status">✅ Reserva confirmada e paga</div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

.active-card {
  width: 280px;
  background: var(--color-bg-secondary, #fff);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: var(--shadow-sm, 0 4px 16px rgba(0, 0, 0, 0.1));
  font-family: 'Poppins', sans-serif;
  color: var(--color-black-text, #1a1a1a);
  display: flex;
  flex-direction: column;
}

.active-card__img {
  width: 100%;
  height: 180px;
  background-size: cover;
  background-position: center;
  background-color: #e8e8e8;
  position: relative;
}

.active-card__badge {
  position: absolute;
  bottom: 10px;
  left: 10px;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
}

.badge--ativo {
  background: #dcfce7;
  color: #16a34a;
}
.badge--urgente {
  background: #fef3c7;
  color: #d97706;
}
.badge--encerrado {
  background: #f3f4f6;
  color: #6b7280;
}

.active-card__body {
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.active-card__title {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
}

.active-card__row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  opacity: 0.65;
}

.active-card__requester {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  background: #f8f9fb;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
}

.active-card__avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  background-color: #d1d5db;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: #6b7280;
  flex-shrink: 0;
}

.active-card__requester-name {
  font-size: 12px;
}

.active-card__status {
  font-size: 12px;
  color: #16a34a;
  font-weight: 600;
}
</style>
