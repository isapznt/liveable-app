<script setup lang="ts">
import {
  PhCaretLeft,
  PhPencilSimpleLine,
  PhHouse,
  PhBuildingApartment,
  PhFarm,
  PhBed,
  PhBathtub,
  PhDresser,
  PhWifiHigh,
  PhMonitor,
  PhSidebar,
  PhCigarette,
  PhSnowflake,
  PhWashingMachine,
  PhHardDrive,
} from '@phosphor-icons/vue'
import { ref, watch } from 'vue'

// ── Props / emits ──────────────────────────────────────
const props = defineProps<{
  propertyId: number | string
  open: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved'): void
}>()

// ── Estado do formulário ───────────────────────────────
const salvando = ref(false)
const erro = ref<string | null>(null)
const sucesso = ref(false)

const form = ref({
  property_title: '',
  local: '',
  area: '',
  type: '',
  beds_qtd: 1,
  toilette: 1,
  dresser: 1,
  wifi: false,
  tv: false,
  cooler: false,
  air_conditioning: false,
  washer: false,
  microwave: false,
  smoker: false,
  pricePerDay: '',
  pricePerWeek: '',
  pricePerMonth: '',
})

const tiposAtivos = ref<string[]>([])
const tipoProp = ref<string | null>(null)

watch(
  () => props.open,
  async (aberto) => {
    if (!aberto) return
    erro.value = null
    sucesso.value = false
    try {
      const res = await fetch(`http://127.0.0.1:8000/api/property/${props.propertyId}`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      })
      const data = await res.json()
      const p = data.Propriedade

      form.value = {
        property_title: p.property_title ?? '',
        local: p.local ?? '',
        area: p.area ?? '',
        type: p.type ?? '',
        beds_qtd: p.beds_qtd ?? 1,
        toilette: p.toilette ?? 1,
        dresser: 1,
        wifi: !!p.wifi,
        tv: !!p.tv,
        cooler: !!p.cooler,
        air_conditioning: !!p.air_conditioning,
        washer: !!p.washer,
        microwave: !!p.microwave,
        smoker: !!p.smoker,
        pricePerDay: p.pricePerDay ?? '',
        pricePerWeek: p.pricePerWeek ?? '',
        pricePerMonth: p.pricePerMonth ?? '',
      }

      tipoProp.value = p.type ?? null

      tiposAtivos.value = []
      if (p.pricePerDay) tiposAtivos.value.push('dia')
      if (p.pricePerWeek) tiposAtivos.value.push('semana')
      if (p.pricePerMonth) tiposAtivos.value.push('mes')
    } catch (e) {
      console.error('[TheEditProperty] carregar', e)
    }
  },
)

// ── Helpers ────────────────────────────────────────────
function toggleTipo(tipo: string) {
  tipoProp.value = tipoProp.value === tipo ? null : tipo
  form.value.type = tipoProp.value ?? ''
}

function togglePreco(t: string) {
  if (tiposAtivos.value.includes(t)) {
    tiposAtivos.value = tiposAtivos.value.filter((x) => x !== t)
  } else {
    tiposAtivos.value.push(t)
  }
}

function inc(campo: 'beds_qtd' | 'toilette' | 'dresser') {
  form.value[campo]++
}

function dec(campo: 'beds_qtd' | 'toilette' | 'dresser') {
  if (form.value[campo] > 1) form.value[campo]--
}

