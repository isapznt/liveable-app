<script setup lang="ts">
import TheNav from '../components/TheNav.vue'
import TheFooter from '../components/TheFooter.vue'
import AdminPanel from '@/modules/admin/components/Adminpanel.vue'
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'

const isAdmin = ref(false)
const route = useRoute()

const mostrarPanel = computed(() => isAdmin.value && route.name === 'home')

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) return
  try {
    const res = await fetch('http://127.0.0.1:8000/api/user', {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    })
    const data = await res.json()
    isAdmin.value = data.role === 'admin'
  } catch {}
})
</script>

<template>
  <div class="container-all">
    <header>
      <nav><TheNav class="nav" /></nav>
    </header>

    <main>
      <template v-if="mostrarPanel">
        <AdminPanel class="admin-panel" />
        <div class="admin-divisor">
          <span>View pública</span>
        </div>
      </template>

      <router-view />
    </main>

    <footer>
      <TheFooter />
    </footer>
  </div>
</template>

<style scoped>
.container-all {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  gap: 2vw;
}

header {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 2vw;
}

main {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 2rem;
  flex: 1;
}

.admin-panel {
  width: 100%;
}

.admin-divisor {
  width: 100%;
  max-width: 1200px;
  display: flex;
  align-items: center;
  gap: 16px;
  margin: 2rem 0;
}

.admin-divisor::before,
.admin-divisor::after {
  content: '';
  flex: 1;
  height: 1.5px;
  background: linear-gradient(90deg, transparent, #1a2fa820, #1a2fa840, #1a2fa820, transparent);
  border-radius: 2px;
}

.admin-divisor span {
  font-family: 'Poppins', sans-serif;
  font-size: 0.75rem;
  font-weight: 600;
  color: #1a2fa8;
  opacity: 0.5;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  white-space: nowrap;
}
</style>
