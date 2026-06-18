<template>
  <div class="social-skeleton">
    <div class="skeleton-head">
      <div class="skeleton-title"></div>
      <div class="skeleton-toggle">
        <div class="skeleton-checkbox"></div>
        <div class="skeleton-toggle-text"></div>
      </div>
    </div>

    <div class="skeleton-fields">
      <div v-for="n in 3" :key="n" class="skeleton-row">
        <div class="skeleton-icon"></div>
        <div class="skeleton-input-wrap">
          <div class="skeleton-prefix"></div>
          <div class="skeleton-input"></div>
        </div>
      </div>
    </div>

    <div class="skeleton-btn"></div>
  </div>
</template>

<style scoped>
.social-skeleton {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

/* Base da animação Shimmer */
.skeleton-title,
.skeleton-checkbox,
.skeleton-toggle-text,
.skeleton-icon,
.skeleton-prefix,
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
  gap: 8px;
}

.skeleton-title {
  width: 130px;
  height: 1.75rem;
  border-radius: 6px;
}

.skeleton-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
}

.skeleton-checkbox {
  width: 16px;
  height: 16px;
  border-radius: 4px;
}

.skeleton-toggle-text {
  width: 240px;
  height: 0.82rem;
  border-radius: 4px;
}

/* Linhas de Redes Sociais */
.skeleton-fields {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.skeleton-row {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.65rem 1rem;
  height: 44px; /* Casando perfeitamente com a altura do input real */
  box-sizing: border-box;
}

.skeleton-icon {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  flex-shrink: 0;
}

.skeleton-input-wrap {
  display: flex;
  align-items: center;
  flex: 1;
  gap: 6px;
}

.skeleton-prefix {
  width: 100px;
  height: 0.88rem;
  border-radius: 4px;
}

.skeleton-input {
  width: 40%;
  height: 0.88rem;
  border-radius: 4px;
}

/* Botão Salvar Redes */
.skeleton-btn {
  align-self: flex-start;
  width: 140px;
  height: 42px; /* Correspondente ao padding e tamanho real do botão */
  border-radius: 12px;
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
