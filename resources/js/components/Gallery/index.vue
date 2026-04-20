<template>
  <div>
    <!-- ─── Header ───────────────────────────────────────────────────── -->
    <div class="table__header">
      <div class="table__header--header">
        <div class="table__header--header-content">
          <h3 class="table__header--header-title">Galería de Medios</h3>
          <p class="table__header--header-description">
            Administra fotos y videos que se muestran en la galería del sitio web.
          </p>
        </div>
        <div class="table__header--header-button">
          <button type="button" class="button__primary" @click="openCreate">
            <span class="material-symbols-outlined">add</span>
            Agregar elemento
          </button>
        </div>
      </div>
    </div>

    <!-- ─── Loading ──────────────────────────────────────────────────── -->
    <div v-if="loading" class="gallery-loading">
      <span class="material-symbols-outlined spin">progress_activity</span>
      Cargando galería...
    </div>

    <!-- ─── Empty state ──────────────────────────────────────────────── -->
    <div v-else-if="items.length === 0" class="gallery-empty">
      <span class="material-symbols-outlined">photo_library</span>
      <p>No hay elementos en la galería.</p>
      <button class="button__primary" @click="openCreate">Agregar el primero</button>
    </div>

    <!-- ─── Grid ─────────────────────────────────────────────────────── -->
    <template v-else>
      <div class="gallery-admin-grid">
        <div v-for="(item, idx) in items" :key="item.id" class="gallery-admin-item">
          <!-- Thumbnail -->
          <div class="gallery-admin-item__thumb">
            <img
              :src="item.thumb_url || '/images/placeholder-doctor.png'"
              :alt="item.title || 'Elemento'"
            />
            <div class="gallery-admin-item__badge" :class="'badge--' + item.type">
              <span class="material-symbols-outlined">
                {{ item.type === 'video' ? 'play_circle' : 'image' }}
              </span>
              {{ item.type === 'video' ? 'Video' : 'Foto' }}
            </div>
            <div v-if="!item.is_active" class="gallery-admin-item__inactive">Inactivo</div>
          </div>

          <!-- Info -->
          <div class="gallery-admin-item__info">
            <span class="gallery-admin-item__title">{{ item.title || '—' }}</span>
            <span class="gallery-admin-item__pos">#{{ idx + 1 }}</span>
          </div>

          <!-- Actions -->
          <div class="gallery-admin-item__actions">
            <button class="gal-btn" title="Subir" :disabled="idx === 0" @click="moveUp(idx)">
              <span class="material-symbols-outlined">arrow_upward</span>
            </button>
            <button class="gal-btn" title="Bajar" :disabled="idx === items.length - 1" @click="moveDown(idx)">
              <span class="material-symbols-outlined">arrow_downward</span>
            </button>
            <button class="gal-btn gal-btn--blue" title="Editar" @click="openEdit(item)">
              <span class="material-symbols-outlined">edit</span>
            </button>
            <button
              class="gal-btn"
              :title="item.is_active ? 'Desactivar' : 'Activar'"
              @click="toggleActive(item)"
            >
              <span class="material-symbols-outlined">
                {{ item.is_active ? 'visibility' : 'visibility_off' }}
              </span>
            </button>
            <button class="gal-btn gal-btn--red" title="Eliminar" @click="confirmDelete(item)">
              <span class="material-symbols-outlined">delete</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Save order bar -->
      <div v-if="orderChanged" class="gallery-order-bar">
        <span class="material-symbols-outlined">swap_vert</span>
        El orden ha cambiado.
        <button class="button__primary button--small" :disabled="savingOrder" @click="saveOrder">
          {{ savingOrder ? 'Guardando...' : 'Guardar orden' }}
        </button>
        <button class="button__secondary button--small" @click="resetOrder">Descartar</button>
      </div>
    </template>

    <!-- ─── Create / Edit Modal ──────────────────────────────────────── -->
    <BaseModal v-model="openModal" :title="editItem ? 'Editar elemento' : 'Agregar elemento'" @close="resetForm">
      <!-- Type selector (only on create) -->
      <div class="field">
        <label class="field__label">Tipo *</label>
        <div class="type-selector">
          <label class="type-option" :class="{ selected: form.type === 'photo' }">
            <input type="radio" v-model="form.type" value="photo" :disabled="!!editItem" />
            <span class="material-symbols-outlined">image</span>
            Foto
          </label>
          <label class="type-option" :class="{ selected: form.type === 'video' }">
            <input type="radio" v-model="form.type" value="video" :disabled="!!editItem" />
            <span class="material-symbols-outlined">play_circle</span>
            Video (YouTube)
          </label>
        </div>
      </div>

      <!-- Photo upload -->
      <div v-if="form.type === 'photo'" class="field">
        <label class="field__label">
          {{ editItem ? 'Reemplazar imagen (opcional)' : 'Imagen *' }}
        </label>
        <div class="file-drop" @dragover.prevent @drop.prevent="onDrop" @click="$refs.fileInput.click()">
          <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="onFileChange" />
          <span class="material-symbols-outlined">upload</span>
          <span>Arrastra una imagen aquí o <u>haz clic para seleccionar</u></span>
          <small>Máx. 5 MB · JPG, PNG, WebP</small>
        </div>
        <small v-if="formErrors.file" class="field__error">{{ formErrors.file }}</small>
        <div v-if="filePreview || (editItem && editItem.thumb_url)" class="file-preview">
          <img :src="filePreview || editItem.thumb_url" alt="Preview" />
          <button v-if="filePreview" type="button" class="file-preview__remove" @click="removeFile">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
      </div>

      <!-- Video URL -->
      <div v-if="form.type === 'video'" class="field">
        <label class="field__label">URL de YouTube *</label>
        <input
          v-model="form.video_url"
          type="url"
          class="field__input"
          placeholder="https://www.youtube.com/watch?v=..."
          @input="updateYoutubeThumbnail"
        />
        <small v-if="formErrors.video_url" class="field__error">{{ formErrors.video_url }}</small>
        <div v-if="youtubeThumbnail" class="file-preview">
          <img :src="youtubeThumbnail" alt="Vista previa del video" />
        </div>
      </div>

      <!-- Title -->
      <div class="field">
        <label class="field__label">Título (opcional)</label>
        <input v-model="form.title" type="text" class="field__input" placeholder="Ej: Instalaciones de la clínica" />
      </div>

      <!-- Description -->
      <div class="field">
        <label class="field__label">Descripción (opcional)</label>
        <textarea v-model="form.description" class="field__input" rows="2" placeholder="Breve descripción..."></textarea>
      </div>

      <!-- Active -->
      <div class="field">
        <label class="toggle-label">
          <input type="checkbox" v-model="form.is_active" />
          <span>Visible en el sitio web</span>
        </label>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openModal = false">Cancelar</button>
        <button class="button__primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando...' : (editItem ? 'Guardar cambios' : 'Agregar') }}
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import BaseModal from '../Common/BaseModal.vue'

