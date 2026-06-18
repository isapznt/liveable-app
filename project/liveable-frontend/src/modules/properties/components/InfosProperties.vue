<script setup lang="ts">
import { ref } from 'vue'
import { onMounted, computed } from 'vue'
import api from '../services/api.ts'
import { useRoute } from 'vue-router'

const route = useRoute()
const propertyId = route.params.id

interface Property {
  local: string
  pricePerDay: number
  type: string
  rooms: number
}

const property = ref<Property | null>(null)

onMounted(async () => {
  try {
    const response = await api.get(`/property/${propertyId}`)

    property.value = response.data.Propriedade

    console.log(property.value)
  } catch (error) {
    console.error(error)
  }
})

const tipoFormatado = computed(() => {
  if (!property.value?.type) return ''

  return property.value.type.charAt(0).toUpperCase() +
         property.value.type.slice(1).toLowerCase()
})
</script>

<template>
  <div class="container-barra">
    <div class="barra-pesquisa">
      <div class="card-opcao">
        <i class="ICONE LUPA"></i>

        <div class="grupo-texto">
          <i class="fa-solid fa-magnifying-glass-location"></i>
          <div class="align-text">
            <span class="titulo">Localização</span>
            <span class="texto" :title="property?.local">{{ property?.local }}</span>
          </div>
        </div>
      </div>

      <div class="linha-divisoria"></div>

      <div class="card-opcao">
        <i class="ICONE CASA"></i>

        <div class="grupo-texto">
          <i class="fa-solid fa-house"></i>
          <div class="align-text">
            <span class="titulo">Tipo de imóvel</span>
            <span class="texto">{{ tipoFormatado }}</span>
          </div>
        </div>
      </div>

      <div class="linha-divisoria"></div>

      <div class="card-opcao">
        <i class="ICONE CAMA"></i>

        <div class="grupo-texto">
          <i class="fa-solid fa-bed"></i>
          <div class="align-text">
            <span class="titulo">Quantidade de quartos</span>
            <span class="texto">{{ property?.rooms }} Suítes</span>
          </div>
        </div>
      </div>

      <div class="linha-divisoria"></div>

      <div class="card-opcao card-preco">
        <div class="caixa-preco">
          <span class="preco">R$ {{ property?.pricePerDay }},00</span>
          <span class="noite">p/noite</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

.container-barra {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 10px 0;
  font-family: 'Poppins', sans-serif;
  color: var(--color-black-text);
}

.barra-pesquisa {
  display: flex;
  align-items: center;
  background: linear-gradient(160deg, #ffffff 0%, #f7f7fa 60%, #f2f2f6 100%);

  border-radius: 30px;
  border: 1px solid rgba(255, 255, 255, 0.95);
  outline: 1px solid rgba(0, 0, 0, 0.07);
  box-shadow:
    0 1px 2px rgba(0, 0, 0, 0.04),
    0 4px 12px rgba(0, 0, 0, 0.06),
    0 12px 32px rgba(0, 0, 0, 0.08),
    0 24px 56px rgba(0, 0, 0, 0.05),
    inset 0 1px 0 rgba(255, 255, 255, 1),
    inset 0 -1px 0 rgba(0, 0, 0, 0.025);

  padding: 10px;
  width: 100%;
  min-height: 96px;
  box-sizing: border-box;
  gap: 6px;
  background: var(--color-bg-secondary);
}

.card-opcao {
  display: flex;
  align-items: center;
  gap: 16px;
  flex: 1;
  min-width: 0;
  padding: 14px 26px;
  cursor: pointer;
  background: #ffffff;
  border-radius: 30px;
  border: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow:
    0 1px 3px rgba(0, 0, 0, 0.05),
    0 2px 8px rgba(0, 0, 0, 0.04),
    inset 0 1px 0 rgba(255, 255, 255, 1);
  transition:
    box-shadow 0.2s,
    opacity 0.15s,
    transform 0.2s;
  min-height: 72px;
  background: var(--color-bg);
}

.card-opcao:hover {
  box-shadow:
    0 2px 8px rgba(0, 0, 0, 0.09),
    0 4px 16px rgba(0, 0, 0, 0.07),
    inset 0 1px 0 rgba(255, 255, 255, 1);
  opacity: 0.9;
  transform: translateY(-1px);
}

.icone {
  font-size: 22px;
  color: #111;
  width: 32px;
  text-align: center;
  flex-shrink: 0;
}

.grupo-texto {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 1rem;
  font-size: 1.3rem;
  min-width: 0;
  width: 100%;
  background: var(--color-bg);
}

.grupo-texto i {
  padding-right: 1rem;
  border-right: 1px solid black;
}

.align-text {
  display: flex;
  flex-direction: column;
}

.titulo {
  font-size: 13px;
  font-weight: 500;
  color: var(--color-black-text);
  white-space: nowrap;
  letter-spacing: 0.01em;
}

.texto {
  font-size: 20px;
  font-weight: 700;
  color: var(--color-black-text);
  letter-spacing: -0.02em;

  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.align-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.linha-divisoria {
  width: 1px;
  height: 38px;
  background: linear-gradient(to bottom, transparent, #d4d4da 30%, #d4d4da 70%, transparent);
  flex-shrink: 0;
  margin: 0 2px;
}

.card-preco {
  flex: 0 0 auto;
  padding: 14px 30px;
  cursor: default;
  background: var(--color-bg);
}

.card-preco:hover {
  opacity: 1;
  transform: none;
  box-shadow:
    0 1px 3px rgba(0, 0, 0, 0.05),
    0 2px 8px rgba(0, 0, 0, 0.04),
    inset 0 1px 0 rgba(255, 255, 255, 1);
}

.caixa-preco {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 2px;
  color: var(--color-black-text);
}

.preco {
  font-size: 22px;
  font-weight: 800;
  color: var(--color-black-text);
  letter-spacing: -0.03em;
  line-height: 1.1;
}

.noite {
  font-size: 13px;
  font-weight: 500;
  color: #a0a0a8;
  letter-spacing: 0.01em;
}

@media (max-width: 900px) {
  .barra-pesquisa {
    flex-direction: column;
    border-radius: 28px;
    padding: 10px;
    align-items: stretch;
    gap: 6px;
    min-height: auto;
  }

  .card-opcao {
    border-radius: 18px;
    flex: unset;
  }

  .linha-divisoria {
    display: none;
  }

  .card-preco {
    padding: 14px 24px;
  }

  .caixa-preco {
    flex-direction: row;
    gap: 6px;
    align-items: baseline;
    justify-content: center;
  }

  .card-preco {
    justify-content: center;
  }
}
</style>
