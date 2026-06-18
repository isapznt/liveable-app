<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route  = useRoute()
const router = useRouter()

const token                = ref('')
const email                = ref('')
const password             = ref('')
const passwordConfirmation = ref('')
const mensagem             = ref('')
const erro                 = ref('')
const enviando             = ref(false)
const showPass             = ref(false)
const showPassConf         = ref(false)

onMounted(() => {
  token.value = route.query.token as string ?? ''
  email.value = route.query.email as string ?? ''
})

async function resetar() {
  enviando.value = true
  erro.value     = ''
  mensagem.value = ''

  try {
    const res = await fetch('http://127.0.0.1:8000/api/reset-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        token:                 token.value,
        email:                 email.value,
        password:              password.value,
        password_confirmation: passwordConfirmation.value,
      }),
    })

    const data = await res.json()

    if (res.ok) {
      mensagem.value = data.message
      setTimeout(() => router.push('/baselogin'), 2000)
    } else {
      erro.value = data.message
    }
  } catch {
    erro.value = 'Erro ao conectar com o servidor.'
  } finally {
    enviando.value = false
  }
}

function strength(p: string) {
  if (!p) return 0
  let s = 0
  if (p.length >= 8)          s++
  if (/[A-Z]/.test(p))        s++
  if (/[0-9]/.test(p))        s++
  if (/[^A-Za-z0-9]/.test(p)) s++
  return s
}
const strengthLabel = ['', 'Fraca', 'Razoável', 'Boa', 'Forte']
const strengthColor = ['', '#e53e3e', '#dd6b20', '#3182ce', '#38a169']
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">

      <h1 class="title">Redefinir senha</h1>
      <p class="subtitle">Escolha uma nova senha segura para a sua conta.</p>

      <!-- Nova senha -->
      <div class="field">
        <label class="label" for="password">Nova senha</label>
        <div class="input-wrap">
          <input
            id="password"
            class="input"
            :type="showPass ? 'text' : 'password'"
            placeholder="Mínimo 8 caracteres"
            v-model="password"
          />
          <button class="eye-btn" type="button" @click="showPass = !showPass" tabindex="-1">
            <svg v-if="!showPass" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
        </div>

        <!-- Barra de força -->
        <div v-if="password" class="strength-bar">
          <div class="strength-track">
            <div
              class="strength-fill"
              :style="{ width: (strength(password) / 4 * 100) + '%', background: strengthColor[strength(password)] }"
            ></div>
          </div>
          <span class="strength-label" :style="{ color: strengthColor[strength(password)] }">
            {{ strengthLabel[strength(password)] }}
          </span>
        </div>
      </div>

      <!-- Confirmar senha -->
      <div class="field">
        <label class="label" for="password-conf">Confirmar senha</label>
        <div class="input-wrap">
          <input
            id="password-conf"
            class="input"
            :type="showPassConf ? 'text' : 'password'"
            placeholder="Repita a nova senha"
            v-model="passwordConfirmation"
            @keyup.enter="resetar"
          />
          <button class="eye-btn" type="button" @click="showPassConf = !showPassConf" tabindex="-1">
            <svg v-if="!showPassConf" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
          </button>
        </div>
        <!-- Match indicator -->
        <p
          v-if="passwordConfirmation && password"
          class="match-hint"
          :class="password === passwordConfirmation ? 'match-ok' : 'match-fail'"
        >
          {{ password === passwordConfirmation ? '✓ Senhas coincidem' : '✗ Senhas não coincidem' }}
        </p>
      </div>

      <transition name="fade">
        <div v-if="mensagem" class="alert alert--ok">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          {{ mensagem }}
        </div>
        <div v-else-if="erro" class="alert alert--erro">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/><path d="M8 5v3M8 11h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          {{ erro }}
        </div>
      </transition>

      <button
        class="btn-primary"
        @click="resetar"
        :disabled="enviando || !password || password !== passwordConfirmation"
      >
        <span v-if="enviando" class="spinner"></span>
        {{ enviando ? 'Salvando...' : 'Redefinir senha' }}
      </button>

      <a href="/baselogin" class="back-link">← Voltar para o login</a>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

.auth-page {
  font-family: 'Poppins', sans-serif;
  min-height: 70vh;
  background-color: var(--color-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.auth-card {
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 40px 36px;
  width: 100%;
  max-width: 420px;
  display: flex;
  flex-direction: column;
}

/* Brand */
.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 28px;
}
.brand-name {
  font-size: 18px;
  font-weight: 700;
  color: var(--color-primary);
  letter-spacing: -0.3px;
}

/* Títulos */
.title {
  font-size: 22px;
  font-weight: 700;
  color: var(--color-black-text);
  margin: 0 0 8px;
  letter-spacing: -0.4px;
}
.subtitle {
  font-size: 13.5px;
  color: var(--color-black-text);
  opacity: 0.55;
  margin: 0 0 28px;
  line-height: 1.6;
}

/* Field */
.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 16px;
}
.label {
  font-size: 13px;
  font-weight: 500;
  color: var(--color-black-text);
}

/* Input wrap para ícone do olho */
.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.input {
  background: var(--input-color);
  border: 1.5px solid var(--color-border);
  border-radius: 10px;
  padding: 12px 42px 12px 14px;
  font-size: 14px;
  font-family: 'Poppins', sans-serif;
  color: var(--color-black-text);
  outline: none;
  width: 100%;
  box-sizing: border-box;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input::placeholder { opacity: 0.4; }
.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--color-overlay);
}
.eye-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--color-black-text);
  opacity: 0.4;
  padding: 0;
  display: flex;
  transition: opacity 0.2s;
}
.eye-btn:hover { opacity: 0.75; }

/* Força da senha */
.strength-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}
.strength-track {
  flex: 1;
  height: 4px;
  background: var(--color-border);
  border-radius: 99px;
  overflow: hidden;
}
.strength-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.3s, background 0.3s;
}
.strength-label {
  font-size: 11px;
  font-weight: 600;
  min-width: 48px;
  text-align: right;
}

/* Match hint */
.match-hint {
  font-size: 12px;
  font-weight: 500;
  margin: 0;
}
.match-ok   { color: #38a169; }
.match-fail { color: #e53e3e; }

/* Alerts */
.alert {
  display: flex;
  align-items: center;
  gap: 8px;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 16px;
}
.alert--ok  { background: rgba(0, 180, 100, 0.10); color: #0a7a45; }
.alert--erro{ background: rgba(220, 38, 38, 0.08); color: #c02020; }

/* Button */
.btn-primary {
  background: var(--color-primary);
  color: var(--color-primary-text);
  border: none;
  border-radius: 10px;
  padding: 13px;
  font-size: 14px;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  cursor: pointer;
  width: 100%;
  margin-top: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
}
.btn-primary:hover:not(:disabled) {
  background: var(--color-primary-hover);
  box-shadow: var(--shadow-hover-blue);
}
.btn-primary:active:not(:disabled) { transform: scale(0.98); }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

/* Spinner */
.spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Back link */
.back-link {
  display: block;
  text-align: center;
  margin-top: 20px;
  font-size: 13px;
  color: var(--color-primary);
  text-decoration: none;
  font-weight: 500;
  opacity: 0.85;
  transition: opacity 0.2s;
}
.back-link:hover { opacity: 1; }

/* Transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
