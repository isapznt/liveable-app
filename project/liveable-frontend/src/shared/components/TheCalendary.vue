<template>
  <div class="calendario-wrapper">
    <div class="calendario-header">
      <h2 class="titulo">Calendário</h2>
      <p class="subtitulo">Dias Solicitados <span>(Em preto)</span></p>
    </div>

    <div class="calendario-grid">
      <button class="nav-btn nav-prev" @click="mesAnterior" aria-label="Mês anterior">
        &#8249;
      </button>

      <!-- Mês 1 -->
      <div class="mes-card">
        <div class="mes-titulo">{{ nomeMes(mes1) }} {{ ano1 }}</div>
        <div class="dias-semana">
          <span v-for="d in DIAS_SEMANA" :key="d">{{ d }}</span>
        </div>
        <div class="dias-grid">
          <span
            v-for="(dia, i) in diasDoMes(mes1, ano1)"
            :key="'m1-' + i"
            class="dia"
            :class="classeDia(dia, mes1, ano1)"
            @click="dia && !isDiaIndisponivel(dia, mes1, ano1) && selecionarDia(dia, mes1, ano1)"
            @mouseenter="dia && !isDiaIndisponivel(dia, mes1, ano1) && hoverDia(dia, mes1, ano1)"
            @mouseleave="hoverDate = null"
            :title="isDiaIndisponivel(dia!, mes1, ano1) ? 'Indisponível' : ''"
          >
            {{ dia || '' }}
          </span>
        </div>
      </div>

      <!-- Mês 2 -->
      <div class="mes-card">
        <div class="mes-titulo">{{ nomeMes(mes2) }} {{ ano2 }}</div>
        <div class="dias-semana">
          <span v-for="d in DIAS_SEMANA" :key="d">{{ d }}</span>
        </div>
        <div class="dias-grid">
          <span
            v-for="(dia, i) in diasDoMes(mes2, ano2)"
            :key="'m2-' + i"
            class="dia"
            :class="classeDia(dia, mes2, ano2)"
            @click="dia && !isDiaIndisponivel(dia, mes2, ano2) && selecionarDia(dia, mes2, ano2)"
            @mouseenter="dia && !isDiaIndisponivel(dia, mes2, ano2) && hoverDia(dia, mes2, ano2)"
            @mouseleave="hoverDate = null"
            :title="isDiaIndisponivel(dia!, mes2, ano2) ? 'Indisponível' : ''"
          >
            {{ dia || '' }}
          </span>
        </div>
      </div>

      <button class="nav-btn nav-next" @click="proximoMes" aria-label="Próximo mês">&#8250;</button>
    </div>

    <!-- Legenda -->
    <div class="legenda">
      <span class="legenda-item"> <span class="legenda-cor disponivel"></span> Disponível </span>
      <span class="legenda-item">
        <span class="legenda-cor selecionado-ex"></span> Selecionado
      </span>
      <span class="legenda-item">
        <span class="legenda-cor indisponivel-ex"></span> Indisponível
      </span>
    </div>

    <!-- Resumo da seleção -->
    <div v-if="dataInicio" class="resumo">
      <div class="resumo-item">
        <span class="resumo-label">Check-in</span>
        <span class="resumo-valor">{{ formatarData(dataInicio) }}</span>
      </div>
      <div class="resumo-separador">→</div>
      <div class="resumo-item">
        <span class="resumo-label">Check-out</span>
        <span class="resumo-valor">{{ dataFim ? formatarData(dataFim) : '—' }}</span>
      </div>
      <div v-if="dataInicio && dataFim" class="resumo-item">
        <span class="resumo-label">Total</span>
        <span class="resumo-valor">{{ totalDias }} dias</span>
      </div>
      <button class="btn-limpar" @click="limpar">Limpar</button>
    </div>

    <!-- Aviso de período bloqueado -->
    <div v-if="avisoConflito" class="aviso-conflito">
      ⚠️ O período selecionado contém datas indisponíveis. Por favor, escolha outro intervalo.
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

// ─── Tipos ─────────────────────────────────────────────────────────────────────

/**
 * Representa um período alugado/bloqueado vindo do banco de dados.
 * Aceita tanto string ISO (ex: "2025-07-10") quanto objeto Date.
 */
