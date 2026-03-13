<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="modelValue"
        class="modal-overlay"
        @click.self="close"
      >
        <div class="modal-container" role="dialog" aria-modal="true">
          <!-- Header opcional -->
          <div class="modal-header" v-if="title || closable">
            <h3 v-if="title" class="modal-title">{{ title }}</h3>

            <button
              v-if="closable"
              class="modal-close"
              type="button"
              @click="close"
              aria-label="Cerrar modal"
            >
              ✕
            </button>
          </div>

          <!-- Contenido dinámico -->
          <div class="modal-body">
            <slot />
          </div>

          <!-- Footer opcional -->
          <div class="modal-footer" v-if="$slots.footer">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
export default {
  name: 'BaseModal',
  props: {
    modelValue: { type: Boolean, default: false },
    title: { type: String, default: '' },
    closable: { type: Boolean, default: true },
    closeOnEsc: { type: Boolean, default: true },
  },
  emits: ['update:modelValue', 'close'],
  mounted() {
    window.addEventListener('keydown', this.onKeydown)
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.onKeydown)
  },
  methods: {
    close() {
      this.$emit('update:modelValue', false)
      this.$emit('close')
    },
    onKeydown(e) {
      if (!this.closeOnEsc) return
      if (e.key === 'Escape' && this.modelValue) {
        this.close()
      }
    },
  },
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  z-index: 10;
}

.modal-container {
  width: 100%;
  max-width: 560px;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 18px;
  border-bottom: 1px solid #eee;
}

.modal-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: #333;
  margin-top: 10px
}

.modal-close {
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  padding: 6px;
}

.modal-body {
  padding: 18px;
  max-height: 700px;
  overflow: auto;
  max-height: 80vh;
}

.modal-footer {
  padding: 16px 18px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Animación */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
