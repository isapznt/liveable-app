<script setup lang="ts">
import { PhGlobe, PhInstagramLogo, PhFacebookLogo, PhXLogo } from '@phosphor-icons/vue'
</script>

<template>
  <div class="container">
    <div class="cima">
      <div>
        <h3>Inspiração para viagens futuras</h3>
      </div>

      <div class="destinations">
        <div class="destination-group">
          <a href="https://pt.wikipedia.org/wiki/Ilhabela" target="_blank"> Ilhabela, SP </a>
          <a href="https://pt.wikipedia.org/wiki/Trancoso_(Porto_Seguro)" target="_blank">
            Trancoso, BA
          </a>
          <a href="https://pt.wikipedia.org/wiki/Arma%C3%A7%C3%A3o_dos_B%C3%BAzios" target="_blank">
            Búzios, RJ
          </a>
        </div>

        <div class="destination-group">
          <a href="https://pt.wikipedia.org/wiki/Ibiza" target="_blank"> Ibiza, Espanha </a>
          <a href="https://pt.wikipedia.org/wiki/Positano" target="_blank"> Positano, Itália </a>
          <a href="https://pt.wikipedia.org/wiki/Santorini" target="_blank"> Santorini, Grécia </a>
        </div>

        <div class="destination-group">
          <a href="https://pt.wikipedia.org/wiki/T%C3%B3quio" target="_blank"> Tóquio, Japão </a>
          <a href="https://pt.wikipedia.org/wiki/Lisboa" target="_blank"> Lisboa, Portugal </a>
          <a href="https://pt.wikipedia.org/wiki/Nova_Iorque" target="_blank"> Nova York, EUA </a>
        </div>
      </div>
    </div>

    <div class="baixo">
      <div class="baixo-group-1">
        <p>&copy; 2026 Liveable, inc</p>
        <a href="/Liveable_Termos_Politica_Privacidade.docx" download
          >Privacidade</a
        >
        <a href="/Liveable_Termos_Politica_Privacidade.docx" download
          >Termos</a
        >
        <a href="/Liveable_Termos_Politica_Privacidade.docx" download
          >Informações</a
        >
      </div>

      <div class="baixo-group-2">
        <div class="baixo-group-icon-text">
          <PhGlobe :size="20" />
          <p>Português (BR)</p>
        </div>

        <p>R$ BLR</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

.container {
  background-color: var(--footer-color);
  width: 100%;
  height: auto;
  color: black;
  font-family: 'Poppins', sans-serif;
  box-sizing: border-box;
  padding: 1rem 3rem;
  color: var(--color-black-text);
}

.cima {
  display: flex;
  flex-direction: column;
}

.destinations {
  display: flex;
  flex-direction: row;
  gap: clamp(2rem, 4rem, 8rem);
}

.destinations .destination-group {
  display: flex;
  flex-direction: column;
}

.baixo {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.baixo .baixo-group-1,
.baixo-group-2 {
  display: flex;
  align-items: center;
}

.baixo-group-1 {
  gap: 1rem;
}

.baixo-group-2 {
  gap: 2rem;
}

.baixo-group-icon-text {
  display: flex;
  align-items: center;
}

/* Configurações Globais */

a {
  color: currentColor;
}

@media (max-width: 768px) {
  .container {
    padding: 2rem 1.5rem;
  }

  .destinations {
    flex-wrap: wrap;
    gap: 2rem;
  }

  .baixo {
    flex-direction: column;
    align-items: flex-start;
    gap: 1.5rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
  }

  .baixo .baixo-group-1 {
    flex-wrap: wrap;
    gap: 0.8rem 1.5rem;
  }
}

@media (max-width: 480px) {
  .destinations {
    flex-direction: column;
    gap: 1.5rem;
  }

  .baixo-group-2 {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .baixo-group-2 div:last-child {
    display: flex;
    gap: 1rem;
  }
}
</style>
