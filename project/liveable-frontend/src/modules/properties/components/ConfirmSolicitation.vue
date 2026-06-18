<script setup lang="ts">
import {
  PhCaretLeft,
  PhWifiHigh,
  PhMonitor,
  PhSidebar,
  PhCigarette,
  PhSnowflake,
  PhWashingMachine,
  PhHardDrive,
} from '@phosphor-icons/vue'
import { ref, onMounted } from 'vue'
import { exibirConfirm } from '@/modules/properties/composables/useConfirmSolicitation'
import TheCalendary from '@/shared/components/TheCalendary.vue'
import { useReservas } from '@/modules/properties/composables/useReservas'
import { useRoute } from 'vue-router'

const route = useRoute()
const propertyId = Number(route.params.id)

const { periodosBloqueados, carregando, erro, buscarReservas } = useReservas()

// ─── Comodidades ─────────────────────────────────────────────────────────────
interface PropertyAmenities {
  wifi: boolean
  tv: boolean
  cooler: boolean
  air_conditioning: boolean
  washer: boolean
  microwave: boolean
  smoker: boolean
}

const amenities = ref<PropertyAmenities | null>(null)
const carregandoProperty = ref(false)

async function buscarDadosProperty() {
  carregandoProperty.value = true
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/property/${propertyId}`)
    const data = await response.json()

    if (response.ok) {
      const prop = data.Propriedade ?? data
      amenities.value = {
        wifi:             Boolean(prop.wifi),
        tv:               Boolean(prop.tv),
        cooler:           Boolean(prop.cooler),
        air_conditioning: Boolean(prop.air_conditioning),
        washer:           Boolean(prop.washer),
        microwave:        Boolean(prop.microwave),
        smoker:           Boolean(prop.smoker),
      }
    }
  } catch (e) {
    console.error('[buscarDadosProperty]', e)
  } finally {
    carregandoProperty.value = false
  }
}

// ─── Reserva já existente ────────────────────────────────────────────────────
const jaTemReserva = ref(false)
const carregandoReservaUsuario = ref(false)

async function verificarReservaUsuario() {
  carregandoReservaUsuario.value = true
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/properties/${propertyId}/my-rent`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
    })
    const data = await response.json()
    if (response.ok) {
      jaTemReserva.value = Boolean(data.has_rent)
    }
  } catch (e) {
    console.error('[verificarReservaUsuario]', e)
  } finally {
    carregandoReservaUsuario.value = false
  }
}

// ─── Init ─────────────────────────────────────────────────────────────────────
onMounted(() => {
  if (!propertyId || isNaN(propertyId)) {
    console.error('[ConfirmarSolicitacao] ID inválido na rota:', route.params.id)
    return
  }
  buscarReservas(propertyId)
  buscarDadosProperty()
  verificarReservaUsuario()
})

// ─── Calendário ───────────────────────────────────────────────────────────────
const checkin  = ref<string | null>(null)
const checkout = ref<string | null>(null)

function handleDatas(datas: { checkin: string; checkout: string }) {
  checkin.value  = datas.checkin
  checkout.value = datas.checkout
}

// ─── Formulário ───────────────────────────────────────────────────────────────
const numPersons = ref<number | null>(null)
const details    = ref<string>('')
const has_pet    = ref<boolean | undefined>(undefined)

// ─── Envio ────────────────────────────────────────────────────────────────────
const enviando       = ref(false)
const erroReserva    = ref<string | null>(null)
const sucessoReserva = ref(false)

