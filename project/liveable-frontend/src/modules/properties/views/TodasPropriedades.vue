<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import CardCasa from '@/modules/properties/components/CardCasa.vue'
import CardCasaSkeleton from '@/modules/properties/components/CardCasaSkeleton.vue'

interface Property {
  id: number
  property_title: string
  pricePerDay: number
  avaliation: number
  type: string
  images: { url: string }[]
}

const route = useRoute()

const properties = ref<Property[]>([])
const carregando = ref(true)

onMounted(async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/properties')
    const data     = await response.json()
    properties.value = data
  } catch (e) {
    console.error(e)
  } finally {
    carregando.value = false
  }
})

// Lê query params reativamente
const searchQuery = computed(() => (route.query.q    as string) ?? '')
const filterType  = computed(() => (route.query.type as string) ?? '')

const filtered = computed(() => {
  let list = properties.value

  if (filterType.value) {
    const tipos = filterType.value.split(',').map(t => t.toLowerCase().trim())
    list = list.filter(p => tipos.includes(p.type?.toLowerCase()))
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(p =>
      p.property_title?.toLowerCase().includes(q) ||
      p.type?.toLowerCase().includes(q)
    )
  }

  return list
})

// Labels amigáveis para o tipo
const tipoLabel: Record<string, string> = {
  casa:        'Casas',
  apartamento: 'Apartamentos',
  chacara:     'Chácaras',
}

// "casa,apartamento" → "Casas e Apartamentos"
function formatarTipos(tipos: string): string {
  const labels = tipos.split(',').map(t => tipoLabel[t.trim()] ?? t.trim())
  if (labels.length === 1) return labels[0]
  const ultimo = labels.pop()
  return `${labels.join(', ')} e ${ultimo}`
}

const tiposFormatados = computed(() =>
  filterType.value ? formatarTipos(filterType.value) : ''
)

// Lista individual para renderizar uma tag por tipo
const tiposAtivos = computed(() =>
  filterType.value ? filterType.value.split(',').map(t => t.trim()) : []
)

const titulo = computed(() => {
  if (searchQuery.value && filterType.value)
    return `"${searchQuery.value}" em ${tiposFormatados.value}`
  if (searchQuery.value)
    return `Resultados para "${searchQuery.value}"`
  if (filterType.value)
    return tiposFormatados.value
  return 'Todas as propriedades'
})
</script>

<template>
  <div class="page">
    <!-- Cabeçalho -->
    <div class="header">
      <div class="header-texto">
        <h2 class="titulo">{{ titulo }}</h2>
        <p class="subtitulo" v-if="!carregando">
          {{ filtered.length }} {{ filtered.length === 1 ? 'imóvel encontrado' : 'imóveis encontrados' }}
        </p>
      </div>

      <!-- Tags de filtros ativos -->
      <div class="tags" v-if="tiposAtivos.length || searchQuery">
        <span v-for="tipo in tiposAtivos" :key="tipo" class="tag">
          <i class="fa-solid fa-tag"></i>
          {{ tipoLabel[tipo] ?? tipo }}
        </span>
        <span v-if="searchQuery" class="tag">
          <i class="fa-solid fa-magnifying-glass"></i>
          {{ searchQuery }}
        </span>
      </div>
    </div>

    <!-- Grid -->
    <div class="grid" v-if="carregando">
      <CardCasaSkeleton v-for="n in 8" :key="n" />
    </div>

    <div class="grid" v-else-if="filtered.length">
      <CardCasa v-for="casa in filtered" :key="casa.id" :casa="casa" />
    </div>

    <div class="vazio" v-else>
      <i class="fa-solid fa-house-circle-xmark"></i>
      <p>Nenhum imóvel encontrado</p>
      <span>Tente outros termos ou remova os filtros</span>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.page {
  width: 100%;
  min-height: 100vh;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
  color: var(--color-black-text);
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* ── Header ── */
.header {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.titulo {
  position: relative;
  display: inline-block;
  font-size: 1.3rem;
  font-weight: 600;
  margin: 0;
}

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

.subtitulo {
  font-size: 0.9rem;
  opacity: 0.5;
  margin: 0.4rem 0 0;
}

/* ── Tags ── */
.tags {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 0.6rem;
}

.tag {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--color-primary);
  color: var(--color-primary-text);
  border-radius: 999px;
  padding: 4px 14px;
  font-size: 12px;
  font-weight: 500;
}

/* ── Grid ── */
.grid {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

/* ── Vazio ── */
.vazio {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  opacity: 0.4;
  padding: 4rem 0;
}

.vazio i {
  font-size: 3rem;
}

.vazio p {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
}

.vazio span {
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .page {
    padding: 1.5rem 1rem;
  }

  .titulo {
    font-size: 1.3rem;
  }

  .grid {
    justify-content: center;
  }
}
</style>