export interface PeriodoBloqueado {
  checkin: string | Date // data de entrada (inclusive)
  checkout: string | Date // data de saída (inclusive ou exclusive — veja isDiaIndisponivel)
}

// ─── Props ─────────────────────────────────────────────────────────────────────
const props = withDefaults(
  defineProps<{
    /**
     * Lista de períodos bloqueados/alugados do banco de dados.
     * Exemplo:
     *   [
     *     { checkin: '2025-07-10', checkout: '2025-07-15' },
     *     { checkin: new Date('2025-08-01'), checkout: new Date('2025-08-05') },
     *   ]
     */
    periodosBloqueados?: PeriodoBloqueado[]
  }>(),
  {
    periodosBloqueados: () => [],
  },
)

const emit = defineEmits<{
  updateDates: [{ checkin: string; checkout: string }]
}>()

// ─── Constantes ────────────────────────────────────────────────────────────────
const DIAS_SEMANA = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'] as const

const NOMES_MESES = [
  'Janeiro',
  'Fevereiro',
  'Março',
  'Abril',
  'Maio',
  'Junho',
  'Julho',
  'Agosto',
  'Setembro',
  'Outubro',
  'Novembro',
  'Dezembro',
] as const

// ─── Estado reativo ────────────────────────────────────────────────────────────
const hoje = new Date()
const mesBase = ref<number>(hoje.getMonth())
const anoBase = ref<number>(hoje.getFullYear())
const dataInicio = ref<Date | null>(null)
const dataFim = ref<Date | null>(null)
const hoverDate = ref<Date | null>(null)
const avisoConflito = ref<boolean>(false)

// ─── Computed ──────────────────────────────────────────────────────────────────
const mes1 = computed<number>(() => mesBase.value % 12)
const ano1 = computed<number>(() => anoBase.value + Math.floor(mesBase.value / 12))

const mes2 = computed<number>(() => (mesBase.value + 1) % 12)
const ano2 = computed<number>(() => anoBase.value + Math.floor((mesBase.value + 1) / 12))

const totalDias = computed<number>(() => {
  if (!dataInicio.value || !dataFim.value) return 0
  return Math.round((dataFim.value.getTime() - dataInicio.value.getTime()) / (1000 * 60 * 60 * 24))
})

/**
 * Pré-processa os períodos bloqueados normalizando para timestamps (ms).
 * Feito como computed para reprocessar automaticamente se a prop mudar.
 */
const periodosNormalizados = computed(() =>
  props.periodosBloqueados.map((p) => ({
    inicio: normalizar(p.checkin).getTime(),
    fim: normalizar(p.checkout).getTime(),
  })),
)

// ─── Helpers ───────────────────────────────────────────────────────────────────

/** Normaliza string ISO ou Date para Date zerado às 00:00:00 */
function normalizar(d: string | Date): Date {
  if (typeof d === 'string') {
    // Quebra "2026-06-10" em partes — sem deixar o Date inferir UTC
    const [ano, mes, dia] = d.split('T')[0].split('-').map(Number)
    return new Date(ano, mes - 1, dia) // mês é 0-indexed
  }
  return new Date(d.getFullYear(), d.getMonth(), d.getDate())
}

function toDate(dia: number, mes: number, ano: number): Date {
  return new Date(ano, mes, dia)
}

/**
 * Verifica se um dia específico está dentro de algum período bloqueado.
 * O intervalo bloqueado é INCLUSIVE em ambos os extremos (checkin e checkout).
 * Ajuste a condição `>= fim` para `> fim` se checkout for exclusivo no seu sistema.
 */
function isDiaIndisponivel(dia: number | null, mes: number, ano: number): boolean {
  if (!dia) return false
  const ts = toDate(dia, mes, ano).getTime()
  const tsHoje = new Date(hoje.getFullYear(), hoje.getMonth(), hoje.getDate()).getTime()
  if (ts < tsHoje) return true
  return periodosNormalizados.value.some((p) => ts >= p.inicio && ts <= p.fim)
}

/**
 * Verifica se há algum dia indisponível dentro de um intervalo de datas.
 * Usado para validar a seleção do usuário antes de confirmar.
 */
