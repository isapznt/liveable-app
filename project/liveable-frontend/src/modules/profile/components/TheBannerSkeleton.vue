<template>
  <div class="appearance-skeleton">
    <div class="skeleton-banner"></div>

    <div class="skeleton-avatar"></div>

    <div class="skeleton-padding">
      <div class="skeleton-texts">
        <div class="skeleton-title"></div>
        <div class="skeleton-sub"></div>
      </div>

      <div class="skeleton-buttons">
        <div class="skeleton-btn"></div>
        <div class="skeleton-btn"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.appearance-skeleton {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: relative;
  border-radius: 25px;
  overflow: hidden;
  box-shadow: var(--shadow-sm, 0 1px 3px rgba(0,0,0,0.1));
  background-color: var(--color-bg-secondary, #fff);
}

/* Base da animação Shimmer */
.skeleton-banner,
.skeleton-avatar,
.skeleton-title,
.skeleton-sub,
.skeleton-btn {
  background: linear-gradient(
    90deg,
    #ececec 25%,
    #f5f5f5 50%,
    #ececec 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite linear;
}

/* Banner correspondente à altura original */
.skeleton-banner {
  width: 100%;
  height: 150px;
  border-radius: 25px;
}

/* Foto de Perfil respeitando o mesmo clamp, posição absoluta e transform do original */
.skeleton-avatar {
  width: clamp(7rem, 18%, 20rem);
  aspect-ratio: 1/1;
  position: absolute;
  top: 150px;
  left: 28px;
  transform: translateY(-50%);
  border-radius: 20px;
  border: 4px solid var(--color-bg-secondary, #fff); /* Evita que o shimmer do fundo se misture */
  box-sizing: border-box;
}

/* Conteúdo de Texto e Ações */
.skeleton-padding {
  display: flex;
  flex-direction: column;
  width: 100%;
  gap: 1.5rem;
  box-sizing: border-box;
  padding: 0 1.7rem;
}

.skeleton-texts {
  width: 100%;
  margin-top: 4.8rem; /* Margem idêntica para compensar a foto absoluta */
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-title {
  width: 65%;
  height: 1.6rem;
  border-radius: 6px;
}

.skeleton-sub {
  width: 45%;
  height: 0.9rem;
  border-radius: 4px;
}

/* Botões de Confirmação */
.skeleton-buttons {
  width: 100%;
  display: flex;
  gap: 10px;
  margin-bottom: 1.6rem;
}

.skeleton-btn {
  width: clamp(120px, 20%, 200px);
  height: 47px; /* Altura exata baseada nos paddings originais */
  border-radius: 10px;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

@media (max-width: 768px) {
  .appearance-skeleton {
    width: 95%;
    margin: 0 auto;
  }
}
</style>
