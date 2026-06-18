<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'

interface PaymentData {
  payment_id: number
  amount: number
  expires_at: string
  checkin: string
  checkout: string
  property: { id: number; title: string; image: string | null }
}

const props = defineProps<{ payment: PaymentData }>()
const emit = defineEmits<{ close: []; paid: [] }>()

const BASE = 'http://127.0.0.1:8000/api'
const token = () => localStorage.getItem('token') ?? ''
const headers = () => ({ Authorization: `Bearer ${token()}`, Accept: 'application/json' })

const qrCode = ref<string | null>(null)
const brCode = ref<string | null>(null)
const carregando = ref(true)
const copiado = ref(false)
const statusPago = ref(false)
const statusExpired = ref(false)
const erroQr = ref<string | null>(null)
const simulando = ref(false)

const tempoRestante = ref('')
let countdownInterval: ReturnType<typeof setInterval> | null = null
let pollInterval: ReturnType<typeof setInterval> | null = null

function atualizarContagem() {
  const diff = new Date(props.payment.expires_at).getTime() - Date.now()
  if (diff <= 0) {
    tempoRestante.value = 'Expirado'
    statusExpired.value = true
    limparIntervalos()
    return
  }
  const h = Math.floor(diff / 3_600_000)
  const m = Math.floor((diff % 3_600_000) / 60_000)
  const s = Math.floor((diff % 60_000) / 1_000)
  tempoRestante.value = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

async function carregarQrCode() {
  carregando.value = true
  erroQr.value = null
  try {
    const res = await fetch(`${BASE}/payments/${props.payment.payment_id}/qrcode`, {
      headers: headers(),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message ?? `Erro ${res.status}`)
    const base64 = data.br_code_base64 ?? ''
    qrCode.value = base64.startsWith('data:') ? base64 : 'data:image/png;base64,' + base64
    brCode.value = data.br_code
  } catch (e: any) {
    erroQr.value = e.message
  } finally {
    carregando.value = false
  }
}

async function verificarPagamento() {
  try {
    const res = await fetch(`${BASE}/payments/${props.payment.payment_id}/check`, {
      method: 'POST',
      headers: headers(),
    })
    const data = await res.json()
    if (data.status === 'paid') {
      statusPago.value = true
      limparIntervalos()
      setTimeout(() => emit('paid'), 2000)
    } else if (data.status === 'expired') {
      statusExpired.value = true
      limparIntervalos()
    }
  } catch (e) {
    console.error('[PixPaymentModal] check', e)
  }
}

async function simularPagamento() {
  simulando.value = true
  try {
    const res = await fetch(`${BASE}/payments/${props.payment.payment_id}/simulate`, {
      method: 'POST',
      headers: headers(),
    })
    const data = await res.json()
    if (data.status === 'paid') {
      statusPago.value = true
      limparIntervalos()
      setTimeout(() => {
        emit('paid')
        window.location.reload()
      }, 2000)
    }
  } catch (e) {
    console.error('[simularPagamento]', e)
  } finally {
    simulando.value = false
  }
}

function copiarCodigo() {
  if (!brCode.value) return
  navigator.clipboard.writeText(brCode.value)
  copiado.value = true
  setTimeout(() => (copiado.value = false), 2500)
}

function limparIntervalos() {
  if (countdownInterval) clearInterval(countdownInterval)
  if (pollInterval) clearInterval(pollInterval)
}

const valorFormatado = computed(() =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(
    props.payment.amount / 100,
  ),
)

function formatDate(d: string) {
  return new Date(d + 'T12:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}

onMounted(async () => {
  atualizarContagem()
  await carregarQrCode()
  countdownInterval = setInterval(atualizarContagem, 1000)
  pollInterval = setInterval(verificarPagamento, 5000)
})

onUnmounted(limparIntervalos)
</script>

<template>
  <Teleport to="body">
    <div class="pix-backdrop" @click.self="emit('close')">
      <div class="pix-modal">
        <div class="pix-modal__header">
          <div class="pix-modal__header-info">
            <h2 class="pix-modal__title">Pagamento PIX</h2>
            <p class="pix-modal__sub">{{ payment.property.title }}</p>
            <p class="pix-modal__dates">
              {{ formatDate(payment.checkin) }} → {{ formatDate(payment.checkout) }}
            </p>
          </div>
          <button class="pix-modal__close" @click="emit('close')">✕</button>
        </div>

        <div class="pix-modal__meta">
          <div class="pix-modal__meta-item">
            <span class="pix-modal__meta-label">Valor total</span>
            <span class="pix-modal__meta-value">{{ valorFormatado }}</span>
          </div>
          <div class="pix-modal__meta-item">
            <span class="pix-modal__meta-label">Tempo restante</span>
            <span class="pix-modal__meta-value pix-modal__meta-value--timer">{{
              tempoRestante
            }}</span>
          </div>
        </div>

        <div v-if="carregando" class="pix-modal__loading">
          <div class="pix-modal__spinner" />
          <p>Gerando QR Code...</p>
        </div>

        <div v-else-if="erroQr" class="pix-modal__aviso pix-modal__aviso--erro">
          ⚠️ {{ erroQr }}
        </div>

        <div v-else-if="statusPago" class="pix-modal__sucesso">
          <div class="pix-modal__check">✓</div>
          <p>Pagamento confirmado!</p>
          <p class="pix-modal__sucesso-sub">Sua reserva foi confirmada.</p>
        </div>

        <div v-else-if="statusExpired" class="pix-modal__aviso pix-modal__aviso--erro">
          ⏰ O prazo de pagamento expirou. Entre em contato com o proprietário.
        </div>

        <!-- QR Code -->
        <template v-else>
          <div class="pix-modal__qr-wrap">
            <img :src="qrCode!" alt="QR Code PIX" class="pix-modal__qr" />
            <p class="pix-modal__qr-hint">Escaneie com o app do seu banco</p>
          </div>

          <div class="pix-modal__copy">
            <p class="pix-modal__copy-label">Ou copie o código PIX:</p>
            <div class="pix-modal__copy-row">
              <span class="pix-modal__copy-code">{{ brCode?.slice(0, 40) }}...</span>
              <button class="pix-modal__copy-btn" @click="copiarCodigo">
                {{ copiado ? '✓ Copiado!' : 'Copiar' }}
              </button>
            </div>
          </div>

          <p class="pix-modal__poll-hint">
            <span class="pix-modal__dot" />
            Verificando pagamento automaticamente...
          </p>

          <!-- DEV ONLY -->
          <button class="pix-modal__dev-btn" @click="simularPagamento" :disabled="simulando">
            {{ simulando ? 'Simulando...' : '🧪 Simular pagamento (dev)' }}
          </button>
        </template>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.pix-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  font-family: 'Poppins', sans-serif;
  overflow-y: auto;
}

.pix-modal {
  background: #fff;
  border-radius: 24px;
  width: 100%;
  max-width: 420px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 28px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  position: relative;
  margin: auto;
}

/* Header */
.pix-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}
.pix-modal__title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a1a2e;
}
.pix-modal__sub {
  margin: 2px 0 0;
  font-size: 0.85rem;
  color: #6b7280;
}
.pix-modal__dates {
  margin: 2px 0 0;
  font-size: 0.78rem;
  color: #9ca3af;
}

.pix-modal__close {
  background: #f3f4f6;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  font-size: 0.9rem;
  cursor: pointer;
  color: #6b7280;
  flex-shrink: 0;
  transition: background 0.18s;
}
.pix-modal__close:hover {
  background: #e5e7eb;
}

.pix-modal__meta {
  display: flex;
  gap: 12px;
}

.pix-modal__meta-item {
  flex: 1;
  background: #f8f9fb;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.pix-modal__meta-label {
  font-size: 0.72rem;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.pix-modal__meta-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
}
.pix-modal__meta-value--timer {
  color: #1a2fa8;
  font-variant-numeric: tabular-nums;
  font-size: 1.3rem;
}

.pix-modal__loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 24px 0;
  color: #9ca3af;
  font-size: 0.9rem;
}

