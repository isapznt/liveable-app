<script setup lang="ts">
import { ref, onMounted } from 'vue'

const BASE = 'http://127.0.0.1:8000/api'
const token = () => localStorage.getItem('token') ?? ''

const headers = () => ({
  Authorization: `Bearer ${token()}`,
  Accept: 'application/json',
  'Content-Type': 'application/json',
})

// ── Tipos ──────────────────────────────────────────────
interface Stats {
  total_users: number
  total_properties: number
  total_reviews: number
  total_admins: number
  new_users_month: number
  new_props_month: number
}

interface UserRow {
  id: number
  name: string
  last_name: string
  email: string
  role: string
  created_at: string
}

interface PropertyRow {
  id: number
  property_title: string
  local: string
  type: string
  is_featured: boolean
}

// ── Estado ─────────────────────────────────────────────
const stats = ref<Stats | null>(null)
const users = ref<UserRow[]>([])
const allProperties = ref<PropertyRow[]>([])
const carregando = ref(true)
const carregandoProps = ref(true)
const filtro = ref('')
const filtroProps = ref('')

const form = ref({ name: '', last_name: '', email: '', password: '' })
const criando = ref(false)
const erroForm = ref<string | null>(null)
const okForm = ref(false)

const usersVisiveis = () =>
  users.value.filter((u) =>
    `${u.name} ${u.last_name} ${u.email}`.toLowerCase().includes(filtro.value.toLowerCase()),
  )

const propsVisiveis = () =>
  allProperties.value.filter((p) =>
    `${p.property_title} ${p.local} ${p.type}`.toLowerCase().includes(filtroProps.value.toLowerCase()),
  )

// ── Carregamento ───────────────────────────────────────
onMounted(async () => {
  try {
    const [sRes, uRes] = await Promise.all([
      fetch(`${BASE}/admin/stats`, { headers: headers() }),
      fetch(`${BASE}/admin/users`, { headers: headers() }),
    ])
    stats.value = await sRes.json()
    users.value = await uRes.json()
  } catch (e) {
    console.error('[AdminPanel] carregar dados', e)
  } finally {
    carregando.value = false
  }

  try {
    const res = await fetch(`${BASE}/properties`, { headers: headers() })
    allProperties.value = await res.json()
  } catch (e) {
    console.error('[AdminPanel] carregar propriedades', e)
  } finally {
    carregandoProps.value = false
  }
})

// ── Criar admin ────────────────────────────────────────
async function criarAdmin() {
  erroForm.value = null
  okForm.value = false
  criando.value = true

  try {
    const res = await fetch(`${BASE}/admin/create-admin`, {
      method: 'POST',
      headers: headers(),
      body: JSON.stringify(form.value),
    })
    const data = await res.json()

    if (!res.ok) {
      const msgs = typeof data === 'string' ? data : Object.values(data).flat().join(' ')
      throw new Error(msgs as string)
    }

    users.value.unshift(data.user)
    okForm.value = true
    form.value = { name: '', last_name: '', email: '', password: '' }
    if (stats.value) {
      stats.value.total_admins++
      stats.value.total_users++
    }
    setTimeout(() => (okForm.value = false), 2500)
  } catch (e: any) {
    erroForm.value = e.message ?? 'Erro ao criar admin.'
  } finally {
    criando.value = false
  }
}

// ── Toggle role ────────────────────────────────────────
async function toggleRole(user: UserRow) {
  const novoRole = user.role === 'admin' ? 'user' : 'admin'
  try {
    const res = await fetch(`${BASE}/admin/users/${user.id}/role`, {
      method: 'PATCH',
      headers: headers(),
      body: JSON.stringify({ role: novoRole }),
    })
    if (!res.ok) throw new Error()
    user.role = novoRole
    if (stats.value) {
      stats.value.total_admins += novoRole === 'admin' ? 1 : -1
    }
  } catch (e) {
    console.error('[toggleRole]', e)
  }
}

// ── Toggle featured ────────────────────────────────────
async function toggleFeatured(prop: PropertyRow) {
  const novoValor = !prop.is_featured
  try {
    const res = await fetch(`${BASE}/admin/properties/${prop.id}/featured`, {
      method: 'PATCH',
      headers: headers(),
      body: JSON.stringify({ is_featured: novoValor }),
    })
    if (!res.ok) throw new Error()
    prop.is_featured = novoValor
  } catch (e) {
    console.error('[toggleFeatured]', e)
  }
}
</script>