function intervaloTemConflito(inicio: Date, fim: Date): boolean {
  const msInicio = inicio.getTime()
  const msFim = fim.getTime()
  return periodosNormalizados.value.some((p) => p.inicio <= msFim && p.fim >= msInicio)
}

// ─── Navegação ─────────────────────────────────────────────────────────────────
function mesAnterior(): void {
  mesBase.value--
}
function proximoMes(): void {
  mesBase.value++
}
function nomeMes(mes: number): string {
  return NOMES_MESES[mes] ?? ''
}

function diasDoMes(mes: number, ano: number): (number | null)[] {
  const primeiroDia = new Date(ano, mes, 1).getDay()
  const totalDiasNoMes = new Date(ano, mes + 1, 0).getDate()
  const dias: (number | null)[] = []
  for (let i = 0; i < primeiroDia; i++) dias.push(null)
  for (let d = 1; d <= totalDiasNoMes; d++) dias.push(d)
  return dias
}

// ─── Seleção de datas ──────────────────────────────────────────────────────────
function selecionarDia(dia: number, mes: number, ano: number): void {
  const data = toDate(dia, mes, ano)
  avisoConflito.value = false

  // 1º clique ou reiniciando
  if (!dataInicio.value || (dataInicio.value && dataFim.value)) {
    dataInicio.value = data
    dataFim.value = null
    hoverDate.value = null
    return
  }

  // 2º clique: define o fim garantindo início < fim
  let inicio = dataInicio.value
  let fim = data

  if (data < dataInicio.value) {
    inicio = data
    fim = dataInicio.value
  }

  // Verifica conflito com períodos bloqueados
  if (intervaloTemConflito(inicio, fim)) {
    avisoConflito.value = true
    // Reinicia a seleção para o dia clicado como novo início
    dataInicio.value = data
    dataFim.value = null
    hoverDate.value = null
    return
  }

  dataInicio.value = inicio
  dataFim.value = fim
  hoverDate.value = null

  emit('updateDates', {
    checkin: formatarParaAPI(inicio),
    checkout: formatarParaAPI(fim),
  })
}

function hoverDia(dia: number, mes: number, ano: number): void {
  if (dataInicio.value && !dataFim.value) {
    hoverDate.value = toDate(dia, mes, ano)
  }
}

function classeDia(dia: number | null, mes: number, ano: number): string[] {
  if (!dia) return ['vazio']

  const data = toDate(dia, mes, ano)
  const inicio = dataInicio.value
  const fim = dataFim.value ?? hoverDate.value

  const classes: string[] = []

  // Indisponível (alugado)
  if (isDiaIndisponivel(dia, mes, ano)) {
    classes.push('indisponivel')
    return classes
  }

  const eInicio = !!inicio && data.getTime() === inicio.getTime()
  const eFim = !!fim && data.getTime() === fim.getTime()
  const noIntervalo = !!inicio && !!fim && data > inicio && data < fim

  if (eInicio) classes.push('inicio')
  if (eFim && dataFim.value) classes.push('fim')
  if (eFim && !dataFim.value && hoverDate.value) classes.push('hover-fim')
  if (noIntervalo) classes.push('no-intervalo')
  if (eInicio || (eFim && dataFim.value)) classes.push('selecionado')

  return classes
}