.pix-modal__spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #e5e7eb;
  border-top-color: #1a2fa8;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* QR Code */
.pix-modal__qr-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.pix-modal__qr {
  width: 200px;
  height: 200px;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
}

.pix-modal__qr-hint {
  font-size: 0.8rem;
  color: #9ca3af;
  margin: 0;
}

.pix-modal__copy {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pix-modal__copy-label {
  font-size: 0.78rem;
  color: #6b7280;
  margin: 0;
  font-weight: 500;
}

.pix-modal__copy-row {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f3f4f6;
  border-radius: 10px;
  padding: 10px 12px;
}

.pix-modal__copy-code {
  flex: 1;
  font-size: 0.72rem;
  color: #6b7280;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-family: monospace;
}

.pix-modal__copy-btn {
  border: none;
  background: #1a2fa8;
  color: #fff;
  border-radius: 8px;
  padding: 5px 14px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.18s;
  flex-shrink: 0;
}
.pix-modal__copy-btn:hover {
  background: #1527a0;
}

.pix-modal__poll-hint {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.75rem;
  color: #9ca3af;
  margin: 0;
}

.pix-modal__dot {
  width: 8px;
  height: 8px;
  background: #22c55e;
  border-radius: 50%;
  animation: pulse 1.5s ease-in-out infinite;
  flex-shrink: 0;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.5;
    transform: scale(0.8);
  }
}

/* Dev btn */
.pix-modal__dev-btn {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 12px;
  background: #22c55e;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition:
    background 0.18s,
    opacity 0.18s;
}

.pix-modal__dev-btn:hover:not(:disabled) {
  background: #16a34a;
}
.pix-modal__dev-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Sucesso */
.pix-modal__sucesso {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 20px 0;
  color: #1a1a2e;
  font-weight: 600;
}

.pix-modal__check {
  width: 56px;
  height: 56px;
  background: #dcfce7;
  color: #16a34a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
}

.pix-modal__sucesso-sub {
  font-size: 0.85rem;
  font-weight: 400;
  color: #6b7280;
  margin: 0;
}

/* Avisos */
.pix-modal__aviso {
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 0.85rem;
  margin: 0;
}

.pix-modal__aviso--erro {
  background: #fff8e1;
  border: 1px solid #ffe082;
  color: #7a5800;
}

p {
  margin: 0;
}
</style>