<template>
  <section class="admin">
    <div class="admin__title-row">
      <h2 class="admin__title">Painel de <span>Gerenciamento</span></h2>
    </div>

    <!-- Stats -->
    <div v-if="carregando" class="admin__loading">Carregando estatísticas...</div>

    <div v-else-if="stats" class="admin__stats">
      <div class="admin__stat">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
        </svg>
        <div class="admin__stat-info">
          <span class="admin__stat-label">Usuários</span>
          <span class="admin__stat-value">{{ stats.total_users }}</span>
        </div>
        <span class="admin__stat-sub">+{{ stats.new_users_month }} este mês</span>
      </div>

      <div class="admin__stat">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
        </svg>
        <div class="admin__stat-info">
          <span class="admin__stat-label">Imóveis</span>
          <span class="admin__stat-value">{{ stats.total_properties }}</span>
        </div>
        <span class="admin__stat-sub">+{{ stats.new_props_month }} este mês</span>
      </div>

      <div class="admin__stat">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
        </svg>
        <div class="admin__stat-info">
          <span class="admin__stat-label">Avaliações</span>
          <span class="admin__stat-value">{{ stats.total_reviews }}</span>
        </div>
      </div>

      <div class="admin__stat">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" />
        </svg>
        <div class="admin__stat-info">
          <span class="admin__stat-label">Admins</span>
          <span class="admin__stat-value">{{ stats.total_admins }}</span>
        </div>
      </div>
    </div>

    <!-- Propriedades em Alta -->
    <div class="admin__card">
      <div class="admin__card-head">
        <div>
          <h3 class="admin__card-title">Propriedades em Alta</h3>
          <p class="admin__card-desc">Marque as propriedades que aparecem no carrossel "Em Alta".</p>
        </div>
        <input class="admin__search" placeholder="Buscar imóvel..." v-model="filtroProps" />
      </div>

      <div v-if="carregandoProps" class="admin__loading">Carregando imóveis...</div>

      <div v-else class="admin__table-wrap">
        <table class="admin__table">
          <thead>
            <tr>
              <th>Título</th>
              <th>Local</th>
              <th>Tipo</th>
              <th>Em Alta</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="prop in propsVisiveis()" :key="prop.id">
              <td>{{ prop.property_title }}</td>
              <td class="admin__email">{{ prop.local }}</td>
              <td>
                <span class="admin__role-badge admin__role-badge--user">{{ prop.type }}</span>
              </td>
              <td>
                <button
                  class="admin__featured-btn"
                  :class="prop.is_featured ? 'admin__featured-btn--on' : 'admin__featured-btn--off'"
                  @click="toggleFeatured(prop)"
                >
                  {{ prop.is_featured ? '🔥 Em alta' : 'Destacar' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Criar novo admin -->
    <div class="admin__card">
      <h3 class="admin__card-title">Criar novo admin</h3>
      <div class="admin__form">
        <input class="admin__input" placeholder="Nome" v-model="form.name" />
        <input class="admin__input" placeholder="Sobrenome" v-model="form.last_name" />
        <input class="admin__input" placeholder="E-mail" type="email" v-model="form.email" />
        <input class="admin__input" placeholder="Senha (mín. 6 caracteres)" type="password" v-model="form.password" />
      </div>

      <p v-if="erroForm" class="admin__aviso admin__aviso--erro">⚠️ {{ erroForm }}</p>
      <p v-if="okForm"   class="admin__aviso admin__aviso--ok">✅ Admin criado com sucesso!</p>

      <button class="admin__btn" @click="criarAdmin" :disabled="criando">
        {{ criando ? 'Criando...' : 'Criar admin' }}
      </button>
    </div>

    <!-- Lista de usuários -->
    <div class="admin__card">
      <div class="admin__card-head">
        <h3 class="admin__card-title">Usuários cadastrados</h3>
        <input class="admin__search" placeholder="Buscar usuário..." v-model="filtro" />
      </div>

      <div class="admin__table-wrap">
        <table class="admin__table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>E-mail</th>
              <th>Role</th>
              <th>Cadastro</th>
              <th>Ação</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in usersVisiveis()" :key="u.id">
              <td>{{ u.name }} {{ u.last_name }}</td>
              <td class="admin__email">{{ u.email }}</td>
              <td>
                <span
                  class="admin__role-badge"
                  :class="u.role === 'admin' ? 'admin__role-badge--admin' : 'admin__role-badge--user'"
                >
                  {{ u.role }}
                </span>
              </td>
              <td class="admin__date">{{ new Date(u.created_at).toLocaleDateString('pt-BR') }}</td>
              <td>
                <button
                  class="admin__toggle-btn"
                  :class="u.role === 'admin' ? 'admin__toggle-btn--demote' : 'admin__toggle-btn--promote'"
                  @click="toggleRole(u)"
                >
                  {{ u.role === 'admin' ? 'Remover admin' : 'Tornar admin' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.admin {
  display: flex;
  flex-direction: column;
  gap: 28px;
  font-family: 'Poppins', sans-serif;
  width: 100%;
  margin-top: 1.5rem;
}

.admin__title-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.admin__title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
}

.admin__title span { color: #1a2fa8; }

h2 {
  position: relative;
  display: inline-block;
  font-size: 1.3rem;
  font-weight: 600;
}

h2::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: -3px;
  height: 3px;
  width: 75%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

h2::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: -3px;
  height: 3px;
  width: 20%;
  background-color: var(--color-primary);
  border-radius: 15px;
}

.admin__loading {
  font-size: 0.9rem;
  color: #9ca3af;
  padding: 12px 0;
}

.admin__stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}

.admin__stat {
  background: #fff;
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  position: relative;
  background: var(--color-bg-secondary);
}

.admin__stat svg {
  width: 28px;
  height: 28px;
  color: var(--color-primary);
  flex-shrink: 0;
}

.admin__stat-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.admin__stat-label {
  font-size: 11px;
  color: #9ca3af;
}

.admin__stat-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
  line-height: 1;
}

.admin__stat-sub {
  position: absolute;
  top: 12px;
  right: 14px;
  font-size: 10px;
  color: #22c55e;
  font-weight: 600;
}

.admin__card {
  background: #fff;
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  background: var(--color-bg-secondary);
}

.admin__card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.admin__card-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
}

