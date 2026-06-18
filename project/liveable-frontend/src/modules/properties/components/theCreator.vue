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
import { ref } from 'vue'
import { exibirConfirm } from '../composables/useConfirmSolicitation'
import { getToken } from '../../../services/auth.js'

const tipoProp = ref<string | null>(null)

function toggleTipo(tipo: string) {
  tipoProp.value = tipoProp.value === tipo ? null : tipo
}

const tiposAtivos = ref<string[]>([])

function togglePreco(t: string) {
  if (tiposAtivos.value.includes(t)) {
    tiposAtivos.value = tiposAtivos.value.filter((x) => x !== t)
  } else {
    tiposAtivos.value.push(t)
  }
}

const valor_diario = ref<number | ''>('')
const valor_semanal = ref<number | ''>('')
const valor_mensal = ref<number | ''>('')

const camas = ref(1)
const banheiros = ref(1)
const quartos = ref(1)

function inc(campo: 'camas' | 'banheiros' | 'quartos') {
  if (campo === 'camas') camas.value++
  if (campo === 'banheiros') banheiros.value++
  if (campo === 'quartos') quartos.value++
}

function dec(campo: 'camas' | 'banheiros' | 'quartos') {
  if (campo === 'camas' && camas.value > 1) camas.value--
  if (campo === 'banheiros' && banheiros.value > 1) banheiros.value--
  if (campo === 'quartos' && quartos.value > 1) quartos.value--
}

const wifi = ref(false)
const tv = ref(false)
const refrigerador = ref(false)
const fumaca = ref(false)
const ar = ref(false)
const maquina_lavar = ref(false)
const micro_ondas = ref(false)

const loading = ref(false)
const erro = ref<string | null>(null)
const sucesso = ref(false)

const endereco = ref('')
const area_terreno = ref('')
const titulo = ref('')
const status = ref('Disponível')
const imagem = ref<File | null>(null)

function pegarImagem(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    imagem.value = target.files[0]
  }
}

