<script setup lang="ts">
defineProps<{
  open: boolean
  titulo: string
  deletando?: boolean
}>()

const emit = defineEmits<{
  (e: 'cancelar'): void
  (e: 'confirmar'): void
}>()
</script>

<template>
  <Transition name="overlay">
    <div v-if="open" class="overlay" @click.self="emit('cancelar')">
      <Transition name="modal">
        <div v-if="open" class="modal">
          <div class="icon-wrap">
            <PhTrash :size="26" weight="regular" />
          </div>
          <h3>Deletar imóvel</h3>
          <p>
            Tem certeza que deseja deletar
            <strong>"{{ titulo }}"</strong>? Esta ação não pode ser desfeita.
          </p>
          <div class="btns">
            <button class="btn-cancel" @click="emit('cancelar')" :disabled="deletando">
              Cancelar
            </button>
            <button class="btn-confirm" @click="emit('confirmar')" :disabled="deletando">
              {{ deletando ? 'Deletando...' : 'Sim, deletar' }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script lang="ts">
import { PhTrash } from '@phosphor-icons/vue'
</script>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal {
  background: var(--color-bg-secondary);
  border-radius: 20px;
  padding: 2rem;
  width: 100%;
  max-width: 380px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  font-family: 'Poppins', sans-serif;
}

.icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #fcebeb;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a32d2d;
  flex-shrink: 0;
}

h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--color-black-text);
  text-align: center;
}

p {
  margin: 0;
  font-size: 0.88rem;
  color: var(--color-black-text);
  opacity: 0.65;
  text-align: center;
  line-height: 1.6;
}

p strong {
  opacity: 1;
  color: var(--color-black-text);
  font-weight: 600;
}

.btns {
  display: flex;
  gap: 10px;
  width: 100%;
  margin-top: 0.5rem;
}

.btn-cancel,
.btn-confirm {
  flex: 1;
  padding: 0.7rem;
  border-radius: 12px;
  font-family: 'Poppins', sans-serif;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition:
    background 0.18s,
    opacity 0.18s;
}

.btn-cancel {
  background: transparent;
  border: 1.5px solid #e5e7eb;
  color: var(--color-black-text);
}

.btn-cancel:hover:not(:disabled) {
  background: var(--color-bg);
}

.btn-confirm {
  background: #e24b4a;
  border: none;
  color: #fff;
}

.btn-confirm:hover:not(:disabled) {
  background: #a32d2d;
}

.btn-cancel:disabled,
.btn-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Animações */
.overlay-enter-active,
.overlay-leave-active {
  transition: opacity 0.25s ease;
}
.overlay-enter-from,
.overlay-leave-to {
  opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(8px);
}
</style>
