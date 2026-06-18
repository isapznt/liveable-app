import { ref, onMounted } from 'vue'

const favorites = ref<number[]>([])
const carregado = ref(false)

export function useFavorites() {
  async function carregar() {
    const token = localStorage.getItem('token')
    if (!token || carregado.value) return
    try {
      const res = await fetch('http://127.0.0.1:8000/api/favorites', {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      })
      if (!res.ok) return
      const data = await res.json()
      favorites.value = data.map((p: { id: number }) => p.id)
      carregado.value = true
    } catch (e) {
      console.error('[useFavorites]', e)
    }
  }

  async function toggleFavorite(propertyId: number) {
    const token = localStorage.getItem('token')
    if (!token) return false

    try {
      const res = await fetch(`http://127.0.0.1:8000/api/property/${propertyId}/like`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      })
      if (!res.ok) return false

      const isFav = favorites.value.includes(propertyId)
      if (isFav) {
        favorites.value = favorites.value.filter((id) => id !== propertyId)
      } else {
        favorites.value.push(propertyId)
      }
      return true
    } catch (e) {
      console.error('[toggleFavorite]', e)
      return false
    }
  }

  function isFavorite(propertyId: number) {
    return favorites.value.includes(propertyId)
  }

  onMounted(carregar)

  return { favorites, carregar, toggleFavorite, isFavorite }
}