// ── Salvar ─────────────────────────────────────────────
async function salvar() {
  salvando.value = true
  erro.value = null
  sucesso.value = false

  const body: Record<string, any> = {
    property_title: form.value.property_title,
    local: form.value.local,
    area: Number(form.value.area),
    type: form.value.type,
    beds_qtd: form.value.beds_qtd,
    toilette: form.value.toilette,
    wifi: form.value.wifi,
    tv: form.value.tv,
    cooler: form.value.cooler,
    air_conditioning: form.value.air_conditioning,
    washer: form.value.washer,
    microwave: form.value.microwave,
    smoker: form.value.smoker,
  }

  if (tiposAtivos.value.includes('dia')) body.pricePerDay = Number(form.value.pricePerDay)
  if (tiposAtivos.value.includes('semana')) body.pricePerWeek = Number(form.value.pricePerWeek)
  if (tiposAtivos.value.includes('mes')) body.pricePerMonth = Number(form.value.pricePerMonth)

  try {
    const res = await fetch(`http://127.0.0.1:8000/api/property/update/${props.propertyId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
      body: JSON.stringify(body),
    })

    const data = await res.json()
    if (!res.ok) throw new Error(data.message ?? `Erro ${res.status}`)

    sucesso.value = true
    emit('saved')
    setTimeout(() => {
      sucesso.value = false
      emit('close')
    }, 1600)
  } catch (e: any) {
    erro.value = e.message ?? 'Erro ao salvar.'
  } finally {
    salvando.value = false
  }
}
</script>

<template>
  <Transition name="overlay">
    <div v-if="open" class="edit-overlay" @click.self="emit('close')" />
  </Transition>

  <Transition name="drawer">
    <div v-if="open" class="all">
      <!-- Cabeçalho -->
      <div class="voltar" @click="emit('close')">
        <PhCaretLeft :size="32" />
        <div class="circle-editor">
          <PhPencilSimpleLine class="editor-icon" />
        </div>
        <p>Editar propriedade</p>
      </div>

      <!-- Valores -->
      <div class="valores">
        <div class="valores-title">
          <p class="title-principal">Definição de valores</p>
          <p class="subtitulo">Preencha os valores de aluguel para exibi-los na simulação.</p>
        </div>
        <div class="valores-cards">
          <div class="valores-card" @click="togglePreco('dia')">
            <div class="valores-card-cima">
              <p>Valor diário</p>
              <input type="radio" :checked="tiposAtivos.includes('dia')" readonly />
            </div>
            <div class="valores-card-baixo">
              <input
                v-if="tiposAtivos.includes('dia')"
                class="price-input"
                type="number"
                placeholder="R$ 0,00"
                v-model="form.pricePerDay"
                @click.stop
              />
              <p v-else class="price-placeholder">—</p>
            </div>
          </div>

          <div class="valores-card" @click="togglePreco('semana')">
            <div class="valores-card-cima">
              <p>Valor semanal</p>
              <input type="radio" :checked="tiposAtivos.includes('semana')" readonly />
            </div>
            <div class="valores-card-baixo">
              <input
                v-if="tiposAtivos.includes('semana')"
                class="price-input"
                type="number"
                placeholder="R$ 0,00"
                v-model="form.pricePerWeek"
                @click.stop
              />
              <p v-else class="price-placeholder">—</p>
            </div>
          </div>

          <div class="valores-card" @click="togglePreco('mes')">
            <div class="valores-card-cima">
              <p>Valor mensal</p>
              <input type="radio" :checked="tiposAtivos.includes('mes')" readonly />
            </div>
            <div class="valores-card-baixo">
              <input
                v-if="tiposAtivos.includes('mes')"
                class="price-input"
                type="number"
                placeholder="R$ 0,00"
                v-model="form.pricePerMonth"
                @click.stop
              />
              <p v-else class="price-placeholder">—</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Categoria -->
      <div class="categoria">
        <div class="categoria-title">
          <p class="title-principal">Categoria do imóvel</p>
          <p class="subtitulo">Defina a categoria do imóvel que será exibida ao público.</p>
        </div>
        <div class="categoria-cards">
          <div class="categoria-card" @click="toggleTipo('casa')">
            <div class="circle-categoria"><PhHouse class="categoria-icon" /></div>
            <p>Casa</p>
            <input type="radio" :checked="tipoProp === 'casa'" readonly />
          </div>
          <div class="categoria-card" @click="toggleTipo('apartamento')">
            <div class="circle-categoria"><PhBuildingApartment class="categoria-icon" /></div>
            <p>Apart.</p>
            <input type="radio" :checked="tipoProp === 'apartamento'" readonly />
          </div>
          <div class="categoria-card" @click="toggleTipo('chacara')">
            <div class="circle-categoria"><PhFarm class="categoria-icon" /></div>
            <p>Chácara</p>
            <input type="radio" :checked="tipoProp === 'chacara'" readonly />
          </div>
        </div>
      </div>

      <!-- Informações gerais -->
      <div class="info-gerais-inputs">
        <div class="info-gerais-input">
          <input type="text" v-model="form.local" placeholder="Endereço" />
          <div class="info-gerais-divisoria"></div>
          <span class="info-gerais-label">Endereço</span>
        </div>
        <div class="info-gerais-input">
          <input type="number" v-model="form.area" placeholder="Área do terreno" />
          <div class="info-gerais-divisoria"></div>
          <span class="info-gerais-label">m²</span>
        </div>
        <div class="info-gerais-input">
          <input type="text" v-model="form.property_title" placeholder="Título" />
          <div class="info-gerais-divisoria"></div>
          <span class="info-gerais-label">Título</span>
        </div>
      </div>

      <!-- Detalhes (camas, banheiros, quartos) -->
      <div class="detalhes">
        <div class="detalhes-title">
          <p class="title-principal">Detalhes</p>
          <p class="subtitulo">Mais informações que serão exibidas no post.</p>
        </div>
        <div class="detalhes-cards">
          <div class="detalhes-card">
            <div class="detalhes-circle"><PhBed :size="28" /></div>
            <button @click="dec('beds_qtd')">-</button>
            <p>{{ form.beds_qtd }}</p>
            <button @click="inc('beds_qtd')">+</button>
          </div>
          <div class="detalhes-card">
            <div class="detalhes-circle"><PhBathtub :size="28" /></div>
            <button @click="dec('toilette')">-</button>
            <p>{{ form.toilette }}</p>
            <button @click="inc('toilette')">+</button>
          </div>
          <div class="detalhes-card">
            <div class="detalhes-circle"><PhDresser :size="28" /></div>
            <button @click="dec('dresser')">-</button>
            <p>{{ form.dresser }}</p>
            <button @click="inc('dresser')">+</button>
          </div>
        </div>
      </div>

      <!-- Comodidades -->
      <div class="mais-detalhes">
        <div class="mais-detalhes-title">
          <p class="title-principal">Mais detalhes</p>
          <p class="subtitulo">Comodidades da acomodação.</p>
        </div>
        <div class="mais-detalhes-options">
          <div class="esquerda">
            <label
              ><input type="checkbox" v-model="form.wifi" /><PhWifiHigh
                class="mais-detalhes-icons"
              />
              Wi-fi</label
            >
            <label
              ><input type="checkbox" v-model="form.tv" /><PhMonitor class="mais-detalhes-icons" />
              TV</label
            >
            <label
              ><input type="checkbox" v-model="form.cooler" /><PhSidebar
                class="mais-detalhes-icons"
              />
              Refrigerador</label
            >
            <label
              ><input type="checkbox" v-model="form.smoker" /><PhCigarette class="mais-detalhes-icons" /> Det.
              fumaça</label
            >
          </div>
          <div class="direita">
            <label
              ><input type="checkbox" v-model="form.air_conditioning" /><PhSnowflake
                class="mais-detalhes-icons"
              />
              Ar condicionado</label
            >
            <label
              ><input type="checkbox" v-model="form.washer" /><PhWashingMachine
                class="mais-detalhes-icons"
              />
              Máq. de lavar</label
            >
            <label
              ><input type="checkbox" v-model="form.microwave" /><PhHardDrive
                class="mais-detalhes-icons"
              />
              Micro-ondas</label
            >
          </div>
        </div>
      </div>

      <p v-if="erro" class="aviso aviso--erro">⚠️ {{ erro }}</p>
      <p v-if="sucesso" class="aviso aviso--ok">✅ Propriedade atualizada!</p>

      <button class="confirm-button" @click="salvar" :disabled="salvando">
        {{ salvando ? 'Salvando...' : 'Confirmar' }}
      </button>
    </div>
  </Transition>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.edit-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  z-index: 998;
}

.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.28s ease;
}
.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

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
  gap: 3rem;
  box-shadow: var(--shadow-md);
  color: var(--color-black-text);
  font-family: 'Poppins', sans-serif;
}

.drawer-enter-active,
.drawer-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(100%);
}

/* ── Cabeçalho ── */
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

.editor-icon {
  width: clamp(35px, 1.5vw, 40px);
}

/* ── Valores ── */
.valores,
.categoria,
.info-gerais,
.detalhes,
.mais-detalhes {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.valores-title,
.categoria-title,
.info-gerais-title,
.detalhes-title,
.mais-detalhes-title {
  display: flex;
  flex-direction: column;
}

.valores-cards,
.categoria-cards,
.detalhes-cards {
  width: 100%;
  display: flex;
  justify-content: space-around;
}

.valores-card {
  width: 30%;
  min-height: 120px;
  background-color: var(--color-bg);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  border-radius: 12px;
  overflow: hidden;
  font-weight: 500;
  cursor: pointer;
}

.valores-card-cima {
  width: 100%;
  padding: 0.8rem;
  box-sizing: border-box;
  display: flex;
  justify-content: space-around;
  border-bottom: 1px solid var(--color-border);
  font-size: 0.85rem;
  align-items: center;
}

.valores-card-baixo {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 8px;
}

.price-input {
  width: 100%;
  border: none;
  background: transparent;
  text-align: center;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--color-black-text);
  outline: none;
}

.price-placeholder {
  opacity: 0.35;
  font-size: 0.85rem;
}

/* ── Categoria ── */
.categoria-card {
  width: 30%;
  min-height: 60px;
  background-color: var(--color-bg);
  display: flex;
  justify-content: space-around;
  align-items: center;
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
}

.circle-categoria {
  aspect-ratio: 1/1;
  border: 0.5px solid var(--color-border-black);
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.categoria-icon {
  width: clamp(20px, 2.5rem, 40px);
}

/* ── Info gerais ── */
.info-gerais-inputs {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.info-gerais-input {
  width: 100%;
  display: flex;
  align-items: center;
  min-height: 52px;
  border-radius: 10px;
  box-shadow: var(--shadow-sm);
  background-color: var(--input-color);
  box-sizing: border-box;
  overflow: hidden;
}

.info-gerais-input input {
  flex: 1;
  min-width: 0;
  height: 52px;
  border: none;
  background: transparent;
  box-sizing: border-box;
  padding: 10px 14px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  color: var(--color-black-text);
  outline: none;
}

.info-gerais-divisoria {
  flex-shrink: 0;
  width: 1px;
  height: 60%;
  background: var(--color-border-black);
}

.info-gerais-label {
  flex-shrink: 0;
  padding: 0 14px;
  font-weight: 500;
  font-size: 0.88rem;
  color: var(--color-perm-black-text);
  white-space: nowrap;
}

/* ── Detalhes ── */
.detalhes-card {
  width: 30%;
  min-height: 55px;
  border-radius: 13px;
  display: flex;
  justify-content: space-around;
  align-items: center;
  background-color: var(--color-bg);
  box-shadow: var(--shadow-sm);
}

.detalhes-card button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-black-text);
  padding: 4px 8px;
}

/* ── Comodidades ── */
.mais-detalhes-options {
  width: 100%;
  display: flex;
  justify-content: space-around;
}

.esquerda,
.direita {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.esquerda label,
.direita label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1rem;
  cursor: pointer;
}

.esquerda label input,
.direita label input {
  accent-color: #1a2fa8;
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.mais-detalhes-icons {
  width: clamp(28px, 1.5vw, 36px);
}

.aviso {
  width: 100%;
  border-radius: 10px;
  padding: 10px 16px;
  font-size: 13px;
  margin: 0;
  box-sizing: border-box;
}

.aviso--erro {
  background: #fff8e1;
  border: 1px solid #ffe082;
  color: #7a5800;
}
.aviso--ok {
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  color: #2e7d32;
}

.confirm-button {
  width: 100%;
  padding: 0.9rem 0;
  border: 0;
  background-color: var(--color-primary);
  color: var(--color-primary-text);
  border-radius: 15px;
  cursor: pointer;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  font-size: 1rem;
  transition: opacity 0.2s;
}

.confirm-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

p {
  margin: 0;
}
.title-principal {
  font-size: 1.2rem;
  font-weight: 650;
}
.subtitulo {
  opacity: 0.6;
  font-weight: 500;
}
</style>
