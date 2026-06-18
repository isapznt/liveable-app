<template>
  <div class="card-skeleton">
    <div class="shimmer-bg">
      <div class="informs-skeleton">
        <div class="texts-skeleton">
          <div class="titulo-placeholder"></div>
          <div class="linha-placeholder"></div>
          <div class="linha-placeholder curta"></div>
        </div>

        <div class="botao-placeholder"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-skeleton {
  width: 100%;
  min-height: 350px;
  border-radius: 30px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  background-color: #ececec;
}

.shimmer-bg,
.titulo-placeholder,
.linha-placeholder,
.botao-placeholder {
  background: linear-gradient(90deg, #ececec 25%, #f5f5f5 50%, #ececec 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite linear;
}

.shimmer-bg {
  width: 100%;
  height: 350px;
  display: flex;
  align-items: flex-end;
  padding: 0;
}

.informs-skeleton {
  width: 100%;
  min-height: 120px;
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 25px;
  box-sizing: border-box;
}

.texts-skeleton {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 70%;
}

.titulo-placeholder {
  height: 20px;
  width: 90%;
  border-radius: 6px;
}

.linha-placeholder {
  height: 14px;
  width: 75%;
  border-radius: 4px;
}

.linha-placeholder.curta {
  width: 50%;
}

.botao-placeholder {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  flex-shrink: 0;
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
