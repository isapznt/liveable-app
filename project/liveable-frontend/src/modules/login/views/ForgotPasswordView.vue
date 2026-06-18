<script setup lang="ts">
import { ref } from 'vue'

const email    = ref('')
const mensagem = ref('')
const erro     = ref('')
const enviando = ref(false)

async function enviar() {
  enviando.value = true
  erro.value     = ''
  mensagem.value = ''

  try {
    const res = await fetch('http://127.0.0.1:8000/api/forgot-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: email.value }),
    })

    const data = await res.json()
    if (res.ok) mensagem.value = data.message
    else        erro.value     = data.message
  } catch {
    erro.value = 'Erro ao conectar com o servidor.'
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">

      <h1 class="title">Esqueci minha senha</h1>
      <p class="subtitle">Digite seu e-mail e enviaremos um link para você redefinir sua senha.</p>

      <div class="field">
        <label class="label" for="email">E-mail</label>
        <input
          id="email"
          class="input"
          type="email"
          placeholder="seu@email.com"
          v-model="email"
          @keyup.enter="enviar"
        />
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

      <button class="btn-primary" @click="enviar" :disabled="enviando || !email">
        <span v-if="enviando" class="spinner"></span>
        {{ enviando ? 'Enviando...' : 'Enviar link' }}
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
  gap: 0;
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
.input {
  background: var(--input-color);
  border: 1.5px solid var(--color-border);
  border-radius: 10px;
  padding: 12px 14px;
  font-size: 14px;
  font-family: 'Poppins', sans-serif;
  color: var(--color-black-text);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input::placeholder { opacity: 0.4; }
.input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--color-overlay);
}

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
