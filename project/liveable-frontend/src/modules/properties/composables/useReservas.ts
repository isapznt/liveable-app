import { ref } from 'vue'

export interface PeriodoBloqueado {
  checkin: string
  checkout: string
}

export function useReservas() {
  const periodosBloqueados = ref<PeriodoBloqueado[]>([])
  const carregando = ref(false)
  const erro = ref<string | null>(null)

  async function buscarReservas(propertyId: number | string) {
    carregando.value = true
    erro.value = null

    try {
      const response = await fetch(`http://127.0.0.1:8000/api/properties/${propertyId}/rent`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      })

      if (!response.ok) throw new Error(`Erro ${response.status}`)

      const data = await response.json()

      periodosBloqueados.value = data.map((reserva: any) => ({
        checkin: reserva.checkin.split('T')[0],
        checkout: reserva.checkout.split('T')[0],
      }))
    } catch (e: any) {
      erro.value = e.message ?? 'Erro desconhecido'
      console.error('[useReservas]', e)
    } finally {
      carregando.value = false
    }
  }

  return { periodosBloqueados, carregando, erro, buscarReservas }
}
