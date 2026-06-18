<script setup lang="ts">
import { PhHeart, PhPhone, PhCheck, PhWrench, PhTrash } from '@phosphor-icons/vue'
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import VueSlider from 'vue-slider-component'
import 'vue-slider-component/theme/default.css'

import InfosProperties from './InfosProperties.vue'
import { exibirConfirm } from '@/modules/properties/composables/useConfirmSolicitation.ts'
import AvaliationSession from './avaliation-session.vue'
import TheEditProperty from './TheEditor.vue'
import ConfirmDelete from '@/shared/components/ConfirmDelete.vue'

const carregando = ref<boolean>(true)

interface Property {
  id: number
  property_title: string
  local: string
  beds_qtd: number
  toilette: number
  area: number
  pricePerDay: number
  owner_contact: string | null
  user_id: number
  images: { url: string }[]
  user: {
    id: number
    name: string
    last_name: string
    profile_picture: string | null
    phone: string | null
  } | null
}

interface Me {
  id: number
  role: string
}

const route = useRoute()
const router = useRouter()
const propertyId = route.params.id

const property = ref<Property | null>(null)
const me = ref<Me | null>(null)
const valor = ref<number>(3)
const copiado = ref(false)
const editAberto = ref(false)
const confirmarDelete = ref(false)
const deletando = ref(false)

const isAdmin = computed(() => me.value?.role === 'admin')

const isOwner = computed(
  () => me.value !== null && property.value !== null && property.value.user_id === me.value.id,
)

const podeGerenciar = computed(() => isAdmin.value || isOwner.value)

onMounted(async () => {
  const token = localStorage.getItem('token')
  const headers: Record<string, string> = { Accept: 'application/json' }

  if (token) headers['Authorization'] = `Bearer ${token}`

  try {
    const [resProperty, resMe] = await Promise.all([
      fetch(`http://127.0.0.1:8000/api/property/${propertyId}`, { headers }),
      token ? fetch(`http://127.0.0.1:8000/api/user`, { headers }) : null,
    ])

    const dataProperty = await resProperty.json()
    property.value = dataProperty.Propriedade

    if (resMe && resMe.ok) {
      me.value = await resMe.json()
    }
  } catch (error) {
    console.error('Erro ao carregar dados da página de detalhes:', error)
  } finally {
    carregando.value = false
  }
})

async function copiarContato() {
  const contato = property.value?.user?.phone
  if (!contato) return
  try {
    await navigator.clipboard.writeText(contato)
    copiado.value = true
    setTimeout(() => (copiado.value = false), 2000)
  } catch {
    console.error('Erro ao copiar')
  }
}

function verPerfil() {
  if (property.value?.user) router.push(`/perfil/${property.value.user.id}`)
}

