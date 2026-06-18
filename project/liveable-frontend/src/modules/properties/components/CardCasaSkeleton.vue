<template>
  <div class="card">
    <div class="cima"></div>

    <div class="baixo">
      <div class="titulo"></div>

      <div class="linha"></div>

      <div class="botao"></div>
    </div>
  </div>
</template>

<style scoped>
.card {
  width: 300px;
  border-radius: 24px;
  overflow: hidden;
}

.cima,
.titulo,
.linha,
.botao {
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);

  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

.cima {
  height: 300px;
  border-radius: 20px;
}

.baixo {
  padding: 15px;
}

.titulo {
  height: 20px;
  width: 80%;
  border-radius: 10px;
  margin-bottom: 12px;
}

.linha {
  height: 15px;
  width: 50%;
  border-radius: 10px;
  margin-bottom: 20px;
}

.botao {
  width: 100%;
  height: 42px;
  border-radius: 14px;
}

@keyframes shimmer {
  from {
    background-position: 200% 0;
  }

  to {
    background-position: -200% 0;
  }
}
</style>