export default {
  name: 'GalleryIndex',
  components: { BaseModal },
  data() {
    return {
      items:           [],
      originalOrder:   [],
      loading:         false,
      orderChanged:    false,
      savingOrder:     false,

      openModal:       false,
      editItem:        null,
      saving:          false,

      form: {
        type:        'photo',
        title:       '',
        description: '',
        video_url:   '',
        is_active:   true,
      },
      formErrors:      {},
      selectedFile:    null,
      filePreview:     null,
      youtubeThumbnail: null,
    }
  },
  mounted() {
    this.fetchItems()
  },
  methods: {
    // ── Data ─────────────────────────────────────────────────────────
    async fetchItems() {
      this.loading = true
      try {
        const { data } = await axios.get('/api/gallery')
        this.items = data
        this.originalOrder = data.map(i => i.id)
      } catch {
        this.showError('No se pudo cargar la galería.')
      } finally {
        this.loading = false
      }
    },

    // ── Order ─────────────────────────────────────────────────────────
    moveUp(idx) {
      if (idx === 0) return
      const arr = [...this.items]
      ;[arr[idx - 1], arr[idx]] = [arr[idx], arr[idx - 1]]
      this.items = arr
      this.checkOrderChanged()
    },
    moveDown(idx) {
      if (idx === this.items.length - 1) return
      const arr = [...this.items]
      ;[arr[idx], arr[idx + 1]] = [arr[idx + 1], arr[idx]]
      this.items = arr
      this.checkOrderChanged()
    },
    checkOrderChanged() {
      this.orderChanged = this.items.some((item, i) => item.id !== this.originalOrder[i])
    },
    resetOrder() {
      this.fetchItems()
      this.orderChanged = false
    },
    async saveOrder() {
      this.savingOrder = true
      try {
        const payload = this.items.map((item, i) => ({ id: item.id, sort_order: i + 1 }))
        await axios.post('/api/gallery-reorder', { items: payload })
        this.originalOrder = this.items.map(i => i.id)
        this.orderChanged = false
        Swal.fire({ icon: 'success', title: 'Orden guardado', timer: 1200, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
      } catch {
        this.showError('Error al guardar el orden.')
      } finally {
        this.savingOrder = false
      }
    },

    // ── Toggle active ─────────────────────────────────────────────────
    async toggleActive(item) {
      try {
        const fd = new FormData()
        fd.append('is_active', item.is_active ? '0' : '1')
        const { data } = await axios.post(`/api/gallery/${item.id}`, fd)
        const idx = this.items.findIndex(i => i.id === item.id)
        if (idx !== -1) this.items.splice(idx, 1, data)
      } catch {
        this.showError('Error al cambiar la visibilidad.')
      }
    },

    // ── Delete ────────────────────────────────────────────────────────
    async confirmDelete(item) {
      const result = await Swal.fire({
        title: '¿Eliminar este elemento?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      })
      if (!result.isConfirmed) return
      try {
        await axios.delete(`/api/gallery/${item.id}`)
        this.items = this.items.filter(i => i.id !== item.id)
        this.originalOrder = this.originalOrder.filter(id => id !== item.id)
        this.checkOrderChanged()
      } catch {
        this.showError('Error al eliminar el elemento.')
      }
    },

    // ── Create / Edit ─────────────────────────────────────────────────
    openCreate() {
      this.editItem  = null
      this.form      = { type: 'photo', title: '', description: '', video_url: '', is_active: true }
      this.formErrors      = {}
      this.selectedFile    = null
      this.filePreview     = null
      this.youtubeThumbnail = null
      this.openModal = true
    },
    openEdit(item) {
      this.editItem  = item
      this.form      = {
        type:        item.type,
        title:       item.title || '',
        description: item.description || '',
        video_url:   item.video_url || '',
        is_active:   item.is_active,
      }
      this.formErrors      = {}
      this.selectedFile    = null
      this.filePreview     = null
      this.youtubeThumbnail = item.type === 'video' ? this.extractYoutubeThumbnail(item.video_url) : null
      this.openModal = true
    },
    resetForm() {
      this.editItem        = null
      this.selectedFile    = null
      this.filePreview     = null
      this.youtubeThumbnail = null
      this.formErrors      = {}
    },

    async save() {
      this.formErrors = {}
      if (!this.editItem && this.form.type === 'photo' && !this.selectedFile) {
        this.formErrors.file = 'Selecciona una imagen.'
        return
      }
      if (this.form.type === 'video' && !this.form.video_url) {
        this.formErrors.video_url = 'Ingresa la URL del video de YouTube.'
        return
      }

      const fd = new FormData()
      fd.append('title',       this.form.title)
      fd.append('description', this.form.description)
      fd.append('type',        this.form.type)
      fd.append('is_active',   this.form.is_active ? '1' : '0')
      if (this.form.video_url) fd.append('video_url', this.form.video_url)
      if (this.selectedFile)   fd.append('file', this.selectedFile)

      this.saving = true
      try {
        if (this.editItem) {
          const { data } = await axios.post(`/api/gallery/${this.editItem.id}`, fd)
          const idx = this.items.findIndex(i => i.id === this.editItem.id)
          if (idx !== -1) this.items.splice(idx, 1, data)
        } else {
          const { data } = await axios.post('/api/gallery', fd)
          this.items.push(data)
          this.originalOrder.push(data.id)
        }
        this.openModal = false
        Swal.fire({ icon: 'success', title: this.editItem ? 'Cambios guardados' : 'Elemento agregado', timer: 1400, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
      } catch (e) {
        if (e.response?.status === 422) {
          const errs = e.response.data.errors || {}
          this.formErrors = Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, v[0]]))
        } else {
          this.showError(e.response?.data?.message || 'Error al guardar.')
        }
      } finally {
        this.saving = false
      }
    },

    // ── File handling ─────────────────────────────────────────────────
    onFileChange(e) {
      const file = e.target.files[0]
      if (!file) return
      this.selectedFile = file
      this.filePreview  = URL.createObjectURL(file)
    },
    onDrop(e) {
      const file = e.dataTransfer.files[0]
      if (!file || !file.type.startsWith('image/')) return
      this.selectedFile = file
      this.filePreview  = URL.createObjectURL(file)
    },
    removeFile() {
      this.selectedFile = null
      this.filePreview  = null
      if (this.$refs.fileInput) this.$refs.fileInput.value = ''
    },

    // ── YouTube helpers ───────────────────────────────────────────────
    updateYoutubeThumbnail() {
      this.youtubeThumbnail = this.extractYoutubeThumbnail(this.form.video_url)
    },
    extractYoutubeThumbnail(url) {
      if (!url) return null
      const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/)
      return m ? `https://img.youtube.com/vi/${m[1]}/hqdefault.jpg` : null
    },

    // ── Utils ─────────────────────────────────────────────────────────
    showError(msg) {
      Swal.fire({ icon: 'error', title: 'Error', text: msg, customClass: { container: 'swal-high-z' } })
    },
  },
}
</script>

<style scoped>
/* ── Grid ──────────────────────────────────────────────────────── */
.gallery-admin-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  padding: 0 24px 24px;
}

/* ── Item card ─────────────────────────────────────────────────── */
.gallery-admin-item {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
  transition: box-shadow 0.15s;
}
.gallery-admin-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.14); }