async function reservar() {
  if (jaTemReserva.value) return

  // Validação no frontend antes de chamar a API
  if (!checkin.value || !checkout.value) {
    erroReserva.value = 'Selecione as datas de check-in e check-out.'
    return
  }

  enviando.value       = true
  erroReserva.value    = null
  sucessoReserva.value = false

  try {
    const response = await fetch(`http://127.0.0.1:8000/api/properties/${propertyId}/rent`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
      body: JSON.stringify({
        checkin:      checkin.value,
        checkout:     checkout.value,
        guests_count: numPersons.value,
        has_pet:      has_pet.value,
        details:      details.value,
      }),
    })

    // Garante que a resposta é JSON antes de fazer parse
    const contentType = response.headers.get('content-type') ?? ''
    if (!contentType.includes('application/json')) {
      throw new Error('Erro interno no servidor. Tente novamente.')
    }

    const data = await response.json()

    if (!response.ok) {
      // Laravel retorna { message } ou { errors: { campo: [...] } }
      if (data.errors) {
        const primeiro = Object.values(data.errors as Record<string, string[]>)[0]
        throw new Error(Array.isArray(primeiro) ? primeiro[0] : String(primeiro))
      }
      throw new Error(data.message ?? `Erro ${response.status}`)
    }

    sucessoReserva.value = true
    jaTemReserva.value   = true

    // Fecha o painel após 2 segundos
    setTimeout(() => {
      exibirConfirm()
    }, 2000)

  } catch (e: any) {
    erroReserva.value = e.message ?? 'Erro ao confirmar reserva.'
    console.error('[reservar]', e)
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="all">
    <!-- Cabeçalho -->
    <div class="voltar" @click="exibirConfirm()">
      <PhCaretLeft :size="32" />
      <div class="circle-editor">
        <i class="fa-regular fa-newspaper editor-icon"></i>
      </div>
      <p>Confirmar Solicitação</p>
    </div>

    <!-- Calendário -->
    <div style="width: 100%">
      <p v-if="carregando">Carregando disponibilidade...</p>
      <p v-else-if="erro" style="color: red">{{ erro }}</p>
      <TheCalendary v-else :periodosBloqueados="periodosBloqueados" @updateDates="handleDatas" />
    </div>

    <!-- Comodidades -->
    <div class="mais-detalhes">
      <div class="mais-detalhes-title">
        <p class="title-principal">Mais detalhes</p>
        <p class="subtitulo">Comodidades da acomodação.</p>
      </div>

      <p v-if="carregandoProperty" class="amenidades-loading">Carregando comodidades...</p>

      <div v-else class="mais-detalhes-options">
        <div class="esquerda">
          <label :class="{ indisponivel: amenities && !amenities.wifi }">
            <PhWifiHigh class="mais-detalhes-icons" /> Wi-fi
          </label>
          <label :class="{ indisponivel: amenities && !amenities.tv }">
            <PhMonitor class="mais-detalhes-icons" /> TV
          </label>
          <label :class="{ indisponivel: amenities && !amenities.cooler }">
            <PhSidebar class="mais-detalhes-icons" /> Refrigerador
          </label>
          <label :class="{ indisponivel: amenities && !amenities.smoker }">
            <PhCigarette class="mais-detalhes-icons" /> Detector de fumaça
          </label>
        </div>
        <div class="direita">
          <label :class="{ indisponivel: amenities && !amenities.air_conditioning }">
            <PhSnowflake class="mais-detalhes-icons" /> Ar condicionado
          </label>
          <label :class="{ indisponivel: amenities && !amenities.washer }">
            <PhWashingMachine class="mais-detalhes-icons" /> Máquina de lavar
          </label>
          <label :class="{ indisponivel: amenities && !amenities.microwave }">
            <PhHardDrive class="mais-detalhes-icons" /> Micro-ondas
          </label>
        </div>
      </div>
    </div>

    <!-- Informações ao proprietário -->
    <div class="infos-prop" :class="{ bloqueado: jaTemReserva }">
      <p>Informações ao proprietário</p>
      <div class="inputs-prop">
        <div class="esquerda-infos">
          <input
            class="num-persons"
            type="number"
            placeholder="Nº de pessoas"
            v-model="numPersons"
            :disabled="jaTemReserva"
          />
          <div class="pet-input">
            <div class="pet-title">
              <i class="fa-solid fa-paw"></i>
              <p>Pet</p>
            </div>
            <div class="inputs-radio">
              <label>Sim</label>
              <input type="checkbox" :checked="has_pet === true"  @click="has_pet = true"  :disabled="jaTemReserva" />
            </div>
            <div class="inputs-radio">
              <label>Não</label>
              <input type="checkbox" :checked="has_pet === false" @click="has_pet = false" :disabled="jaTemReserva" />
            </div>
          </div>
        </div>
        <textarea placeholder="Mais detalhes..." v-model="details" :disabled="jaTemReserva"></textarea>
      </div>
    </div>

    <!-- Erro -->
    <div v-if="erroReserva" class="aviso-erro">
      <i class="fa-solid fa-triangle-exclamation"></i>
      {{ erroReserva }}
    </div>

    <!-- Sucesso -->
    <div v-if="sucessoReserva" class="aviso-sucesso">
      <i class="fa-solid fa-circle-check"></i>
      <div>
        <p class="aviso-titulo">Solicitação enviada!</p>
        <p class="aviso-sub">Aguarde o proprietário aceitar. Fechando em instantes...</p>
      </div>
    </div>

    <button
      class="confirm-button"
      @click="reservar"
      :disabled="enviando || jaTemReserva || carregandoReservaUsuario"
    >
      <span v-if="carregandoReservaUsuario">Verificando...</span>
      <span v-else-if="jaTemReserva && !sucessoReserva">Solicitação já enviada</span>
      <span v-else-if="enviando">Enviando...</span>
      <span v-else>Confirmar</span>
    </button>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.all {
  position: fixed;
  z-index: 999;
  top: 0;
  right: 0;
  height: 100%;
  width: 100%;
  max-width: 600px;
  background-color: var(--color-bg-secondary);
  overflow-y: auto;
  box-sizing: border-box;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  font-family: 'Poppins', sans-serif;
  gap: 3rem;
  box-shadow: var(--shadow-md);
  color: var(--color-black-text);
}

.voltar {
  width: 100%;
  display: flex;
  justify-content: flex-start;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
}

.circle-editor {
  aspect-ratio: 1/1;
  border: 1px solid var(--color-border-black);
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.editor-icon { width: clamp(35px, 1.5vw, 40px); }

.mais-detalhes {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.mais-detalhes-title { display: flex; flex-direction: column; }

.mais-detalhes-options {
  width: 100%;
  display: flex;
  gap: 1rem;
}

.esquerda,
.direita {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
}

.esquerda label,
.direita label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 1.1rem;
  transition: opacity 0.2s;
}

.indisponivel {
  opacity: 0.35;
  text-decoration: line-through;
  text-decoration-color: currentColor;
}

.mais-detalhes-icons { width: clamp(35px, 1.5vw, 40px); }

.amenidades-loading { font-size: 0.9rem; opacity: 0.6; }

.infos-prop {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  transition: opacity 0.2s;
}

.bloqueado {
  opacity: 0.45;
  pointer-events: none;
  user-select: none;
}

.inputs-prop { display: flex; justify-content: space-between; }

.esquerda-infos {
  width: 48%;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.esquerda-infos .num-persons {
  padding: 0.7rem 0 0.7rem 1rem;
  width: 100%;
  border-radius: 7px;
  border: 0;
  box-shadow: var(--shadow-sm);
  box-sizing: border-box;
}

.inputs-prop textarea {
  width: 48%;
  resize: none;
  border-radius: 10px;
  box-shadow: var(--shadow-sm);
  border: 0;
  box-sizing: border-box;
  padding: 0.7rem;
}

.pet-input {
  display: flex;
  justify-content: space-around;
  box-shadow: var(--shadow-sm);
  background-color: var(--color-bg);
  border-radius: 7px;
  align-items: center;
  padding: 0.7rem;
}

.pet-title { display: flex; align-items: center; gap: 10px; }

.inputs-radio { display: flex; gap: 5px; align-items: center; }

/* ── Avisos ── */
.aviso-erro,
.aviso-sucesso,
.aviso-ja-reservado {
  width: 100%;
  border-radius: 12px;
  padding: 14px 16px;
  font-size: 13px;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.aviso-erro {
  background: #fff3f3;
  border: 1px solid #ffb3b3;
  color: #c0392b;
}

.aviso-erro i { font-size: 16px; flex-shrink: 0; }

.aviso-sucesso {
  background: #f0faf3;
  border: 1px solid #6fcf97;
  color: #1e7e44;
}

.aviso-sucesso i { font-size: 22px; flex-shrink: 0; }

.aviso-titulo {
  font-weight: 600;
  font-size: 14px;
  margin: 0;
}

.aviso-sub {
  font-size: 12px;
  opacity: 0.75;
  margin: 2px 0 0;
}

.aviso-ja-reservado {
  background: #e3f2fd;
  border: 1px solid #90caf9;
  color: #1565c0;
}

button, input, textarea { font-family: 'Poppins', sans-serif; }

p { margin: 0; }

.title-principal { font-size: 1.2rem; font-weight: 650; }

.subtitulo { opacity: 0.6; font-weight: 500; }

.confirm-button {
  width: 100%;
  padding: 0.9rem 0;
  border: 0;
  background-color: var(--color-primary);
  color: var(--color-primary-text);
  border-radius: 15px;
  cursor: pointer;
  font-weight: 600;
  transition: opacity 0.2s;
}

.confirm-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
