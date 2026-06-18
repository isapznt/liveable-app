<script setup lang="ts">
import { ref, onMounted } from 'vue'
import ProfileInfosSkeleton from './ProfileInfosSkeleton.vue'

const BASE = 'http://127.0.0.1:8000/api'

const form = ref({ name: '', email: '', phone: '' })
const salvando = ref(false)
const sucesso = ref(false)
const erro = ref<string | null>(null)
const loadingProfileInfos = ref<boolean>(true)

onMounted(async () => {
  try {
    const res = await fetch(`${BASE}/user`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
      },
    })
    const data = await res.json()
    form.value.name = data.name ?? ''
    form.value.email = data.email ?? ''
    form.value.phone = data.phone ?? ''
  } catch (e) {
    console.error('[ProfileInfos]', e)
  } finally {
    loadingProfileInfos.value = false
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
      body: JSON.stringify(form.value),
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
  <ProfileInfosSkeleton v-if="loadingProfileInfos === true" />

  <section v-else class="profile-infos">
    <div class="profile-infos__head">
      <h2 class="profile-infos__title">Informações pessoais</h2>
      <p class="profile-infos__sub">Preencha com seus verdadeiros dados.</p>
    </div>

    <div class="profile-infos__fields">
      <div class="profile-infos__field">
        <label class="profile-infos__label">Nome completo</label>
        <input
          class="profile-infos__input"
          type="text"
          placeholder="Nome completo"
          v-model="form.name"
        />
      </div>

      <div class="profile-infos__field">
        <label class="profile-infos__label">Endereço de e-mail</label>
        <input
          class="profile-infos__input"
          type="email"
          placeholder="Endereço de Email"
          v-model="form.email"
        />
      </div>

      <div class="profile-infos__field">
        <label class="profile-infos__label">Número de celular</label>
        <input
          class="profile-infos__input"
          type="tel"
          placeholder="Número de celular"
          v-model="form.phone"
        />
      </div>
    </div>

    <p v-if="erro" class="profile-infos__aviso profile-infos__aviso--erro">⚠️ {{ erro }}</p>
    <p v-if="sucesso" class="profile-infos__aviso profile-infos__aviso--ok">
      ✅ Salvo com sucesso!
    </p>

    <button class="profile-infos__btn" @click="salvar" :disabled="salvando">
      {{ salvando ? 'Salvando...' : 'Salvar' }}
    </button>
  </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.profile-infos {
  display: flex;
  flex-direction: column;
  gap: 16px;
  font-family: 'Poppins', sans-serif;
}

.profile-infos__head {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.profile-infos__title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
}

.profile-infos__sub {
  margin: 0;
  font-size: 0.8rem;
  color: #9ca3af;
}

.profile-infos__fields {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.profile-infos__field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.profile-infos__label {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--color-black-text, #1a1a2e);
  opacity: 0.7;
}

.profile-infos__input {
  width: 100%;
  box-sizing: border-box;
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  color: var(--color-black-text, #1a1a2e);
  background: var(--color-bg-secondary, #fff);
  outline: none;
  transition: border-color 0.18s;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
}

.profile-infos__input:focus {
  border-color: #1a2fa8;
}
.profile-infos__input::placeholder {
  color: #c0c0c0;
}

.profile-infos__aviso {
  margin: 0;
  font-size: 0.82rem;
  border-radius: 10px;
  padding: 8px 14px;
}

.profile-infos__aviso--erro {
  background: #fff8e1;
  border: 1px solid #ffe082;
  color: #7a5800;
}
.profile-infos__aviso--ok {
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  color: #2e7d32;
}

.profile-infos__btn {
  width: 100%;
  padding: 0.85rem 0;
  border: none;
  border-radius: 14px;
  background: #1a2fa8;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.18s;
}

.profile-infos__btn:hover:not(:disabled) {
  background: #1527a0;
}
.profile-infos__btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
