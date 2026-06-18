<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { getToken } from '@/services/auth'
import TheBannerSkeleton from './TheBannerSkeleton.vue'

const user = ref<any>(null)
const fileInputPhoto = ref<HTMLInputElement | null>(null)
const fileInputBanner = ref<HTMLInputElement | null>(null)
const salvando = ref(false)
const novaFotoPreview = ref<string | null>(null)
const novoBannerPreview = ref<string | null>(null)
const novaFotoFile = ref<File | null>(null)
const loadingBanner = ref<boolean>(true)

const banners = [
  'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800',
  'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
  'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800',
  'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800',
  'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800',
]

const avatars = [
  'https://i.pravatar.cc/150?img=1',
  'https://i.pravatar.cc/150?img=2',
  'https://i.pravatar.cc/150?img=3',
  'https://i.pravatar.cc/150?img=4',
  'https://i.pravatar.cc/150?img=5',
  'https://i.pravatar.cc/150?img=6',
]

const randomBanner = banners[Math.floor(Math.random() * banners.length)]
const randomAvatar = avatars[Math.floor(Math.random() * avatars.length)]

onMounted(async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/user', {
      headers: {
        Authorization: `Bearer ${getToken()}`,
        Accept: 'application/json',
      },
    })
    user.value = await res.json()
  } catch (error) {
    console.error('Erro ao buscar usuário', error)
  } finally {
    loadingBanner.value = false
  }
})

const profilePicture = computed(() => {
  if (novaFotoPreview.value) return novaFotoPreview.value
  if (user.value?.profile_picture) {
    const foto = user.value.profile_picture
    if (foto.startsWith('http://') || foto.startsWith('https://')) {
      return foto
    }
    return `http://127.0.0.1:8000/storage/${foto}`
  }
  return randomAvatar
})

const bannerAtual = computed(() => {
  if (novoBannerPreview.value) return novoBannerPreview.value
  if (user.value?.banner) return `http://127.0.0.1:8000/storage/${user.value.banner}`
  return randomBanner
})

function selecionarFoto() {
  fileInputPhoto.value?.click()
}

function selecionarBanner() {
  fileInputBanner.value?.click()
}

function onFotoSelecionada(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  novaFotoFile.value = file
  novaFotoPreview.value = URL.createObjectURL(file)
}

async function onBannerSelecionado(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file) return
  novoBannerPreview.value = URL.createObjectURL(file)

  const formData = new FormData()
  formData.append('banner', file)

  try {
    const res = await fetch('http://127.0.0.1:8000/api/user/banner', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${getToken()}`,
        Accept: 'application/json',
      },
      body: formData,
    })
    if (!res.ok) throw new Error()
    window.location.reload()
  } catch {
    console.error('Erro ao salvar banner')
  }
}

async function salvar() {
  if (!novaFotoFile.value) return
  salvando.value = true
  try {
    const formData = new FormData()
    formData.append('profile_picture', novaFotoFile.value)

    const res = await fetch('http://127.0.0.1:8000/api/user/photo', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${getToken()}`,
        Accept: 'application/json',
      },
      body: formData,
    })
    if (!res.ok) throw new Error()

    window.location.reload()
  } catch {
    console.error('Erro ao salvar foto')
  } finally {
    salvando.value = false
  }
}
</script>

<template>
  <TheBannerSkeleton v-if="loadingBanner === true" />

  <div v-else class="all">
    <!-- Banner -->
    <div class="banner" :style="{ backgroundImage: `url('${bannerAtual}')` }">
      <button class="banner-edit" @click="selecionarBanner" title="Trocar banner">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="15"
          height="15"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
      </button>
    </div>

    <!-- Foto de perfil -->
    <div
      class="profile-image"
      :style="{ backgroundImage: `url('${profilePicture}')` }"
      @click="selecionarFoto"
      title="Trocar foto"
    >
      <div class="profile-image__overlay">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="white"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path
            d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"
          />
          <circle cx="12" cy="13" r="4" />
        </svg>
      </div>
    </div>

    <!-- Inputs ocultos -->
    <input
      ref="fileInputPhoto"
      type="file"
      accept="image/*"
      style="display: none"
      @change="onFotoSelecionada"
    />
    <input
      ref="fileInputBanner"
      type="file"
      accept="image/*"
      style="display: none"
      @change="onBannerSelecionado"
    />

    <div class="padding">
      <div class="inform-texts">
        <p class="title">
          Aparência de Perfil - <span v-if="user">{{ user.name }} {{ user.last_name }}</span>
        </p>
        <p class="subtitle">Está foto é mostrada públicamente à outros usuários.</p>
      </div>

      <div class="confirm-buttons">
        <button class="button-new-photo" @click="selecionarFoto">Nova Foto</button>
        <button class="button-save" @click="salvar" :disabled="salvando">
          {{ salvando ? 'Salvando...' : 'Salvar' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

.all {
  width: 100%;
  height: auto;
  display: flex;
  flex-direction: column;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  gap: 1rem;
  position: relative;
  border-radius: 25px;
  contain: paint;
  box-shadow: var(--shadow-sm);
}

.banner {
  width: 100%;
  height: 150px;
  border-radius: 25px;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
  position: relative;
}

.banner-edit {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(26, 47, 168, 0.45);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  color: white;
  transition: background 0.2s;
}

.banner-edit:hover {
  background: rgba(26, 47, 168, 0.75);
}

.profile-image {
  width: clamp(7rem, 18%, 20rem);
  height: auto;
  aspect-ratio: 1/1;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
  position: absolute;
  transform: translateY(-50%);
  top: 150px;
  left: 28px;
  border-radius: 20px;
  cursor: pointer;
  overflow: hidden;
}

.profile-image__overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
  border-radius: 20px;
}

.profile-image:hover .profile-image__overlay {
  opacity: 1;
}

.inform-texts {
  width: 100%;
  height: auto;
  margin-top: 4.8rem;
  color: var(--color-black-text);
}

.inform-texts .title {
  font-size: clamp(1rem, 1.2rem + 0.2vw, 2rem);
  margin: 0;
}

.inform-texts .subtitle {
  font-size: clamp(0.6rem, 0.7rem + 0.3vw, 1.2rem);
  margin: 0;
  opacity: 0.6;
}

.confirm-buttons {
  width: 100%;
  height: auto;
  display: flex;
  gap: 10px;
  margin-bottom: 1.6rem;
}

.confirm-buttons button {
  width: clamp(120px, 20%, 200px);
  padding: 13px 0;
  cursor: pointer;
  border-radius: 10px;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  transition: background-color 0.5s;
}

.button-new-photo {
  outline: 0.5px solid var(--color-primary);
  color: var(--color-primary);
  background-color: transparent;
}

.button-new-photo:hover {
  background-color: var(--color-primary);
  outline: 0;
  color: var(--color-primary-text);
}

.button-save {
  background-color: var(--color-primary);
  color: var(--color-primary-text);
}

.button-save:hover:not(:disabled) {
  background-color: var(--color-primary-hover);
}

.button-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.padding {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: auto;
  gap: 1.5rem;
  box-sizing: border-box;
  padding: 0 1.7rem;
}

@media (max-width: 768px) {
  .all {
    aspect-ratio: auto;
    min-height: auto;
    width: 95%;
    margin: 0 auto;
  }
}
</style>
