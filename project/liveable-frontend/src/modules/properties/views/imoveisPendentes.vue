<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PendingRentCard from '@/modules/properties/components/Card-imoveis-pendencia.vue'
import ActiveRentCard from '@/modules/properties/components/ActiveRentCard.vue'
import PixPaymentModal from '@/modules/properties/components/PixPaymentModal.vue'

const BASE = 'http://127.0.0.1:8000/api'
const token = () => localStorage.getItem('token') ?? ''
const headers = () => ({
  Authorization: `Bearer ${token()}`,
  Accept: 'application/json',
  'Content-Type': 'application/json',
})

// ── Pendentes (dono aceitar/recusar) ─────────────────────────
const reservasPendentes = ref<any[]>([])
const carregandoPendentes = ref(true)
const erroPendentes = ref<string | null>(null)

// ── Aguardando pagamento (solicitador pagar) ─────────────────
const pagamentosPendentes = ref<any[]>([])
const carregandoPagamentos = ref(true)

// ── Reservas ativas (pagas) ──────────────────────────────────
const reservasAtivas = ref<any[]>([])
const carregandoAtivas = ref(true)

// ── Modal PIX ───────────────────────────────────────────────
const pagamentoSelecionado = ref<any>(null)

onMounted(async () => {
  await Promise.all([carregarPendentes(), carregarPagamentos(), carregarAtivas()])
})

async function carregarPendentes() {
  carregandoPendentes.value = true
  erroPendentes.value = null
  try {
    const res = await fetch(`${BASE}/my-properties/pending-rents`, { headers: headers() })
    if (!res.ok) throw new Error(`Erro ${res.status}`)
    reservasPendentes.value = await res.json()
  } catch (e: any) {
    erroPendentes.value = e.message
  } finally {
    carregandoPendentes.value = false
  }
}

async function carregarPagamentos() {
  carregandoPagamentos.value = true
  try {
    const res = await fetch(`${BASE}/payments/my`, { headers: headers() })
    const data = await res.json()
    pagamentosPendentes.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('[carregarPagamentos]', e)
  } finally {
    carregandoPagamentos.value = false
  }
}

async function carregarAtivas() {
  carregandoAtivas.value = true
  try {
    const res = await fetch(`${BASE}/rents/active`, { headers: headers() })
    const data = await res.json()
    reservasAtivas.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('[carregarAtivas]', e)
  } finally {
    carregandoAtivas.value = false
  }
}

async function handleConfirm(rentId: number) {
  await updateStatus(rentId, true)
}

async function handleReject(rentId: number) {
  await updateStatus(rentId, false)
}

async function updateStatus(rentId: number, confirmed: boolean) {
  try {
    const res = await fetch(`${BASE}/rents/${rentId}/status`, {
      method: 'PATCH',
      headers: headers(),
      body: JSON.stringify({ confirmed }),
    })
    if (!res.ok) throw new Error(`Erro ${res.status}`)

    // Remove da lista de pendentes
    reservasPendentes.value = reservasPendentes.value.filter((r) => r.rent_id !== rentId)

    // Se confirmou, recarrega pagamentos pendentes (novo payment foi criado)
    if (confirmed) await carregarPagamentos()
  } catch (e: any) {
    console.error('[updateStatus]', e)
  }
}

function abrirPix(pagamento: any) {
  pagamentoSelecionado.value = pagamento
}

function aoConfirmarPagamento() {
  // Remove da lista de pendentes e recarrega ativas
  pagamentosPendentes.value = pagamentosPendentes.value.filter(
    (p) => p.payment_id !== pagamentoSelecionado.value?.payment_id,
  )
  pagamentoSelecionado.value = null
  carregarAtivas()
}

function formatarValor(centavos: number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(
    centavos / 100,
  )
}

function tempoRestante(expiresAt: string) {
  const diff = new Date(expiresAt).getTime() - Date.now()
  if (diff <= 0) return 'Expirado'
  const h = Math.floor(diff / 3_600_000)
  const m = Math.floor((diff % 3_600_000) / 60_000)
  return `${h}h ${m}min`
}
</script>

