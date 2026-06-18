const TOKEN_KEY = 'token'

// Salva o token
export function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token)
}

// Pega o token
export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

// Remove o token (logout)
export function removeToken() {
  localStorage.removeItem(TOKEN_KEY)
}

// Verifica se está logado
export function isAuthenticated() {
  return !!getToken()
}

export async function getUser() {
  const token = getToken()

  if (!token) {
    throw new Error('Token não encontrado')
  }

  const response = await fetch('http://127.0.0.1:8000/api/user', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
  })

  if (!response.ok) {
    throw new Error('Erro ao buscar usuário')
  }

  return await response.json()
}