async function salvarImovel() {
  loading.value = true
  erro.value = null
  sucesso.value = false

  try {
    const formData = new FormData()

    formData.append('type', tipoProp.value || '')
    formData.append('local', endereco.value)
    formData.append('area', area_terreno.value)
    formData.append('property_title', titulo.value)
    formData.append('pricePerDay', String(valor_diario.value || 0))
    formData.append('pricePerWeek', String(valor_semanal.value || 0))
    formData.append('pricePerMonth', String(valor_mensal.value || 0))
    formData.append('status', status.value)
    formData.append('beds_qtd', String(camas.value))
    formData.append('toilette', String(banheiros.value))
    formData.append('rooms', String(quartos.value))
    formData.append('wifi', wifi.value ? '1' : '0')
    formData.append('tv', tv.value ? '1' : '0')
    formData.append('cooler', refrigerador.value ? '1' : '0')
    formData.append('air_conditioning', ar.value ? '1' : '0')
    formData.append('washer', maquina_lavar.value ? '1' : '0')
    formData.append('microwave', micro_ondas.value ? '1' : '0')
    formData.append('smoker', fumaca.value ? '1' : '0')

    if (imagem.value) formData.append('images[]', imagem.value)

    const token = getToken()
    const response = await fetch('http://127.0.0.1:8000/api/property/store', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: formData,
    })

    const data = await response.json()
    if (!response.ok) throw new Error(data.message ?? `Erro ${response.status}`)

    sucesso.value = true
    setTimeout(() => {
      sucesso.value = false
      exibirConfirm()
    }, 1600)
  } catch (e: any) {
    erro.value = e.message ?? 'Erro ao salvar imóvel.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="all">
    <!-- Cabeçalho -->
    <div class="voltar" @click="exibirConfirm()">
      <PhCaretLeft :size="32" />
      <div class="circle-editor">
        <PhPencilSimpleLine class="editor-icon" />
      </div>
      <p>Criar Propriedade</p>
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
              v-model="valor_diario"
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
              v-model="valor_semanal"
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
              v-model="valor_mensal"
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
    <div class="info-gerais">
      <div class="info-gerais-title">
        <p class="title-principal">Informações gerais</p>
      </div>
      <div class="info-gerais-inputs">
        <div class="info-gerais-input">
          <input type="text" v-model="endereco" placeholder="Endereço" />
          <div class="info-gerais-divisoria"></div>
          <span class="info-gerais-label">Endereço</span>
        </div>
        <div class="info-gerais-input">
          <input type="number" v-model="area_terreno" placeholder="Área do terreno" />
          <div class="info-gerais-divisoria"></div>
          <span class="info-gerais-label">m²</span>
        </div>
        <div class="info-gerais-input">
          <input type="text" v-model="titulo" placeholder="Título" />
          <div class="info-gerais-divisoria"></div>
          <span class="info-gerais-label">Título</span>
        </div>
        <div class="info-gerais-input">
          <input type="file" accept="image/*" @change="pegarImagem" />
          <div class="info-gerais-divisoria"></div>
          <span class="info-gerais-label">Imagem</span>
        </div>
      </div>
    </div>

    <!-- Detalhes -->
    <div class="detalhes">
      <div class="detalhes-title">
        <p class="title-principal">Detalhes</p>
        <p class="subtitulo">Mais informações que serão exibidas no post.</p>
      </div>
      <div class="detalhes-cards">
        <div class="detalhes-card">
          <div class="detalhes-circle"><PhBed :size="28" /></div>
          <button @click="dec('camas')">-</button>
          <p>{{ camas }}</p>
          <button @click="inc('camas')">+</button>
        </div>
        <div class="detalhes-card">
          <div class="detalhes-circle"><PhBathtub :size="28" /></div>
          <button @click="dec('banheiros')">-</button>
          <p>{{ banheiros }}</p>
          <button @click="inc('banheiros')">+</button>
        </div>
        <div class="detalhes-card">
          <div class="detalhes-circle"><PhDresser :size="28" /></div>
          <button @click="dec('quartos')">-</button>
          <p>{{ quartos }}</p>
          <button @click="inc('quartos')">+</button>
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
            ><input type="checkbox" v-model="wifi" /><PhWifiHigh class="mais-detalhes-icons" />
            Wi-fi</label
          >
          <label
            ><input type="checkbox" v-model="tv" /><PhMonitor class="mais-detalhes-icons" />
            TV</label
          >
          <label
            ><input type="checkbox" v-model="refrigerador" /><PhSidebar
              class="mais-detalhes-icons"
            />
            Refrigerador</label
          >
          <label
            ><input type="checkbox" v-model="fumaca" /><PhCigarette class="mais-detalhes-icons" />
            Det. fumaça</label
          >
        </div>
        <div class="direita">
          <label
            ><input type="checkbox" v-model="ar" /><PhSnowflake class="mais-detalhes-icons" /> Ar
            condicionado</label
          >
          <label
            ><input type="checkbox" v-model="maquina_lavar" /><PhWashingMachine
              class="mais-detalhes-icons"
            />
            Máq. de lavar</label
          >
          <label
            ><input type="checkbox" v-model="micro_ondas" /><PhHardDrive
              class="mais-detalhes-icons"
            />
            Micro-ondas</label
          >
        </div>
      </div>
    </div>

    <p v-if="erro" class="aviso aviso--erro">⚠️ {{ erro }}</p>
    <p v-if="sucesso" class="aviso aviso--ok">✅ Imóvel criado com sucesso!</p>

    <button class="confirm-button" @click="salvarImovel" :disabled="loading">
      {{ loading ? 'Salvando...' : 'Confirmar' }}
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
  gap: 3rem;
  box-shadow: var(--shadow-md);
  color: var(--color-black-text);
  font-family: 'Poppins', sans-serif;
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

.editor-icon {
  width: clamp(35px, 1.5vw, 40px);
}

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

.info-gerais-input input[type='file'] {
  padding: 14px;
  font-size: 0.82rem;
  cursor: pointer;
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