function formatarData(date: Date): string {
  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function formatarParaAPI(date: Date): string {
  return date.toISOString().split('T')[0]
}

function limpar(): void {
  dataInicio.value = null
  dataFim.value = null
  hoverDate.value = null
  avisoConflito.value = false
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

* {
  box-sizing: border-box;
}

.calendario-wrapper {
  width: 100%;
  user-select: none;
  font-family: 'Poppins', sans-serif;
}

.titulo {
  font-size: 22px;
  font-weight: 700;
  margin: 0 0 2px;
  color: var(--color-black-text);
}

.subtitulo {
  opacity: 0.6;
  font-weight: 500;
  margin: 0 0 24px;
}
.subtitulo span {
  color: #333;
  font-weight: 500;
}

.calendario-grid {
  display: grid;
  grid-template-columns: auto 1fr 1fr auto;
  gap: 16px;
  align-items: start;
}

.nav-btn {
  background: none;
  border: 1px solid #e0e0e0;
  border-radius: 50%;
  width: 34px;
  height: 34px;
  font-size: 20px;
  cursor: pointer;
  color: #444;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 4px;
  transition: background 0.15s;
}
.nav-btn:hover {
  background: #f5f5f5;
}

.mes-card {
  background: #fff;
  border: 1px solid #e8e8e8;
  border-radius: 14px;
  padding: 16px;
  box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
}

.mes-titulo {
  text-align: center;
  font-weight: 600;
  font-size: 14px;
  color: #111;
  margin-bottom: 14px;
}

.dias-semana {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  margin-bottom: 6px;
}
.dias-semana span {
  text-align: center;
  font-size: 11px;
  font-weight: 600;
  color: #aaa;
  padding: 2px 0;
  letter-spacing: 0.03em;
}

.dias-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}

.dia {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 34px;
  font-size: 13px;
  font-weight: 400;
  color: #222;
  cursor: pointer;
  border-radius: 50%;
  transition:
    background 0.1s,
    color 0.1s;
  z-index: 1;
}

.dia:hover:not(.vazio):not(.selecionado):not(.no-intervalo):not(.indisponivel) {
  background: #f0f0f0;
}

.dia.vazio {
  cursor: default;
}

/* ── Dias indisponíveis ───────────────────────────────────────────────────── */
.dia.indisponivel {
  cursor: not-allowed;
  color: #ccc;
  position: relative;
}

/* Linha diagonal sobre o número */
.dia.indisponivel::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 70%;
  height: 1px;
  background: #d0d0d0;
  transform: translate(-50%, -50%) rotate(-45deg);
  pointer-events: none;
}

/* ── Seleção ──────────────────────────────────────────────────────────────── */
.dia.no-intervalo {
  background: #1a1a1a;
  color: #fff;
  border-radius: 0;
}

.dia.selecionado {
  background: #111 !important;
  color: #fff !important;
  border-radius: 50% !important;
  font-weight: 600;
}

.dia.hover-fim {
  background: #555;
  color: #fff;
  border-radius: 50%;
}

.dia.inicio {
  background: #111;
  color: #fff;
  border-radius: 50% 0 0 50%;
}
.dia.fim {
  background: #111;
  color: #fff;
  border-radius: 0 50% 50% 0;
}
.dia.inicio.fim,
.dia.selecionado:not(.no-intervalo) {
  border-radius: 50% !important;
}

/* ── Legenda ──────────────────────────────────────────────────────────────── */
.legenda {
  display: flex;
  gap: 20px;
  margin-top: 16px;
  flex-wrap: wrap;
}
.legenda-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #666;
}
.legenda-cor {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  display: inline-block;
}
.legenda-cor.disponivel {
  background: #e8e8e8;
}
.legenda-cor.selecionado-ex {
  background: #111;
}
.legenda-cor.indisponivel-ex {
  background: #fff;
  border: 1px solid #ddd;
  position: relative;
  overflow: hidden;
}
.legenda-cor.indisponivel-ex::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 1px;
  background: #ccc;
  transform: translate(-50%, -50%) rotate(-45deg);
}

/* ── Resumo ───────────────────────────────────────────────────────────────── */
.resumo {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 20px;
  background: #f8f8f8;
  border: 1px solid #e8e8e8;
  border-radius: 12px;
  padding: 14px 20px;
  flex-wrap: wrap;
}
.resumo-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.resumo-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #999;
}
.resumo-valor {
  font-size: 14px;
  font-weight: 500;
  color: #111;
}
.resumo-separador {
  font-size: 18px;
  color: #ccc;
}
.btn-limpar {
  margin-left: auto;
  background: none;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 6px 14px;
  font-size: 13px;
  font-family: 'Poppins', sans-serif;
  cursor: pointer;
  color: #666;
  transition: background 0.15s;
}
.btn-limpar:hover {
  background: #eee;
}

/* ── Aviso de conflito ────────────────────────────────────────────────────── */
.aviso-conflito {
  margin-top: 12px;
  background: #fff8e1;
  border: 1px solid #ffe082;
  border-radius: 10px;
  padding: 10px 16px;
  font-size: 13px;
  color: #7a5800;
}
</style>
