<script setup lang="ts">
import { ref, onMounted } from 'vue'
import ProfileBioSkeleton from './ProfileBioSkeleton.vue'

const BASE = 'http://127.0.0.1:8000/api'

const bio = ref('')
const salvando = ref(false)
const sucesso = ref(false)
const erro = ref<string | null>(null)
const loadingBio = ref<boolean>(true)

onMounted(async () => {
  try {
    const res = await fetch(`${BASE}/user`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
      },
    })
    const data = await res.json()
    bio.value = data.bio ?? ''
  } catch (e) {
    console.error('[ProfileBio]', e)
  } finally {
    loadingBio.value = false
  }
})

async function salvar() {
  salvando.value = true
  sucesso.value = false
  erro.value = null
  try {
    const res = await fetch(`${BASE}/user`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify({ bio: bio.value }),
    })
    if (!res.ok) {
      const err = await res.json()
      throw err
    }
    sucesso.value = true
    setTimeout(() => (sucesso.value = false), 2500)
  } catch (e: any) {
    erro.value = e?.message ?? 'Erro ao salvar.'
  } finally {
    salvando.value = false
  }
}
</script>

<template>
  <ProfileBioSkeleton v-if="loadingBio === true" />

  <section v-else class="profile-bio">
    <h2 class="profile-bio__title">Bio</h2>

    <textarea
      class="profile-bio__textarea"
      placeholder="Um pouco sobre você..."
      v-model="bio"
      rows="6"
    />

    <p v-if="erro" class="profile-bio__aviso profile-bio__aviso--erro">⚠️ {{ erro }}</p>
    <p v-if="sucesso" class="profile-bio__aviso profile-bio__aviso--ok">✅ Bio salva!</p>

    <button class="profile-bio__btn" @click="salvar" :disabled="salvando">
      {{ salvando ? 'Salvando...' : 'Salvar bio' }}
    </button>
  </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.profile-bio {
  display: flex;
  flex-direction: column;
  gap: 12px;
  font-family: 'Poppins', sans-serif;
}

.profile-bio__title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
}

.profile-bio__textarea {
  width: 100%;
  box-sizing: border-box;
  border: 1.5px solid #e5e7eb;
  border-radius: 14px;
  padding: 0.85rem 1rem;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  color: var(--color-black-text, #1a1a2e);
  background: var(--color-bg-secondary, #fff);
  outline: none;
  resize: vertical;
  transition: border-color 0.18s;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
}

.profile-bio__textarea:focus {
  border-color: #1a2fa8;
}
.profile-bio__textarea::placeholder {
  color: #c0c0c0;
}

.profile-bio__aviso {
  margin: 0;
  font-size: 0.82rem;
  border-radius: 10px;
  padding: 8px 14px;
}

.profile-bio__aviso--erro {
  background: #fff8e1;
  border: 1px solid #ffe082;
  color: #7a5800;
}
.profile-bio__aviso--ok {
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  color: #2e7d32;
}

.profile-bio__btn {
  align-self: flex-start;
  padding: 0.7rem 1.6rem;
  border: none;
  border-radius: 12px;
  background: #1a2fa8;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.18s;
}

.profile-bio__btn:hover:not(:disabled) {
  background: #1527a0;
}
.profile-bio__btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
