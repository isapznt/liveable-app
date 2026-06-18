<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const userId = route.params.id
const user = ref<any>(null)
const loading = ref(true)

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
]

const randomBanner = banners[Math.floor(Math.random() * banners.length)]
const randomAvatar = avatars[Math.floor(Math.random() * avatars.length)]

// Detecta se o valor já é uma URL completa (http/https) ou um path do storage
function resolveImage(value: string | null | undefined, fallback: string): string {
  if (!value) return fallback
  if (value.startsWith('http://') || value.startsWith('https://')) return value
  return `http://127.0.0.1:8000/storage/${value}`
}

onMounted(async () => {
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/user/${userId}`, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
        Accept: 'application/json',
      },
    })
    user.value = await res.json()
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})

const bannerUrl = computed(() => resolveImage(user.value?.banner, randomBanner))
const avatarUrl = computed(() => resolveImage(user.value?.profile_picture, randomAvatar))
</script>

<template>
  <div class="page">
    <!-- SKELETON -->
    <template v-if="loading">
      <div class="skeleton-banner">
        <div class="skeleton-avatar"></div>
      </div>
      <div class="header">
        <div class="header__name">
          <div class="skeleton-line" style="width: 180px; height: 28px; border-radius: 8px"></div>
          <div class="skeleton-line" style="width: 60px; height: 22px; border-radius: 20px"></div>
        </div>
      </div>
      <div class="body">
        <div class="card">
          <div class="skeleton-line" style="width: 60px; height: 18px"></div>
          <div class="skeleton-line" style="width: 100%; height: 14px"></div>
          <div class="skeleton-line" style="width: 80%; height: 14px"></div>
        </div>
        <div class="card">
          <div class="skeleton-line" style="width: 100px; height: 18px"></div>
          <div class="skeleton-line" style="width: 200px; height: 14px"></div>
          <div class="skeleton-line" style="width: 160px; height: 14px"></div>
        </div>
      </div>
    </template>

    <!-- CONTEÚDO REAL -->
    <template v-else>
      <div class="banner" :style="{ backgroundImage: `url('${bannerUrl}')` }">
        <div class="avatar" :style="{ backgroundImage: `url('${avatarUrl}')` }"></div>
      </div>

      <div class="header">
        <div class="header__name">
          <h1>{{ user?.name }} {{ user?.last_name }}</h1>
          <span v-if="user?.role" class="badge">{{ user.role }}</span>
        </div>
      </div>

      <div class="body">
        <div v-if="user?.bio" class="card">
          <h2 class="card__title">Bio</h2>
          <p class="card__text">{{ user.bio }}</p>
        </div>

        <div class="card">
          <h2 class="card__title">Informações</h2>
          <div class="info-list">
            <div class="info-item" v-if="user?.email">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,12 2,6" />
              </svg>
              <span>{{ user.email }}</span>
            </div>
            <div class="info-item" v-if="user?.phone">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.55a16 16 0 0 0 6.29 6.29l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
              </svg>
              <span>{{ user.phone }}</span>
            </div>
          </div>
        </div>

        <div v-if="user?.share_socials && (user?.twitter || user?.instagram || user?.facebook)" class="card">
          <h2 class="card__title">Redes Sociais</h2>
          <div class="socials">
            <a v-if="user.twitter" :href="`https://x.com/${user.twitter}`" target="_blank" class="social-link">
              <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.261 5.632zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
              </svg>
              x.com/{{ user.twitter }}
            </a>
            <a v-if="user.instagram" :href="`https://instagram.com/${user.instagram}`" target="_blank" class="social-link">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                <rect x="2" y="2" width="20" height="20" rx="5" />
                <circle cx="12" cy="12" r="5" />
                <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
              </svg>
              instagram.com/{{ user.instagram }}
            </a>
            <a v-if="user.facebook" :href="`https://facebook.com/${user.facebook}`" target="_blank" class="social-link">
              <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
              </svg>
              facebook.com/{{ user.facebook }}
            </a>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

.page {
  width: 100%;
  min-height: 100vh;
  font-family: 'Poppins', sans-serif;
  background: var(--color-bg, #f4f6fb);
}

/* ── Banner / Avatar real ── */
.banner {
  width: 100%;
  height: 220px;
  background-size: cover;
  background-position: center;
  position: relative;
  border-radius: 40px;
}

.avatar {
  width: 110px;
  height: 110px;
  border-radius: 20px;
  background-size: cover;
  background-position: center;
  position: absolute;
  bottom: -55px;
  left: 3rem;
  border: 4px solid var(--color-bg, #f4f6fb);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

/* ── Skeleton ── */
@keyframes shimmer {
  0%   { background-position: -600px 0; }
  100% { background-position:  600px 0; }
}

.skeleton-banner {
  width: 100%;
  height: 220px;
  border-radius: 40px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 600px 100%;
  animation: shimmer 1.4s infinite linear;
  position: relative;
}

.skeleton-avatar {
  width: 110px;
  height: 110px;
  border-radius: 20px;
  position: absolute;
  bottom: -55px;
  left: 3rem;
  border: 4px solid var(--color-bg, #f4f6fb);
  background: linear-gradient(90deg, #cbd5e1 25%, #e2e8f0 50%, #cbd5e1 75%);
  background-size: 600px 100%;
  animation: shimmer 1.4s infinite linear;
}

.skeleton-line {
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 600px 100%;
  animation: shimmer 1.4s infinite linear;
  border-radius: 6px;
}

/* ── Header ── */
.header {
  padding: 4rem 3rem 1rem;
}

.header__name {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header__name h1 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
}

.badge {
  background: var(--color-primary, #1a2fa8);
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 3px 12px;
  border-radius: 20px;
  text-transform: capitalize;
}

/* ── Body / Cards ── */
.body {
  padding: 1rem 3rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
  max-width: 700px;
}

.card {
  background: var(--color-bg-secondary, #fff);
  border-radius: 18px;
  padding: 1.4rem 1.6rem;
  box-shadow: 0 1px 8px rgba(0, 0, 0, 0.07);
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.card__title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-black-text, #1a1a2e);
}

.card__text {
  margin: 0;
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.6;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  color: #6b7280;
}

.info-item svg {
  flex-shrink: 0;
  color: var(--color-primary, #1a2fa8);
}

.socials {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.social-link {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  color: var(--color-primary, #1a2fa8);
  text-decoration: none;
  font-weight: 500;
  transition: opacity 0.2s;
}

.social-link:hover {
  opacity: 0.7;
}

@media (max-width: 768px) {
  .header,
  .body {
    padding-left: 1.2rem;
    padding-right: 1.2rem;
  }
  .avatar,
  .skeleton-avatar {
    left: 1.2rem;
  }
}
</style>
