<script setup lang="ts">
import { useRouter } from 'vue-router'
import { ref } from 'vue'
const router = useRouter()

const nome = ref<string>('')
const sobrenome = ref<string>('')
const email = ref<string>('')
const senha = ref<string>('')

async function enviarCadastro() {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: nome.value,
        last_name: sobrenome.value,
        email: email.value,
        password: senha.value,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Erro ao cadastrar')
    }

    console.log('Usuário cadastrado:', data)

    alert('Cadastro realizado com sucesso!')

    // limpar campos
    nome.value = ''
    sobrenome.value = ''
    email.value = ''
    senha.value = ''
  } catch (error: any) {
    console.error(error)
    alert(error.message)
  }
}

function loginComGoogle() {
  window.location.href = 'http://localhost:8000/api/auth/google/redirect'
}

function loginComFacebook() {
  window.location.href = 'http://localhost:8000/api/auth/facebook/redirect'
}

function loginComTwitter() {
  window.location.href = 'http://localhost:8000/api/auth/twitter/redirect'
}

function goToEntrar() {
  router.push('/baselogin')
}
</script>

<template>
  <div class="container">
    <h1>Criar uma conta!</h1>
    <h2>Já tem uma conta? <span @click="goToEntrar()">Entrar</span></h2>

    <div class="inputs-name">
      <input class="input-large input" type="text" placeholder="Nome" v-model="nome" />
      <input class="input-large input" type="text" placeholder="Sobrenome" v-model="sobrenome" />
    </div>

    <input class="input-large input" type="text" placeholder="Email" v-model="email" />

    <div class="input-password">
      <input class="input-large input" type="text" placeholder="Senha" v-model="senha" />
    </div>

    <button class="input-large button" @click="enviarCadastro()">Cadastrar-se</button>

    <div class="divisoria">
      <div class="risc"></div>
      <p>Ou cadastrar-se com</p>
      <div class="risc"></div>
    </div>

    <div class="social-medias">
      <button @click="loginComGoogle"><i class="fa-brands fa-google"></i></button>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

* {
  margin: 0;
  padding: 0;
}

.container {
  display: flex;
  flex-direction: column;
  min-width: 40%;
  font-family: 'Poppins', sans-serif;
  justify-content: center;
  gap: 2vw;
  color: var(--color-black-text);
}

.logo {
  display: none;
}

button,
input {
  font-family: 'Poppins', sans-serif;
}

button {
  cursor: pointer;
}

.button {
  background-color: var(--color-primary);
  color: white;
  transition: background-color 0.5s;
}

.button:hover {
  background-color: var(--color-primary-hover);
}

.container h2 {
  font-size: 1.3rem;
  opacity: 0.6;
}

.container h2 span {
  opacity: 1;
  color: var(--color-primary);
  cursor: pointer;
  text-decoration: underline;
}

.inputs-name {
  display: flex;
  flex-direction: row;
  width: 100%;
  gap: 0.5rem;
}

.container .input-large {
  width: 100%;
  padding: 0.8rem 0;
  border-radius: 0.5rem;
  border: 0;
}

.container .input {
  box-sizing: border-box;
  padding-left: 1rem;
  border: 1px solid black;
}

.input-password {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.input-password a {
  align-self: flex-end;
}

.divisoria {
  display: flex;
  align-items: center;
}

.divisoria p {
  padding: 0 1rem;
  text-wrap: nowrap;
}

.risc {
  width: 100%;
  background-color: black;
  height: 2px;
}

.social-medias {
  width: 100%;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
}

.social-medias button {
  width: 100%;
  padding: 0.3rem 0;
}

@media (max-width: 768px) {
  .container {
    gap: 7vw;
  }

  .container .input-large {
    width: 100%;
    padding: 1.3rem 0;
    border-radius: 0.5rem;
    border: 0;
  }

  .container .input {
    box-sizing: border-box;
    padding-left: 1rem;
    border: 1px solid black;
  }

  .logo {
    display: initial;
    align-self: center;
  }
}
</style>