async function aoSalvar() {
  const token = localStorage.getItem('token')
  const headers: Record<string, string> = { Accept: 'application/json' }
  if (token) headers['Authorization'] = `Bearer ${token}`
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/property/${propertyId}`, { headers })
    const data = await res.json()
    property.value = data.Propriedade
  } catch (e) {
    console.error(e)
  }
}

async function deletarImovel() {
  deletando.value = true
  const token = localStorage.getItem('token')
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/property/delete/${propertyId}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })
    if (!res.ok) throw new Error()
    router.push('/')
  } catch {
    deletando.value = false
  } finally {
    confirmarDelete.value = false
  }
}

import { useFavorites } from '@/modules/favorites/composables/useFavorites'
const { isFavorite, toggleFavorite } = useFavorites()

const logado = computed(() => !!localStorage.getItem('token'))

async function handleFav() {
  if (!logado.value) {
    router.push('/login')
    return
  }
  await toggleFavorite(Number(propertyId))
}

function handleSolicitar() {
  if (!logado.value) {
    router.push('/login')
    return
  }
  exibirConfirm()
}
</script>

<template>
  <div v-if="carregando" class="skeleton-container">
    <div class="skeleton-title"></div>

    <div class="skeleton-details">
      <div class="skeleton-image"></div>

      <div class="skeleton-info">
        <div class="skeleton-line grande"></div>
        <div class="skeleton-line"></div>
        <div class="skeleton-line"></div>
        <div class="skeleton-line"></div>

        <div class="skeleton-button"></div>
      </div>
    </div>
  </div>

  <div v-else class="all">
    <div class="home-title">
      <p>{{ property?.property_title }}</p>
      <div v-if="podeGerenciar" class="gerenciar-btns">
        <button class="btn-editar" @click="editAberto = true">
          <PhWrench :size="15" weight="bold" />
          Editar
        </button>
        <button class="btn-deletar" @click="confirmarDelete = true" :disabled="deletando">
          <PhTrash :size="15" weight="bold" />
          Deletar
        </button>
      </div>
    </div>

    <div class="home-details">
      <div
        class="home-photo"
        :style="
          property?.images?.[0]?.url
            ? { backgroundImage: `url('${encodeURI(property.images[0].url)}')` }
            : {}
        "
      />

      <div class="home-informations">
        <div class="ende">
          <div class="casa-endereco" :title="property?.local">
            <p>{{ property?.local }}</p>
          </div>
          <div class="fav" @click="handleFav" :class="{ ativo: isFavorite(Number(propertyId)) }">
            <PhHeart weight="fill" class="icon-fav" />
          </div>
        </div>

        <div class="info">
          <p>{{ property?.beds_qtd }} Camas</p>
          <div class="divisoria"></div>
          <p>{{ property?.toilette }} Banheiros</p>
          <div class="divisoria"></div>
          <p>{{ property?.area }} m²</p>
        </div>

        <div class="simulation">
          <p>Simulação de preço: R$ {{ valor * (property?.pricePerDay || 0) }}</p>
          <div class="juntar">
            <VueSlider
              class="slider"
              :min="0"
              :max="10"
              v-model="valor"
              :tooltip="'always'"
              tooltip-placement="bottom"
            />
            <p class="n-baixo">N. de dias</p>
          </div>
        </div>

        <div class="contact">
          <div class="contato-escrita">Contato:</div>
          <div class="card-contato" @click="copiarContato" :class="{ copiado }">
            <div
              class="img"
              :class="{ semFoto: !property?.user?.profile_picture }"
              @click.stop="verPerfil"
              :style="
                property?.user?.profile_picture
                  ? {
                      backgroundImage: `url('${
                        property.user.profile_picture.startsWith('http://') ||
                        property.user.profile_picture.startsWith('https://')
                          ? property.user.profile_picture
                          : `http://127.0.0.1:8000/storage/${property.user.profile_picture}`
                      }')`,
                    }
                  : {}
              "
            >
              <span v-if="!property?.user?.profile_picture">
                {{ property?.user?.name?.charAt(0) }}
              </span>
            </div>
            <p @click.stop="verPerfil">
              {{ property?.user?.name }} {{ property?.user?.last_name }}
            </p>
            <div class="phone-icon-wrap" :class="{ copiado }">
              <PhCheck v-if="copiado" class="icon-phone" weight="bold" />
              <PhPhone v-else class="icon-phone" />
            </div>
          </div>
          <Transition name="toast">
            <div v-if="copiado" class="toast">📋 Número copiado para a área de transferência!</div>
          </Transition>
        </div>

        <button class="btn-solicitar" @click="handleSolicitar">Solicitar</button>
      </div>
    </div>

    <InfosProperties />
    <AvaliationSession
      :property-id="Number(propertyId)"
      :price-per-night="property?.pricePerDay ?? 0"
    />

    <TheEditProperty
      v-if="podeGerenciar"
      :property-id="Number(propertyId)"
      :open="editAberto"
      @close="editAberto = false"
      @saved="aoSalvar"
    />

    <ConfirmDelete
      :open="confirmarDelete"
      :titulo="property?.property_title ?? ''"
      :deletando="deletando"
      @cancelar="confirmarDelete = false"
      @confirmar="deletarImovel"
    />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap');

