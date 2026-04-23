<template>
  <div>
    <!-- ─── Header ───────────────────────────────────────────────────── -->
    <div class="table__header">
      <div class="table__header--header">
        <div class="table__header--header-content">
          <h3 class="table__header--header-title">Servicios y Salas</h3>
          <p class="table__header--header-description">
            Administra los servicios disponibles (Rehabilitación, Spa, Gym, etc.), sus descripciones y horarios con capacidad.
          </p>
        </div>
        <div class="table__header--header-button">
          <button type="button" class="button__primary" @click="openCreate">
            <span class="material-symbols-outlined">add</span>
            Nuevo Servicio
          </button>
        </div>
      </div>

      <div class="table__header--body" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input
          v-model.trim="filters.q"
          class="field__input"
          style="max-width:260px;"
          placeholder="Buscar por nombre..."
          @keyup.enter="applyFilters"
        />
        <select v-model="filters.status" class="field__input" style="max-width:180px;" @change="applyFilters">
          <option value="">Todos los estados</option>
          <option value="1">Activos</option>
          <option value="0">Inactivos</option>
        </select>
        <button class="button__secondary button--medium" @click="applyFilters">Filtrar</button>
        <button class="button__secondary button--medium" @click="resetFilters">Limpiar</button>
      </div>
    </div>

    <!-- ─── Table ────────────────────────────────────────────────────── -->
    <div class="datatable">
      <table class="table">
        <thead>
          <tr>
            <th style="width:70px;">Foto</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Horarios</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loadingList">
            <td colspan="6" class="text-center">Cargando...</td>
          </tr>
          <template v-else-if="dataList.length">
            <tr v-for="row in dataList" :key="row.id">
              <td>
                <img
                  v-if="row.photo_url"
                  :src="row.photo_url"
                  class="srv-avatar"
                  :alt="row.title"
                />
                <div v-else class="srv-avatar-placeholder">
                  <span class="material-symbols-outlined">spa</span>
                </div>
              </td>
              <td><strong>{{ row.title }}</strong></td>
              <td>
                <span class="srv-desc">{{ row.description || '—' }}</span>
              </td>
              <td>
                <div class="srv-schedules-mini">
                  <span v-if="!row.schedules || row.schedules.length === 0" style="color:#aaa; font-size:0.8rem;">Sin horarios</span>
                  <span
                    v-for="s in (row.schedules || []).slice(0, 3)"
                    :key="s.id"
                    class="schedule-mini-badge"
                  >
                    {{ dayShort(s.day) }} {{ s.start_time?.substring(0,5) }}-{{ s.end_time?.substring(0,5) }}
                    <span class="capacity-dot" :title="'Capacidad: ' + s.capacity">×{{ s.capacity }}</span>
                  </span>
                  <span v-if="(row.schedules || []).length > 3" style="font-size:0.75rem; color:#888;">
                    +{{ row.schedules.length - 3 }} más
                  </span>
                </div>
              </td>
              <td>
                <span class="status-badge" :class="row.is_active ? 'status--confirmed' : 'status--cancelled'">
                  {{ row.is_active ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td>
                <div class="apt-actions">
                  <button class="apt-btn apt-btn--blue" title="Editar" @click="openEdit(row)">
                    <span class="material-symbols-outlined">edit</span>
                  </button>
                  <button class="apt-btn" title="Gestionar horarios y fechas especiales" @click="openSchedules(row)">
                    <span class="material-symbols-outlined">schedule</span>
                  </button>
                  <button class="apt-btn apt-btn--green" title="Ver reservas" @click="openBookings(row)">
                    <span class="material-symbols-outlined">event_available</span>
                  </button>
                  <button class="apt-btn apt-btn--red" title="Eliminar" @click="confirmDelete(row)">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="6" class="text-center">No hay servicios registrados.</td>
          </tr>
        </tbody>
        <tfoot v-if="pagination.last_page > 1">
          <tr>
            <td colspan="6">
              <div class="pagination">
                <button class="pagination__button" :disabled="pagination.current_page <= 1" @click="goToPage(pagination.current_page - 1)">Anterior</button>
                <button
                  v-for="p in pagesToShow"
                  :key="p"
                  class="pagination__page"
                  :class="{ 'is-active': p === pagination.current_page }"
                  @click="goToPage(p)"
                >{{ p }}</button>
                <button class="pagination__button" :disabled="pagination.current_page >= pagination.last_page" @click="goToPage(pagination.current_page + 1)">Siguiente</button>
                <div class="pagination__meta">Página {{ pagination.current_page }} de {{ pagination.last_page }} · {{ pagination.total }} servicios</div>
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- ─── Modal: Crear / Editar ─────────────────────────────────────── -->
    <BaseModal
      v-model="openCreateModal"
      :title="isEdit ? 'Editar Servicio' : 'Nuevo Servicio'"
      @close="resetForm"
    >
      <div class="photo-preview-container">
        <div class="photo-preview">
          <img v-if="photoPreview" :src="photoPreview" />
          <img v-else-if="form.photo_url" :src="form.photo_url" />
          <div v-else class="photo-placeholder">
            <span class="material-symbols-outlined">spa</span>
          </div>
        </div>
        <div class="field" style="flex:1;">
          <label class="field__label">Foto del servicio</label>
          <input type="file" @change="handleFileUpload" class="field__input" accept="image/*" />
          <small style="color:#888; font-size:0.78rem;">Máx. 3 MB · JPG, PNG, WebP</small>
        </div>
      </div>

      <div class="field">
        <label class="field__label">Nombre del servicio *</label>
        <input v-model="form.title" type="text" class="field__input" placeholder="Ej: Sala de Rehabilitación" />
        <small v-if="errors.title" class="field__error">{{ errors.title }}</small>
      </div>

      <div class="field">
        <label class="field__label">Descripción</label>
        <textarea v-model="form.description" class="field__input" rows="3" placeholder="Describe el servicio..."></textarea>
      </div>

      <div class="field">
        <label class="field__label">Estado</label>
        <select v-model="form.is_active" class="field__input">
          <option :value="true">Activo (visible en el sitio)</option>
          <option :value="false">Inactivo</option>
        </select>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openCreateModal = false">Cancelar</button>
        <button class="button__primary" :disabled="loadingSave" @click="save">
          {{ loadingSave ? 'Guardando...' : (isEdit ? 'Actualizar' : 'Guardar') }}
        </button>
      </template>
    </BaseModal>

    <!-- ─── Modal: Horarios + Fechas Especiales ────────────────────────── -->
    <BaseModal
      v-model="openSchedulesModal"
      :title="'Horarios: ' + (currentService?.title ?? '')"
      @close="closeSchedules"
    >
      <!-- Tabs -->
      <div class="schedule-tabs">
        <button
          class="schedule-tab"
          :class="{ active: scheduleTab === 'regular' }"
          @click="scheduleTab = 'regular'"
        >
          <span class="material-symbols-outlined">calendar_month</span>
          Horarios Semanales
        </button>
        <button
          class="schedule-tab"
          :class="{ active: scheduleTab === 'custom' }"
          @click="scheduleTab = 'custom'; fetchCustomAvailabilities()"
        >
          <span class="material-symbols-outlined">event_busy</span>
          Fechas Especiales
        </button>
      </div>

      <!-- TAB 1: Horarios semanales regulares -->
      <div v-if="scheduleTab === 'regular'" class="schedules-container">
        <div class="schedule-row schedule-row--header">
          <span>Día</span>
          <span>Inicio</span>
          <span>Fin</span>
          <span>Capacidad</span>
          <span></span>
        </div>

        <div v-for="(item, index) in scheduleForm" :key="index" class="schedule-row">
          <div class="field">
            <select v-model="item.day" class="field__input">
              <option value="monday">Lunes</option>
              <option value="tuesday">Martes</option>
              <option value="wednesday">Miércoles</option>
              <option value="thursday">Jueves</option>
              <option value="friday">Viernes</option>
              <option value="saturday">Sábado</option>
              <option value="sunday">Domingo</option>
            </select>
          </div>
          <div class="field">
            <input v-model="item.start_time" type="time" class="field__input" />
          </div>
          <div class="field">
            <input v-model="item.end_time" type="time" class="field__input" />
          </div>
          <div class="field">
            <div class="capacity-input-wrap">
              <span class="material-symbols-outlined capacity-icon">group</span>
              <input
                v-model.number="item.capacity"
                type="number"
                min="1"
                max="999"
                class="field__input capacity-input"
                placeholder="1"
              />
            </div>
          </div>
          <button class="apt-btn apt-btn--red" @click="removeScheduleRow(index)" title="Eliminar turno">
            <span class="material-symbols-outlined">delete</span>
          </button>
        </div>

        <div v-if="scheduleForm.length === 0" class="no-schedules-msg">
          <span class="material-symbols-outlined">schedule</span>
          <p>No hay horarios aún. Agrega un turno.</p>
        </div>

        <button class="button__secondary button--small" @click="addScheduleRow" style="margin-top:12px;">
          <span class="material-symbols-outlined">add</span>
          Añadir turno
        </button>
      </div>

      <!-- TAB 2: Fechas especiales -->
      <div v-if="scheduleTab === 'custom'" class="schedules-container">
        <!-- Formulario de nueva fecha especial -->
        <div class="custom-form">
          <h4 style="margin: 0 0 12px; font-size: 0.95rem; font-weight: 600;">Agregar fecha especial</h4>
          <div class="custom-form__row">
            <div class="field">
              <label class="field__label">Fecha</label>
              <input v-model="customForm.date" type="date" class="field__input" :min="todayStr" />
            </div>
            <div class="field">
              <label class="field__label">Estado</label>
              <select v-model="customForm.is_available" class="field__input">
                <option :value="false">Bloqueado (sin atención)</option>
                <option :value="true">Horario personalizado</option>
              </select>
            </div>
          </div>
          <div v-if="customForm.is_available" class="custom-form__row">
            <div class="field">
              <label class="field__label">Inicio</label>
              <input v-model="customForm.start_time" type="time" class="field__input" />
            </div>
            <div class="field">
              <label class="field__label">Fin</label>
              <input v-model="customForm.end_time" type="time" class="field__input" />
            </div>
            <div class="field">
              <label class="field__label">Capacidad (opcional)</label>
              <div class="capacity-input-wrap">
                <span class="material-symbols-outlined capacity-icon">group</span>
                <input
                  v-model.number="customForm.capacity_override"
                  type="number"
                  min="1"
                  class="field__input capacity-input"
                  placeholder="Usa la del horario"
                />
              </div>
            </div>
          </div>
          <button
            class="button__primary button--small"
            :disabled="loadingCustom || !customForm.date"
            @click="saveCustomAvailability"
            style="margin-top: 8px;"
          >
            {{ loadingCustom ? 'Guardando...' : 'Guardar fecha' }}
          </button>
        </div>

        <hr style="margin: 16px 0; border: 0; border-top: 1px solid #eee;" />

        <!-- Listado de fechas especiales -->
        <p style="font-size: 0.85rem; color: #666; margin-bottom: 8px;">Próximas fechas especiales:</p>
        <div v-if="loadingCustom" style="color:#666; font-size:0.9rem;">Cargando...</div>
        <div v-else-if="customAvailabilities.length === 0" style="color:#999; font-size:0.9rem;">
          No hay fechas especiales configuradas.
        </div>
        <div v-for="item in customAvailabilities" :key="item.id" class="custom-row">
          <span class="material-symbols-outlined" :style="{ color: item.is_available ? '#0ca678' : '#fa5252' }">
            {{ item.is_available ? 'schedule' : 'event_busy' }}
          </span>
          <span class="custom-row__date">{{ formatDate(item.date) }}</span>
          <span class="custom-row__info">
            <template v-if="item.is_available">
              {{ item.start_time?.substring(0,5) }} – {{ item.end_time?.substring(0,5) }}
              <span v-if="item.capacity_override" class="capacity-dot" style="margin-left:6px;">
                ×{{ item.capacity_override }}
              </span>
            </template>
            <template v-else>
              <span style="color:#fa5252; font-weight:500;">Bloqueado</span>
            </template>
          </span>
          <button class="button__ghost text-danger" @click="deleteCustomAvailability(item.id)">
            <span class="material-symbols-outlined">delete</span>
          </button>
        </div>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openSchedulesModal = false">Cerrar</button>
        <button
          v-if="scheduleTab === 'regular'"
          class="button__primary"
          :disabled="loadingSchedules"
          @click="saveSchedules"
        >
          {{ loadingSchedules ? 'Guardando...' : 'Guardar horarios' }}
        </button>
      </template>
    </BaseModal>

    <!-- ─── Modal: Reservas del servicio ───────────────────────────────── -->
    <BaseModal
      v-model="openBookingsModal"
      :title="'Reservas: ' + (currentService?.title ?? '')"
      @close="closeBookings"
    >
      <!-- Filters -->
      <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px;">
        <input
          v-model.trim="bookingFilters.q"
          class="field__input"
          style="max-width:200px; font-size:0.85rem;"
          placeholder="Buscar referencia o paciente..."
          @keyup.enter="fetchBookings(1)"
        />
        <select v-model="bookingFilters.status" class="field__input" style="max-width:160px; font-size:0.85rem;" @change="fetchBookings(1)">
          <option value="">Todos los estados</option>
          <option value="pending">Pendiente</option>
          <option value="confirmed">Confirmado</option>
          <option value="completed">Completado</option>
          <option value="cancelled">Cancelado</option>
        </select>
        <input v-model="bookingFilters.date_from" type="date" class="field__input" style="max-width:150px; font-size:0.85rem;" @change="fetchBookings(1)" />
        <input v-model="bookingFilters.date_to" type="date" class="field__input" style="max-width:150px; font-size:0.85rem;" @change="fetchBookings(1)" />
      </div>

      <!-- Table -->
      <div v-if="loadingBookings" style="text-align:center; padding:20px; color:#888;">Cargando reservas...</div>
      <div v-else-if="bookingList.length === 0" style="text-align:center; padding:20px; color:#aaa;">
        No hay reservas para este servicio.
      </div>
      <div v-else class="booking-list">
        <div v-for="b in bookingList" :key="b.id" class="booking-item">
          <div class="booking-item__info">
            <span class="booking-ref">{{ b.reference_id }}</span>
            <span class="booking-patient">{{ b.patient?.name }}</span>
            <span class="booking-contact">{{ b.patient?.email }} · {{ b.patient?.phone }}</span>
            <span class="booking-datetime">
              <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">event</span>
              {{ formatDate(b.date) }} · {{ b.hour }}
            </span>
            <span v-if="b.notes" class="booking-notes">{{ b.notes }}</span>
          </div>
          <div class="booking-item__actions">
            <span class="status-badge" :class="statusClass(b.status)">{{ statusLabel(b.status) }}</span>
            <div style="display:flex; gap:4px; margin-top:6px; flex-wrap:wrap;">
              <button
                v-if="b.status === 'pending'"
                class="apt-btn apt-btn--green"
                title="Confirmar"
                @click="updateBookingStatus(b, 'confirmed')"
              >
                <span class="material-symbols-outlined">check_circle</span>
              </button>
              <button
                v-if="b.status === 'confirmed'"
                class="apt-btn"
                title="Marcar completado"
                @click="updateBookingStatus(b, 'completed')"
              >
                <span class="material-symbols-outlined">task_alt</span>
              </button>
              <button
                v-if="b.status !== 'cancelled' && b.status !== 'completed'"
                class="apt-btn apt-btn--orange"
                title="Cancelar"
                @click="updateBookingStatus(b, 'cancelled')"
              >
                <span class="material-symbols-outlined">cancel</span>
              </button>
              <button class="apt-btn apt-btn--red" title="Eliminar" @click="deleteBooking(b)">
                <span class="material-symbols-outlined">delete</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="bookingPagination.last_page > 1" class="pagination" style="margin-top:12px;">
        <button class="pagination__button" :disabled="bookingPagination.current_page <= 1" @click="fetchBookings(bookingPagination.current_page - 1)">Anterior</button>
        <span style="font-size:0.82rem; color:#666;">
          Página {{ bookingPagination.current_page }} de {{ bookingPagination.last_page }}
        </span>
        <button class="pagination__button" :disabled="bookingPagination.current_page >= bookingPagination.last_page" @click="fetchBookings(bookingPagination.current_page + 1)">Siguiente</button>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openBookingsModal = false">Cerrar</button>
      </template>
    </BaseModal>

  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import BaseModal from '../Common/BaseModal.vue'

export default {
  name: 'ServicesIndex',
  components: { BaseModal },
  data() {
    return {
      dataList:   [],
      pagination: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
      filters:    { q: '', status: '' },
      loadingList: false,

      // Create / Edit modal
      openCreateModal: false,
      isEdit:          false,
      editingId:       null,
      loadingSave:     false,
      form:            this.emptyForm(),
      errors:          {},
      photoPreview:    null,
      photoFile:       null,

      // Schedules modal
      openSchedulesModal: false,
      currentService:     null,
      scheduleForm:       [],
      loadingSchedules:   false,
      scheduleTab:        'regular',

      // Custom availabilities
      customAvailabilities: [],
      customForm: { date: '', is_available: false, start_time: '08:00', end_time: '12:00', capacity_override: null },
      loadingCustom: false,
      todayStr: new Date().toISOString().split('T')[0],

      // Bookings modal
      openBookingsModal:  false,
      bookingList:        [],
      bookingPagination:  { current_page: 1, last_page: 1, total: 0 },
      bookingFilters:     { q: '', status: '', date_from: '', date_to: '' },
      loadingBookings:    false,
    }
  },
  computed: {
    pagesToShow() {
      const total   = this.pagination.last_page || 1
      const current = this.pagination.current_page || 1
      const delta   = 3
      const start   = Math.max(1, current - delta)
      const end     = Math.min(total, current + delta)
      const pages   = []
      for (let i = start; i <= end; i++) pages.push(i)
      return pages
    },
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    emptyForm() {
      return { title: '', description: '', is_active: true, photo_url: null }
    },

    // ── Fetch ─────────────────────────────────────────────────────────
    async fetchData(page = 1) {
      this.loadingList = true
      try {
        const { data } = await axios.get('/api/services', { params: { page, ...this.filters } })
        this.dataList   = data.data || data
        if (data.current_page) {
          this.pagination = { current_page: data.current_page, last_page: data.last_page, total: data.total }
        }
      } catch {
        this.showError('No se pudieron cargar los servicios.')
      } finally {
        this.loadingList = false
      }
    },
    applyFilters() { this.fetchData(1) },
    resetFilters()  { this.filters = { q: '', status: '' }; this.fetchData(1) },
    goToPage(page)  { if (page >= 1 && page <= this.pagination.last_page) this.fetchData(page) },

    // ── Create / Edit ─────────────────────────────────────────────────
    openCreate() {
      this.isEdit = false; this.editingId = null; this.errors = {}
      this.form = this.emptyForm(); this.photoPreview = null; this.photoFile = null
      this.openCreateModal = true
    },
    openEdit(row) {
      this.isEdit = true; this.editingId = row.id; this.errors = {}
      this.form = { title: row.title, description: row.description || '', is_active: !!row.is_active, photo_url: row.photo_url }
      this.photoPreview = null; this.photoFile = null
      this.openCreateModal = true
    },
    resetForm() {
      this.form = this.emptyForm(); this.photoPreview = null; this.photoFile = null; this.errors = {}
    },
    handleFileUpload(e) {
      const file = e.target.files[0]
      if (!file) return
      this.photoFile    = file
      this.photoPreview = URL.createObjectURL(file)
    },
    async save() {
      this.errors = {}
      if (!this.form.title.trim()) { this.errors.title = 'El nombre es obligatorio.'; return }

      const fd = new FormData()
      fd.append('title',       this.form.title)
      fd.append('description', this.form.description || '')
      fd.append('is_active',   this.form.is_active ? '1' : '0')
      if (this.photoFile) fd.append('photo', this.photoFile)
      if (this.isEdit)    fd.append('_method', 'PUT')

      this.loadingSave = true
      try {
        const url = this.isEdit ? `/api/services/${this.editingId}` : '/api/services'
        await axios.post(url, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        Swal.fire({ icon: 'success', title: 'Guardado', timer: 1400, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
        this.openCreateModal = false
        this.fetchData(this.pagination.current_page)
      } catch (e) {
        if (e.response?.status === 422) {
          const errs = e.response.data.errors || {}
          this.errors = Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, v[0]]))
        } else {
          this.showError(e.response?.data?.message || 'Error al guardar el servicio.')
        }
      } finally {
        this.loadingSave = false
      }
    },

    // ── Delete ────────────────────────────────────────────────────────
    async confirmDelete(row) {
      const result = await Swal.fire({
        title: `¿Eliminar "${row.title}"?`,
        text: 'Se eliminarán también todos sus horarios y fechas especiales. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      })
      if (!result.isConfirmed) return
      try {
        await axios.delete(`/api/services/${row.id}`)
        Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1400, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
        this.fetchData(this.pagination.current_page)
      } catch {
        this.showError('No se pudo eliminar el servicio.')
      }
    },

    // ── Schedules ─────────────────────────────────────────────────────
    openSchedules(row) {
      this.currentService = row
      this.scheduleTab    = 'regular'
      this.scheduleForm   = row.schedules?.length
        ? JSON.parse(JSON.stringify(row.schedules)).map(s => ({
            id:         s.id,
            day:        s.day,
            start_time: s.start_time?.substring(0, 5),
            end_time:   s.end_time?.substring(0, 5),
            capacity:   s.capacity ?? 1,
          }))
        : []
      this.customAvailabilities = []
      this.customForm = { date: '', is_available: false, start_time: '08:00', end_time: '12:00', capacity_override: null }
      this.openSchedulesModal = true
    },
    closeSchedules() {
      this.openSchedulesModal   = false
      this.currentService       = null
      this.scheduleForm         = []
      this.scheduleTab          = 'regular'
      this.customAvailabilities = []
    },
    addScheduleRow() {
      this.scheduleForm.push({ day: 'monday', start_time: '08:00', end_time: '12:00', capacity: 1 })
    },
    removeScheduleRow(index) {
      this.scheduleForm.splice(index, 1)
    },
    async saveSchedules() {
      for (const s of this.scheduleForm) {
        if (!s.capacity || s.capacity < 1) s.capacity = 1
      }
      this.loadingSchedules = true
      try {
        await axios.put(`/api/services/${this.currentService.id}`, {
          schedules: this.scheduleForm,
        })
        Swal.fire({ icon: 'success', title: 'Horarios guardados', timer: 1400, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
        this.openSchedulesModal = false
        this.fetchData(this.pagination.current_page)
      } catch {
        this.showError('Error al guardar los horarios.')
      } finally {
        this.loadingSchedules = false
      }
    },

    // ── Custom Availabilities ──────────────────────────────────────────
    async fetchCustomAvailabilities() {
      if (!this.currentService) return
      this.loadingCustom = true
      try {
        const { data } = await axios.get(`/api/services/${this.currentService.id}/custom-availabilities`)
        this.customAvailabilities = data
      } catch {
        this.showError('No se pudo cargar la disponibilidad especial.')
      } finally {
        this.loadingCustom = false
      }
    },
    async saveCustomAvailability() {
      if (!this.customForm.date) return
      this.loadingCustom = true
      try {
        await axios.post(
          `/api/services/${this.currentService.id}/custom-availabilities`,
          this.customForm
        )
        this.customForm = { date: '', is_available: false, start_time: '08:00', end_time: '12:00', capacity_override: null }
        await this.fetchCustomAvailabilities()
        Swal.fire({ icon: 'success', title: 'Fecha guardada', timer: 1500, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
      } catch (e) {
        this.showError(e.response?.data?.message || 'Error al guardar la fecha especial.')
      } finally {
        this.loadingCustom = false
      }
    },
    async deleteCustomAvailability(id) {
      const result = await Swal.fire({
        title: '¿Eliminar fecha especial?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      })
      if (!result.isConfirmed) return
      try {
        await axios.delete(`/api/services/${this.currentService.id}/custom-availabilities/${id}`)
        await this.fetchCustomAvailabilities()
      } catch {
        this.showError('No se pudo eliminar.')
      }
    },

    // ── Bookings ───────────────────────────────────────────────────────
    openBookings(row) {
      this.currentService    = row
      this.bookingList       = []
      this.bookingFilters    = { q: '', status: '', date_from: '', date_to: '' }
      this.bookingPagination = { current_page: 1, last_page: 1, total: 0 }
      this.openBookingsModal = true
      this.fetchBookings(1)
    },
    closeBookings() {
      this.openBookingsModal = false
      this.bookingList       = []
    },
    async fetchBookings(page = 1) {
      if (!this.currentService) return
      this.loadingBookings = true
      try {
        const { data } = await axios.get('/api/service-bookings', {
          params: { page, service_id: this.currentService.id, per_page: 10, ...this.bookingFilters }
        })
        this.bookingList       = data.data || data
        this.bookingPagination = { current_page: data.current_page, last_page: data.last_page, total: data.total }
      } catch {
        this.showError('No se pudieron cargar las reservas.')
      } finally {
        this.loadingBookings = false
      }
    },
    async updateBookingStatus(booking, status) {
      try {
        await axios.put(`/api/service-bookings/${booking.id}`, { status })
        await this.fetchBookings(this.bookingPagination.current_page)
      } catch {
        this.showError('No se pudo actualizar el estado de la reserva.')
      }
    },
    async deleteBooking(booking) {
      const result = await Swal.fire({
        title: `¿Eliminar reserva ${booking.reference_id}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      })
      if (!result.isConfirmed) return
      try {
        await axios.delete(`/api/service-bookings/${booking.id}`)
        await this.fetchBookings(this.bookingPagination.current_page)
      } catch {
        this.showError('No se pudo eliminar la reserva.')
      }
    },

    // ── Helpers ───────────────────────────────────────────────────────
    dayShort(day) {
      return { monday:'Lu', tuesday:'Ma', wednesday:'Mi', thursday:'Ju', friday:'Vi', saturday:'Sa', sunday:'Do' }[day] ?? day
    },
    formatDate(dateStr) {
      if (!dateStr) return ''
      const [y, m, d] = String(dateStr).substring(0, 10).split('-')
      return `${d}/${m}/${y}`
    },
    statusLabel(status) {
      return { pending:'Pendiente', confirmed:'Confirmado', completed:'Completado', cancelled:'Cancelado' }[status] ?? status
    },
    statusClass(status) {
      return {
        pending:   'status--pending',
        confirmed: 'status--confirmed',
        completed: 'status--completed',
        cancelled: 'status--cancelled',
      }[status] ?? ''
    },
    showError(msg) {
      Swal.fire({ icon: 'error', title: 'Error', text: msg, customClass: { container: 'swal-high-z' } })
    },
  },
}
</script>

<style scoped>
/* ── Table helpers ─────────────────────────────────────────────── */
.srv-avatar {
  width: 48px; height: 48px;
  border-radius: 8px;
  object-fit: cover;
  display: block;
}
.srv-avatar-placeholder {
  width: 48px; height: 48px;
  border-radius: 8px;
  background: #f0f0f0;
  display: flex; align-items: center; justify-content: center;
}
.srv-avatar-placeholder span { font-size: 24px; color: #bbb; }

.srv-desc {
  font-size: 0.8rem;
  color: #666;
  max-width: 240px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ── Schedule mini badges ──────────────────────────────────────── */
.srv-schedules-mini { display: flex; flex-wrap: wrap; gap: 4px; }
.schedule-mini-badge {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  background: #e8f5e9;
  color: #2e7d32;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 12px;
}
.capacity-dot {
  background: #2e7d32;
  color: white;
  border-radius: 10px;
  padding: 0 5px;
  font-size: 0.68rem;
  font-weight: 700;
}

/* ── Status badges ─────────────────────────────────────────────── */
.status-badge      { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.status--pending   { background: #fff3cd; color: #856404; }
.status--confirmed { background: #e3f2fd; color: #1565c0; }
.status--completed { background: #e8f5e9; color: #2e7d32; }
.status--cancelled { background: #fce4ec; color: #b71c1c; }

/* ── Row action buttons ────────────────────────────────────────── */
.apt-actions { display: flex; gap: 4px; align-items: center; }
.apt-btn { border: none; background: #f4f4f4; border-radius: 6px; cursor: pointer; padding: 5px 7px; display: flex; align-items: center; transition: background 0.15s; }
.apt-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.apt-btn span { font-size: 18px; color: #555; }
.apt-btn:not(:disabled):hover { background: #e0e0e0; }
.apt-btn--blue   span { color: #1565c0; }
.apt-btn--green  span { color: #2e7d32; }
.apt-btn--orange span { color: #e65100; }
.apt-btn--red    span { color: #b71c1c; }

/* ── Photo preview ─────────────────────────────────────────────── */
.photo-preview-container {
  display: flex; align-items: center; gap: 20px;
  margin-bottom: 20px; background: #f8f9fa;
  padding: 16px; border-radius: 8px;
}
.photo-preview, .photo-placeholder {
  width: 90px; height: 90px; border-radius: 12px;
  overflow: hidden; border: 2px solid #ddd;
  display: flex; align-items: center; justify-content: center;
  background: white; flex-shrink: 0;
}
.photo-preview img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder span { font-size: 40px; color: #ccc; }

/* ── Schedule tabs ─────────────────────────────────────────────── */
.schedule-tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 16px;
  border-bottom: 2px solid #eee;
}
.schedule-tab {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 0.9rem;
  color: #666;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: color 0.15s, border-color 0.15s;
}
.schedule-tab.active {
  color: var(--color-primary, #0ca678);
  border-bottom-color: var(--color-primary, #0ca678);
  font-weight: 600;
}

/* ── Schedule rows ─────────────────────────────────────────────── */
.schedules-container { display: flex; flex-direction: column; gap: 6px; }

.schedule-row {
  display: grid;
  grid-template-columns: 1.4fr 1fr 1fr 110px 36px;
  gap: 8px;
  align-items: center;
}
.schedule-row--header {
  font-size: 0.75rem;
  font-weight: 700;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 0 4px;
}
.schedule-row--header span:last-child { display: none; }

/* Capacity input */
.capacity-input-wrap { position: relative; display: flex; align-items: center; }
.capacity-icon { position: absolute; left: 8px; font-size: 16px; color: #888; pointer-events: none; }
.capacity-input { padding-left: 30px !important; }

/* Empty state */
.no-schedules-msg {
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  padding: 24px; color: #bbb; font-size: 0.85rem;
}
.no-schedules-msg span { font-size: 36px; }

/* ── Custom availability ───────────────────────────────────────── */
.custom-form { background: #f8f9fa; padding: 12px; border-radius: 8px; }
.custom-form__row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
  margin-bottom: 8px;
}
.custom-row {
  display: grid;
  grid-template-columns: auto 1fr 1fr auto;
  gap: 10px;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
}
.custom-row__date { font-weight: 600; }
.custom-row__info { color: #555; display: flex; align-items: center; }
.button__ghost { border: none; background: none; cursor: pointer; padding: 4px; }
.text-danger { color: #fa5252; }

/* ── Booking list ──────────────────────────────────────────────── */
.booking-list { display: flex; flex-direction: column; gap: 8px; max-height: 400px; overflow-y: auto; }
.booking-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  background: #f8f9fa;
  border-radius: 8px;
  padding: 10px 14px;
  border: 1px solid #eee;
}
.booking-item__info { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.booking-ref      { font-size: 0.78rem; color: #888; font-family: monospace; }
.booking-patient  { font-weight: 600; font-size: 0.92rem; }
.booking-contact  { font-size: 0.8rem; color: #666; }
.booking-datetime { font-size: 0.82rem; color: #444; margin-top: 2px; }
.booking-notes    { font-size: 0.8rem; color: #888; font-style: italic; }
.booking-item__actions { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }

/* ── Table misc ────────────────────────────────────────────────── */
.text-center { text-align: center; padding: 24px; color: #888; }
.field__error { color: #f44336; font-size: 0.8rem; }
@media (max-width: 640px) {
  .schedule-row { grid-template-columns: 1fr 1fr 1fr 80px 36px; }
  .booking-item { flex-direction: column; }
}
</style>
