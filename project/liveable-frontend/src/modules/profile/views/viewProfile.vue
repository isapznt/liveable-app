<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import TheBanner from '../components/TheBanner.vue'
import ProfileInfos from '../components/ProfileInfos.vue'
import ProfileBio from '../components/ProfileBio.vue'
import ProfileSocialLinks from '../components/ProfileSocialinfos.vue'

// ── Theme ──────────────────────────────────────────────
const theme = ref<'light' | 'dark'>('light')
const menuOpen = ref(false)
const menuRef  = ref<HTMLElement | null>(null)

onMounted(() => {
  const saved = localStorage.getItem('theme') as 'light' | 'dark' | null
  theme.value = saved ?? (document.documentElement.getAttribute('data-theme') as 'light' | 'dark') ?? 'light'
  document.addEventListener('mousedown', closeOnOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', closeOnOutside)
})

function setTheme(t: 'light' | 'dark') {
  theme.value = t
  document.documentElement.setAttribute('data-theme', t)
  localStorage.setItem('theme', t)
  menuOpen.value = false
}

function closeOnOutside(e: MouseEvent) {
  if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
    menuOpen.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="page__header">
      <div class="title">
        <h1>Editar <span>Perfil</span></h1>
        <i class="fa-solid fa-user-pen"></i>
      </div>

      <!-- Settings + Theme menu -->
      <div class="settings-wrap" ref="menuRef">
        <svg
          class="page__settings"
          :class="{ active: menuOpen }"
          @click="menuOpen = !menuOpen"
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="12" cy="12" r="3" />
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
        </svg>

        <transition name="menu">
          <div v-if="menuOpen" class="theme-menu">
            <p class="menu-label">Aparência</p>

            <button
              class="menu-item"
              :class="{ selected: theme === 'light' }"
              @click="setTheme('light')"
            >
              <!-- Sol -->
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
              </svg>
              Claro
              <svg v-if="theme === 'light'" class="check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </button>

            <button
              class="menu-item"
              :class="{ selected: theme === 'dark' }"
              @click="setTheme('dark')"
            >
              <!-- Lua -->
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
              </svg>
              Escuro
              <svg v-if="theme === 'dark'" class="check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
          </div>
        </transition>
      </div>
    </div>

    <div class="page__body">
      <div class="page__left">
        <TheBanner />
        <ProfileBio />
      </div>
      <div class="page__right">
        <ProfileInfos />
        <ProfileSocialLinks />
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.page {
  width: 100%;
  min-height: 100vh;
  padding: 2rem 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
  background: var(--color-bg, #f4f6fb);
}

/* Header */
.page__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.title {
  display: flex;
  gap: 10px;
  align-items: center;
  color: var(--color-primary);
}

h1 {
  font-size: 1.3rem;
  font-weight: 600;
  position: relative;
  color: var(--color-black-text);
  margin: 0;
}
h1 span { color: var(--color-primary); }
h1::before {
  content: '';
  position: absolute;
  left: 0; bottom: -3px;
  height: 3px; width: 75%;
  background: var(--color-primary);
  border-radius: 15px;
}
h1::after {
  content: '';
  position: absolute;
  right: 0; bottom: -3px;
  height: 3px; width: 20%;
  background: var(--color-primary);
  border-radius: 15px;
}

/* Settings icon wrapper */
.settings-wrap {
  position: relative;
}

.page__settings {
  cursor: pointer;
  color: #6b7280;
  display: block;
  transition: color 0.2s, transform 0.35s;
}
.page__settings:hover { color: var(--color-primary); }
.page__settings.active {
  color: var(--color-primary);
  transform: rotate(45deg);
}

/* Theme dropdown */
.theme-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: var(--color-bg-secondary);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  padding: 8px;
  min-width: 160px;
  z-index: 100;
}

.menu-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-black-text);
  opacity: 0.4;
  margin: 4px 8px 6px;
}

.menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border: none;
  background: none;
  border-radius: 8px;
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: var(--color-black-text);
  cursor: pointer;
  transition: background 0.15s;
  text-align: left;
  margin-top: 0.4rem;
}
.menu-item:hover {
  background: var(--color-overlay);
  color: var(--color-primary);
}
.menu-item.selected {
  color: var(--color-primary);
  background: var(--color-overlay);
}

.check {
  margin-left: auto;
  color: var(--color-primary);
}

/* Transition */
.menu-enter-active { transition: opacity 0.15s, transform 0.15s; }
.menu-leave-active { transition: opacity 0.1s, transform 0.1s; }
.menu-enter-from  { opacity: 0; transform: translateY(-6px) scale(0.97); }
.menu-leave-to    { opacity: 0; transform: translateY(-4px) scale(0.97); }

/* Body */
.page__body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  align-items: start;
}
.page__left, .page__right {
  display: flex;
  flex-direction: column;
  gap: 5rem;
}

@media (max-width: 768px) {
  .page { padding: 1.5rem 1rem; }
  .page__body { grid-template-columns: 1fr; }
}
</style>