<template>
  <div class="pendentes-wrapper">
    <!-- ── Solicitações para o dono aceitar ── -->
    <section class="pendentes-section">
      <h2 class="pendentes-section__title">
        Solicitações <span>Pendentes</span>
        <span class="pendentes-section__count" v-if="reservasPendentes.length">
          {{ reservasPendentes.length }}
        </span>
      </h2>

      <p v-if="carregandoPendentes" class="estado">Carregando...</p>
      <p v-else-if="erroPendentes" class="estado erro">{{ erroPendentes }}</p>
      <p v-else-if="!reservasPendentes.length" class="estado">Nenhuma solicitação pendente.</p>

      <div v-else class="cards-grid">
        <PendingRentCard
          v-for="rent in reservasPendentes"
          :key="rent.rent_id"
          :rent="rent"
          @confirm="handleConfirm"
          @reject="handleReject"
        />
      </div>
    </section>

    <!-- ── Aguardando pagamento (solicitador) ── -->
    <section class="pendentes-section" v-if="pagamentosPendentes.length || carregandoPagamentos">
      <h2 class="pendentes-section__title">
        Aguardando <span>Pagamento</span>
        <span
          class="pendentes-section__count pendentes-section__count--pix"
          v-if="pagamentosPendentes.length"
        >
          {{ pagamentosPendentes.length }}
        </span>
      </h2>

      <p v-if="carregandoPagamentos" class="estado">Carregando...</p>

      <div v-else class="cards-grid">
        <div v-for="p in pagamentosPendentes" :key="p.payment_id" class="payment-card">
          <div
            class="payment-card__img"
            :style="
              p.property.image ? { backgroundImage: `url('${encodeURI(p.property.image)}')` } : {}
            "
          />
          <div class="payment-card__body">
            <p class="payment-card__title">{{ p.property.title }}</p>
            <p class="payment-card__valor">{{ formatarValor(p.amount) }}</p>
            <div class="payment-card__prazo" :class="{ 'payment-card__prazo--urgente': true }">
              ⏰ {{ tempoRestante(p.expires_at) }} para pagar
            </div>
            <button class="payment-card__btn" @click="abrirPix(p)">Pagar com PIX</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Reservas ativas ── -->
    <section class="pendentes-section">
      <h2 class="pendentes-section__title">Reservas <span>Ativas</span></h2>

      <p v-if="carregandoAtivas" class="estado">Carregando...</p>
      <p v-else-if="!reservasAtivas.length" class="estado">Nenhuma reserva ativa.</p>

      <div v-else class="cards-grid">
        <ActiveRentCard v-for="rent in reservasAtivas" :key="rent.rent_id" :rent="rent" />
      </div>
    </section>

    <!-- ── Modal PIX ── -->
    <PixPaymentModal
      v-if="pagamentoSelecionado"
      :payment="pagamentoSelecionado"
      @close="pagamentoSelecionado = null"
      @paid="aoConfirmarPagamento"
    />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

.pendentes-wrapper {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 40px;
  font-family: 'Poppins', sans-serif;
}

.pendentes-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.pendentes-section__title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 5px;
  position: relative;
  align-self: flex-start;
  color: var(--color-black-text);
}

.pendentes-section__title span:first-of-type {
  color: #1a2fa8;
}

.pendentes-section__title::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: -3px;
  height: 3px;
  width: 75%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

.pendentes-section__title::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: -3px;
  height: 3px;
  width: 20%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

.pendentes-section__count {
  background: #1a2fa8;
  color: #fff;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 9px;
  border-radius: 20px;
}

.pendentes-section__count--pix {
  background: #16a34a;
}

.estado {
  font-size: 14px;
  opacity: 0.6;
  margin: 0;
  color: var(--color-black-text);
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

/* Payment card */
.payment-card {
  width: 280px;
  background: var(--color-bg-secondary, #fff);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: var(--shadow-sm, 0 4px 16px rgba(0, 0, 0, 0.1));
  display: flex;
  flex-direction: column;
}

.payment-card__img {
  width: 100%;
  height: 160px;
  background-size: cover;
  background-position: center;
  background-color: #e8e8e8;
}

.payment-card__body {
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.payment-card__title {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-black-text, #1a1a2e);
}

.payment-card__valor {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
}

.payment-card__prazo {
  font-size: 12px;
  font-weight: 600;
  color: #d97706;
  background: #fef3c7;
  border-radius: 8px;
  padding: 6px 10px;
}

.payment-card__prazo--urgente {
  color: #dc2626;
  background: #fee2e2;
}

.payment-card__btn {
  width: 100%;
  padding: 10px 0;
  border: none;
  border-radius: 12px;
  background: #16a34a;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.18s;
}

.payment-card__btn:hover {
  background: #15803d;
}
</style>
