<script setup lang="ts">
import { ref, onMounted } from 'vue'
import ProfileSocialLinksSkeleton from './ProfileSocialLinksSkeleton.vue'

const BASE = 'http://127.0.0.1:8000/api'

const compartilhar = ref(true)
const links = ref({ twitter: '', instagram: '', facebook: '' })
const salvando = ref(false)
const sucesso = ref(false)
const erro = ref<string | null>(null)
const loadingProfileSocial = ref<boolean>(true)

onMounted(async () => {
  try {
    const res = await fetch(`${BASE}/user`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
      },
    })
    const data = await res.json()
    links.value.twitter = data.twitter ?? ''
    links.value.instagram = data.instagram ?? ''
    links.value.facebook = data.facebook ?? ''
    compartilhar.value = data.share_socials ?? true
  } catch (e) {
    console.error('[ProfileSocialLinks]', e)
  } finally {
    loadingProfileSocial.value = false
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
      body: JSON.stringify({ ...links.value, share_socials: compartilhar.value }),
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

const socials = [
  {
    key: 'twitter' as const,
    prefix: 'x.com/',
    placeholder: 'nome_de_usuario',
    icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.261 5.632zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>`,
  },
  {
    key: 'instagram' as const,
    prefix: 'instagram.com/',
    placeholder: 'nome_de_usuario',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>`,
  },
  {
    key: 'facebook' as const,
    prefix: 'facebook.com/',
    placeholder: 'nome.do.perfil',
    icon: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>`,
  },
]
</script>

<template>
  <ProfileSocialLinksSkeleton v-if="loadingProfileSocial === true" />

  <section v-else class="profile-social">
    <div class="profile-social__head">
      <h2 class="profile-social__title">Redes Sociais</h2>
      <label class="profile-social__toggle">
        <input type="checkbox" v-model="compartilhar" />
        <span class="profile-social__toggle-label">Deseja compartilhar com terceiros?</span>
      </label>
    </div>

    <div class="profile-social__fields">
      <div v-for="s in socials" :key="s.key" class="profile-social__row">
        <span class="profile-social__icon" v-html="s.icon" />
        <div class="profile-social__input-wrap">
          <span class="profile-social__prefix">{{ s.prefix }}</span>
          <input
            class="profile-social__input"
            type="text"
            :placeholder="s.placeholder"
            v-model="links[s.key]"
          />
        </div>
      </div>
    </div>

    <p v-if="erro" class="profile-social__aviso profile-social__aviso--erro">⚠️ {{ erro }}</p>
    <p v-if="sucesso" class="profile-social__aviso profile-social__aviso--ok">✅ Links salvos!</p>

    <button class="profile-social__btn" @click="salvar" :disabled="salvando">
      {{ salvando ? 'Salvando...' : 'Salvar redes' }}
    </button>
  </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.profile-social {
  display: flex;
  flex-direction: column;
  gap: 16px;
  font-family: 'Poppins', sans-serif;
}

.profile-social__head {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.profile-social__title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
}

.profile-social__toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.profile-social__toggle input[type='checkbox'] {
  accent-color: #1a2fa8;
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.profile-social__toggle-label {
  font-size: 0.82rem;
  color: #6b7280;
}

.profile-social__fields {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.profile-social__row {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--color-bg-secondary, #fff);
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.65rem 1rem;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
  transition: border-color 0.18s;
}

.profile-social__row:focus-within {
  border-color: #1a2fa8;
}

.profile-social__icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  color: #6b7280;
  display: flex;
  align-items: center;
}

.profile-social__icon :deep(svg) {
  width: 18px;
  height: 18px;
}

.profile-social__input-wrap {
  display: flex;
  align-items: center;
  flex: 1;
  gap: 2px;
  min-width: 0;
}

.profile-social__prefix {
  font-size: 0.88rem;
  color: #9ca3af;
  white-space: nowrap;
  flex-shrink: 0;
}

.profile-social__input {
  border: none;
  outline: none;
  background: transparent;
  font-family: 'Poppins', sans-serif;
  font-size: 0.88rem;
  color: var(--color-black-text, #1a1a2e);
  width: 100%;
  min-width: 0;
}

.profile-social__input::placeholder {
  color: #c0c0c0;
}

.profile-social__aviso {
  margin: 0;
  font-size: 0.82rem;
  border-radius: 10px;
  padding: 8px 14px;
}

.profile-social__aviso--erro {
  background: #fff8e1;
  border: 1px solid #ffe082;
  color: #7a5800;
}
.profile-social__aviso--ok {
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  color: #2e7d32;
}

.profile-social__btn {
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

.profile-social__btn:hover:not(:disabled) {
  background: #1527a0;
}
.profile-social__btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