.admin__card-desc {
  margin: 4px 0 0;
  font-size: 0.8rem;
  color: #9ca3af;
}

.admin__form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.admin__input,
.admin__search {
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  padding: 0.7rem 1rem;
  font-family: 'Poppins', sans-serif;
  font-size: 0.88rem;
  color: var(--color-perm-black-text, #1a1a2e);
  background: #f8f9fb;
  outline: none;
  transition: border-color 0.18s;
  box-sizing: border-box;
}

.admin__input:focus,
.admin__search:focus {
  border-color: #1a2fa8;
  background: #fff;
}

.admin__input::placeholder,
.admin__search::placeholder { color: #c0c0c0; }

.admin__search { width: 220px; }

.admin__aviso {
  margin: 0;
  font-size: 0.82rem;
  border-radius: 10px;
  padding: 8px 14px;
}
.admin__aviso--erro {
  background: #fff8e1;
  border: 1px solid #ffe082;
  color: #7a5800;
}
.admin__aviso--ok {
  background: #e8f5e9;
  border: 1px solid #a5d6a7;
  color: #2e7d32;
}

.admin__btn {
  align-self: flex-start;
  padding: 0.72rem 1.8rem;
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

.admin__btn:hover:not(:disabled) { background: #1527a0; }
.admin__btn:disabled { opacity: 0.6; cursor: not-allowed; }

.admin__table-wrap { overflow-x: auto; }

.admin__table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.86rem;
}

.admin__table th {
  text-align: left;
  padding: 10px 12px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #9ca3af;
  border-bottom: 1px solid #f3f4f6;
  white-space: nowrap;
}

.admin__table td {
  padding: 12px;
  color: var(--color-black-text, #1a1a2e);
  border-bottom: 1px solid #f9fafb;
  vertical-align: middle;
}

.admin__table tr:last-child td { border-bottom: none; }

.admin__email { color: #6b7280; font-size: 0.82rem; }
.admin__date  { color: #9ca3af; font-size: 0.8rem; white-space: nowrap; }

.admin__role-badge {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.admin__role-badge--admin { background: #e8eaf6; color: #1a2fa8; }
.admin__role-badge--user  { background: #f3f4f6; color: #6b7280; }

.admin__toggle-btn {
  border: 1.5px solid #e5e7eb;
  background: #fff;
  border-radius: 8px;
  padding: 4px 12px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s;
  white-space: nowrap;
}

.admin__toggle-btn--promote:hover { border-color: #1a2fa8; color: #1a2fa8; }
.admin__toggle-btn--demote:hover  { border-color: #dc2626; color: #dc2626; }

/* ── Featured button ── */
.admin__featured-btn {
  border: 1.5px solid #e5e7eb;
  background: #fff;
  border-radius: 8px;
  padding: 4px 12px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s;
  white-space: nowrap;
}

.admin__featured-btn--on {
  border-color: #f97316;
  background: #fff7ed;
  color: #c2410c;
}

.admin__featured-btn--on:hover {
  background: #ffedd5;
}

.admin__featured-btn--off {
  color: #6b7280;
}

.admin__featured-btn--off:hover {
  border-color: #f97316;
  color: #f97316;
}

@media (max-width: 900px) {
  .admin__stats    { grid-template-columns: repeat(2, 1fr); }
  .admin__form     { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
  .admin__stats { grid-template-columns: 1fr 1fr; }
}
</style>