.all {
  width: 100%;
  display: flex;
  flex-direction: column;
  font-family: 'Poppins', sans-serif;
  gap: 20px;
}

.home-title {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 16px;
  font-weight: 600;
  font-size: clamp(1.6rem, 3vw, 2.8rem);
  color: var(--color-black-text);
}

.home-title p {
  margin: 0;
}

.gerenciar-btns {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.btn-editar,
.btn-deletar {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 16px;
  border-radius: 12px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition:
    border-color 0.18s,
    box-shadow 0.18s,
    color 0.18s;
  white-space: nowrap;
}

.btn-editar {
  background: var(--color-primary);
  border: 0;
  color: var(--color-primary-text, #1a1a2e);
}

.btn-editar:hover {
  box-shadow: 0 2px 8px rgba(26, 47, 168, 0.12);
}

.btn-deletar {
  background: #fff;
  border: 1.5px solid #e5e7eb;
  color: #dc2626;
}

.btn-deletar:hover:not(:disabled) {
  border-color: #dc2626;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.12);
}

.btn-deletar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.home-details {
  width: 100%;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.home-photo {
  width: 64%;
  aspect-ratio: 16 / 9;
  border-radius: 30px;
  box-shadow: var(--shadow-sm);
  background-size: cover;
  background-position: center;
}

.home-informations {
  width: 35%;
  border-radius: 30px;
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  box-shadow: var(--shadow-sm);
  box-sizing: border-box;
  padding: 40px;
  gap: 30px;
  background-color: var(--color-bg-secondary);
  color: var(--color-black-text);
}

.home-informations .ende {
  width: 100%;
  min-height: 60px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 15px;
}

.ende p {
  margin: 0;
}

.fav {
  width: 3rem;
  height: 3rem;
  background-color: var(--color-bg);
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.3s;
  cursor: pointer;
  flex-shrink: 0;
}
.fav:hover {
  box-shadow: var(--shadow-hover-blue);
}
.icon-fav {
  color: var(--color-icon-inactive);
  width: clamp(35px, 2.5vw, 40px);
  height: clamp(35px, 2.5vw, 40px);
}

.fav.ativo .icon-fav {
  color: var(--color-primary);
}
.icon-fav {
  transition: color 0.2s;
}

.casa-endereco {
  flex: 1;
  min-width: 0;
  display: flex;
  align-items: center;
  font-size: clamp(1.6rem, 1.5vw, 2.2rem);
  font-weight: 600;
}

.home-informations .info {
  width: 100%;
  min-height: 55px;
  display: flex;
  align-items: center;
  justify-content: space-around;
  border-radius: 20px;
  padding: 10px;
  box-sizing: border-box;
  font-weight: 600;
  box-shadow: var(--shadow-sm);
  font-size: clamp(0.9rem, 0.8vw, 1.6rem);
}

.casa-endereco p {
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.info .divisoria {
  height: 3rem;
  flex-shrink: 0;
  border-radius: 20px;
  width: 1px;
  background-color: var(--color-border-black);
}

.home-informations .simulation {
  width: 100%;
  min-height: 70px;
  font-size: clamp(1.1rem, 1.1vw, 1.8rem);
  text-wrap: nowrap;
}
.slider {
  width: 100%;
}

:deep(.vue-slider-dot-tooltip-text) {
  font-size: clamp(1.1rem, 1vw, 30px);
}
:deep(.vue-slider-process) {
  background: var(--color-primary);
}
:deep(.vue-slider-rail) {
  background: var(--vue-slider-trilha-color);
}
:deep(.vue-slider-dot-tooltip-inner) {
  background-color: transparent;
  color: var(--color-black-text);
}
:deep(.vue-slider-dot-tooltip-inner-bottom::after) {
  border-bottom-color: var(--color-primary);
}

.juntar {
  display: flex;
  flex-direction: column;
  position: relative;
}
.n-baixo {
  margin: 0;
  position: absolute;
  right: 0;
  top: 70%;
  font-size: clamp(1rem, 1vw, 1.5rem);
  opacity: 0.6;
}

.home-informations .contact {
  width: 100%;
  gap: 10px;
  display: flex;
  flex-direction: column;
  position: relative;
}
.contato-escrita {
  min-height: 30px;
  display: flex;
  align-items: center;
  font-size: clamp(1rem, 1vw, 1.6rem);
}

.card-contato {
  width: 100%;
  min-height: 60px;
  display: flex;
  justify-content: space-around;
  align-items: center;
  font-size: clamp(1.1rem, 1.05vw, 1.6rem);
  font-weight: 500;
  border-radius: 15px;
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  transition:
    box-shadow 0.3s,
    background-color 0.3s;
}
.card-contato:hover {
  box-shadow: var(--shadow-hover-blue);
}
.card-contato.copiado {
  background-color: #e8f5e9;
}

.card-contato .img {
  height: 3rem;
  aspect-ratio: 1/1;
  border-radius: 50%;
  background-position: center;
  background-size: cover;
  cursor: pointer;
}
.card-contato .img.semFoto {
  background-color: #cfcfcf;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
  font-weight: 600;
}
.card-contato p {
  cursor: pointer;
  margin: 0;
}

.phone-icon-wrap {
  width: clamp(1.8rem, 1vw, 2.2rem);
  height: clamp(1.8rem, 1vw, 2.2rem);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.3s;
}
.phone-icon-wrap.copiado {
  color: #2e7d32;
}
.icon-phone {
  width: 100%;
  height: 100%;
}

.toast {
  position: absolute;
  bottom: -2.8rem;
  left: 50%;
  transform: translateX(-50%);
  background: #1a2fa8;
  color: white;
  padding: 8px 18px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
  box-shadow: 0 4px 15px rgba(26, 47, 168, 0.35);
}
.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 0.3s,
    transform 0.3s;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(-50%) translateY(6px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(6px);
}

.btn-solicitar {
  width: 100%;
  min-height: 65px;
  background-color: var(--color-primary);
  border-radius: 20px;
  border: none;
  color: white;
  font-family: 'Poppins', sans-serif;
  font-size: clamp(1.1rem, 1.15vw, 30px);
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
}
.btn-solicitar:hover {
  background-color: var(--color-primary-hover);
}

@media (max-width: 768px) {
  .all {
    width: 95%;
    margin-top: 7vw;
    margin-bottom: 7vw;
  }
  .home-title {
    flex-wrap: wrap;
  }
  .gerenciar-btns {
    width: 100%;
  }
  .home-details {
    flex-direction: column;
    gap: 15px;
  }
  .home-photo {
    width: 100%;
    aspect-ratio: 16/9;
  }
  .home-informations {
    width: 100%;
    gap: 40px;
    padding-bottom: 40px;
    padding-top: 40px;
  }
}

/* O skeleton loading */

.skeleton-title,
.skeleton-image,
.skeleton-line,
.skeleton-button {
  background: linear-gradient(90deg, #e5e5e5 25%, #f5f5f5 50%, #e5e5e5 75%);

  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  from {
    background-position: 200% 0;
  }

  to {
    background-position: -200% 0;
  }
}

.skeleton-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.skeleton-title {
  width: 40%;
  height: 50px;
  border-radius: 10px;
}

.skeleton-details {
  display: flex;
  justify-content: space-between;
}

.skeleton-image {
  width: 64%;
  aspect-ratio: 16/9;
  border-radius: 30px;
}

.skeleton-info {
  width: 35%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.skeleton-line {
  height: 50px;
  border-radius: 15px;
}

.skeleton-line.grande {
  height: 80px;
}

.skeleton-button {
  height: 65px;
  border-radius: 20px;
}
</style>
