<template>
  <div class="bio-skeleton">
    <div class="skeleton-title"></div>

    <div class="skeleton-textarea"></div>

    <div class="skeleton-btn"></div>
  </div>
</template>

<style scoped>
.bio-skeleton {
  display: flex;
  flex-direction: column;
  gap: 12px;
  width: 100%;
}

/* Base da animação Shimmer */
.skeleton-title,
.skeleton-textarea,
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

/* Imita o tamanho do h2 (.profile-bio__title) */
.skeleton-title {
  width: 60px;
  height: 1.75rem;
  border-radius: 6px;
}

/* Imita o tamanho aproximado do textarea com 6 linhas e paddings */
.skeleton-textarea {
  width: 100%;
  height: 154px; /* Altura calculada com base no seu rows="6" + paddings */
  border-radius: 14px;
}

/* Imita o botão desalinhado à esquerda (.profile-bio__btn) */
.skeleton-btn {
  align-self: flex-start;
  width: 120px;
  height: 42px; /* Equivalente ao padding: 0.7rem + line-height */
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