.gallery-admin-item__thumb {
  position: relative;
  aspect-ratio: 4/3;
  overflow: hidden;
  background: #f0f0f0;
}
.gallery-admin-item__thumb img {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
}
.gallery-admin-item__badge {
  position: absolute;
  top: 8px; left: 8px;
  display: flex;
  align-items: center;
  gap: 4px;
  background: rgba(0,0,0,0.55);
  color: white;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 20px;
  backdrop-filter: blur(4px);
}
.gallery-admin-item__badge span { font-size: 14px; }
.badge--video { background: rgba(21,101,192,0.8); }

.gallery-admin-item__inactive {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.gallery-admin-item__info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px 4px;
}
.gallery-admin-item__title {
  font-size: 0.82rem;
  font-weight: 600;
  color: #333;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}
.gallery-admin-item__pos {
  font-size: 0.72rem;
  color: #aaa;
  margin-left: 6px;
}

.gallery-admin-item__actions {
  display: flex;
  gap: 4px;
  padding: 4px 8px 10px;
  flex-wrap: wrap;
}
.gal-btn {
  border: none;
  background: #f4f4f4;
  border-radius: 6px;
  cursor: pointer;
  padding: 5px 7px;
  display: flex;
  align-items: center;
  transition: background 0.15s;
}
.gal-btn span { font-size: 17px; color: #555; }
.gal-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.gal-btn:not(:disabled):hover { background: #e0e0e0; }
.gal-btn--blue span { color: #1565c0; }
.gal-btn--red  span { color: #b71c1c; }

/* ── Order bar ─────────────────────────────────────────────────── */
.gallery-order-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 24px;
  background: #fff8e1;
  border-top: 1px solid #ffe082;
  font-size: 0.88rem;
  color: #5d4037;
  flex-wrap: wrap;
}
.gallery-order-bar span.material-symbols-outlined { font-size: 20px; color: #f9a825; }

/* ── Empty / Loading ───────────────────────────────────────────── */
.gallery-empty, .gallery-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 60px 24px;
  color: #aaa;
  font-size: 0.9rem;
}
.gallery-empty span, .gallery-loading span { font-size: 48px; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Modal: type selector ──────────────────────────────────────── */
.type-selector {
  display: flex;
  gap: 12px;
}
.type-option {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 18px;
  border-radius: 8px;
  border: 2px solid #e0e0e0;
  cursor: pointer;
  font-size: 0.88rem;
  transition: border-color 0.15s, background 0.15s;
}
.type-option input { display: none; }
.type-option span  { font-size: 20px; color: #777; }
.type-option.selected { border-color: var(--color-primary, #0ca678); background: #f0fdf4; }
.type-option.selected span { color: var(--color-primary, #0ca678); }

/* ── Modal: file drop ──────────────────────────────────────────── */
.file-drop {
  border: 2px dashed #ddd;
  border-radius: 10px;
  padding: 24px;
  text-align: center;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  color: #888;
  font-size: 0.85rem;
  transition: border-color 0.15s, background 0.15s;
}
.file-drop:hover { border-color: var(--color-primary, #0ca678); background: #f0fdf4; }
.file-drop span { font-size: 32px; color: #ccc; }
.file-drop small { font-size: 0.75rem; color: #bbb; }

.file-preview {
  position: relative;
  margin-top: 10px;
  border-radius: 8px;
  overflow: hidden;
  display: inline-block;
}
.file-preview img { max-width: 100%; max-height: 200px; display: block; border-radius: 8px; }
.file-preview__remove {
  position: absolute;
  top: 6px; right: 6px;
  background: rgba(0,0,0,0.5);
  border: none;
  border-radius: 50%;
  width: 26px; height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.file-preview__remove span { font-size: 16px; color: white; }

/* ── Toggle label ──────────────────────────────────────────────── */
.toggle-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 0.9rem;
}
.field__error { color: #f44336; font-size: 0.8rem; }
</style>
