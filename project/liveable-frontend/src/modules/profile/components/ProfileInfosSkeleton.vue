<template>
  <div class="infos-skeleton">
    <div class="skeleton-head">
      <div class="skeleton-title"></div>
      <div class="skeleton-sub"></div>
    </div>

    <div class="skeleton-fields">
      <div v-for="n in 3" :key="n" class="skeleton-field">
        <div class="skeleton-label"></div>
        <div class="skeleton-input"></div>
      </div>
    </div>

    <div class="skeleton-btn"></div>
  </div>
</template>

<style scoped>
.infos-skeleton {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

/* Base da animação Shimmer */
.skeleton-title,
.skeleton-sub,
.skeleton-label,
.skeleton-input,
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

/* Cabeçalho */
.skeleton-head {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.skeleton-title {
  width: 180px;
  height: 1.75rem;
  border-radius: 6px;
}

.skeleton-sub {
  width: 220px;
  height: 0.9rem;
  border-radius: 4px;
}

/* Campos do Formulário */
.skeleton-fields {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.skeleton-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.skeleton-label {
  width: 110px;
  height: 0.85rem;
  border-radius: 4px;
  margin-left: 2px;
}

.skeleton-input {
  width: 100%;
  height: 47px; /* Altura exata calculada com base no padding + font-size + border */
  border-radius: 12px;
}

/* Botão Salvar (Ocupa 100% da largura) */
.skeleton-btn {
  width: 100%;
  height: 49px; /* Altura exata calculada com base no seu padding do botão */
  border-radius: 14px;
  margin-top: 4px;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>
