<template>
  <div>
    <!-- ─── Header ───────────────────────────────────────────────────── -->
    <div class="table__header">
      <div class="table__header--header">
        <div class="table__header--header-content">
          <h3 class="table__header--header-title">Reservas de Servicios</h3>
          <p class="table__header--header-description">
            Gestiona las reservas de los servicios: confirma, cancela o marca como completadas.
          </p>
        </div>
        <div class="table__header--header-button" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <div class="stat-chip stat-chip--pending">
            <span class="material-symbols-outlined">hourglass_empty</span>
            <span>{{ stats.pending }} pendientes</span>
          </div>
          <div class="stat-chip stat-chip--confirmed">
            <span class="material-symbols-outlined">check_circle</span>
            <span>{{ stats.confirmed }} confirmadas</span>
          </div>
          <button type="button" class="button__primary" @click="openCreate">
            <span class="material-symbols-outlined">add</span>
            Nueva Reserva
          </button>
        </div>
      </div>

      <div class="table__header--body" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input
          v-model.trim="filters.q"
          class="field__input"
          style="max-width:240px;"
          placeholder="Referencia, paciente o email..."
          @keyup.enter="applyFilters"
        />
        <select v-model="filters.service_id" class="field__input" style="max-width:200px;" @change="applyFilters">
          <option value="">Todos los servicios</option>
          <option v-for="s in services" :key="s.id" :value="s.id">{{ s.title }}</option>
        </select>
        <select v-model="filters.status" class="field__input" style="max-width:160px;" @change="applyFilters">
          <option value="">Todos los estados</option>
          <option value="pending">Pendiente</option>
          <option value="confirmed">Confirmado</option>
          <option value="completed">Completado</option>
          <option value="cancelled">Cancelado</option>
        </select>
        <input v-model="filters.date_from" type="date" class="field__input" style="max-width:150px;" @change="applyFilters" title="Desde" />
        <input v-model="filters.date_to" type="date" class="field__input" style="max-width:150px;" @change="applyFilters" title="Hasta" />
        <button class="button__secondary button--medium" @click="applyFilters">Filtrar</button>
        <button class="button__secondary button--medium" @click="resetFilters">Limpiar</button>
      </div>
    </div>

    <!-- ─── Table ────────────────────────────────────────────────────── -->
    <div class="datatable">
      <table class="table">
        <thead>
          <tr>
            <th>Referencia</th>
            <th>Servicio</th>
            <th>Paciente</th>
            <th>Contacto</th>
            <th>Fecha / Hora</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loadingList">
            <td colspan="7" class="text-center">Cargando...</td>
          </tr>
          <template v-else-if="dataList.length">
            <tr v-for="row in dataList" :key="row.id">
              <td>
                <span class="ref-code">{{ row.reference_id }}</span>
              </td>
              <td>
                <strong>{{ row.service?.title ?? '—' }}</strong>
              </td>
              <td>{{ row.patient?.name ?? '—' }}</td>
              <td>
                <div class="contact-cell">
                  <span>{{ row.patient?.email }}</span>
                  <span>{{ row.patient?.phone }}</span>
                </div>
              </td>
              <td>
                <div class="datetime-cell">
                  <span>{{ formatDate(row.date) }}</span>
                  <span class="hour-badge">{{ row.hour }}</span>
                </div>
              </td>
              <td>
                <span class="status-badge" :class="statusClass(row.status)">
                  {{ statusLabel(row.status) }}
                </span>
              </td>
              <td>
                <div class="apt-actions">
                  <button
                    v-if="row.status === 'pending'"
                    class="apt-btn apt-btn--green"
                    title="Confirmar"
                    @click="updateStatus(row, 'confirmed')"
                  >
                    <span class="material-symbols-outlined">check_circle</span>
                  </button>
                  <button
                    v-if="row.status === 'confirmed'"
                    class="apt-btn"
                    title="Marcar completado"
                    @click="updateStatus(row, 'completed')"
                  >
                    <span class="material-symbols-outlined">task_alt</span>
                  </button>
                  <button
                    v-if="row.status !== 'cancelled' && row.status !== 'completed'"
                    class="apt-btn apt-btn--orange"
                    title="Cancelar"
                    @click="updateStatus(row, 'cancelled')"
                  >
                    <span class="material-symbols-outlined">cancel</span>
                  </button>
                  <button class="apt-btn" title="Ver detalles" @click="openDetail(row)">
                    <span class="material-symbols-outlined">info</span>
                  </button>
                  <button class="apt-btn apt-btn--red" title="Eliminar" @click="confirmDelete(row)">
                    <span class="material-symbols-outlined">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="7" class="text-center">No hay reservas registradas.</td>
          </tr>
        </tbody>
        <tfoot v-if="pagination.last_page > 1">
          <tr>
            <td colspan="7">
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
                <div class="pagination__meta">Página {{ pagination.current_page }} de {{ pagination.last_page }} · {{ pagination.total }} reservas</div>
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- ─── Modal: Nueva Reserva ─────────────────────────────────────── -->
    <BaseModal v-model="openCreateModal" title="Nueva Reserva" @close="resetCreateForm">
      <div class="grid-2">
        <div class="field">
          <label class="field__label">Servicio *</label>
          <select v-model="createForm.service_id" class="field__input" @change="onServiceOrDateChange">
            <option value="">Selecciona un servicio</option>
            <option v-for="s in services" :key="s.id" :value="s.id">{{ s.title }}</option>
          </select>
          <small v-if="createErrors.service_id" class="field__error">{{ createErrors.service_id }}</small>
        </div>
        <div class="field">
          <label class="field__label">Fecha *</label>
          <input v-model="createForm.date" type="date" class="field__input" :min="todayStr" @change="onServiceOrDateChange" />
          <small v-if="createErrors.date" class="field__error">{{ createErrors.date }}</small>
        </div>
      </div>

      <!-- Slots disponibles -->
      <div class="field">
        <label class="field__label">Hora *</label>
        <div v-if="loadingSlots" style="font-size:0.85rem; color:#888; padding:6px 0;">Cargando horarios disponibles...</div>
        <div v-else-if="createForm.date && createForm.service_id && availableSlots.length === 0" class="no-slots-msg">
          <span class="material-symbols-outlined">event_busy</span>
          No hay horarios disponibles para esta fecha.
        </div>
        <div v-else-if="availableSlots.length > 0" class="slots-grid">
          <button
            v-for="slot in availableSlots"
            :key="slot.hour"
            type="button"
            class="slot-btn"
            :class="{ 'slot-btn--selected': createForm.hour === slot.hour }"
            @click="createForm.hour = slot.hour"
          >
            <span class="slot-hour">{{ slot.hour }}</span>
            <span class="slot-cap">{{ slot.remaining }}/{{ slot.capacity }} cupos</span>
          </button>
        </div>
        <small v-if="createErrors.hour" class="field__error">{{ createErrors.hour }}</small>
      </div>

      <hr style="margin: 16px 0; border:0; border-top:1px solid #eee;" />
      <p style="font-size:0.85rem; font-weight:600; color:#444; margin:0 0 12px;">Datos del paciente</p>

      <div class="grid-2">
        <div class="field">
          <label class="field__label">Nombre completo *</label>
          <input v-model="createForm.name" type="text" class="field__input" placeholder="Ej: María González" />
          <small v-if="createErrors.name" class="field__error">{{ createErrors.name }}</small>
        </div>
        <div class="field">
          <label class="field__label">Email *</label>
          <input v-model="createForm.email" type="email" class="field__input" placeholder="correo@ejemplo.com" />
          <small v-if="createErrors.email" class="field__error">{{ createErrors.email }}</small>
        </div>
      </div>

      <div class="grid-2">
        <div class="field">
          <label class="field__label">Teléfono *</label>
          <input v-model="createForm.phone" type="text" class="field__input" placeholder="+58..." />
          <small v-if="createErrors.phone" class="field__error">{{ createErrors.phone }}</small>
        </div>
        <div class="field">
          <label class="field__label">Fecha de nacimiento *</label>
          <input v-model="createForm.birthday" type="date" class="field__input" />
          <small v-if="createErrors.birthday" class="field__error">{{ createErrors.birthday }}</small>
        </div>
      </div>

      <div class="field">
        <label class="field__label">Notas (opcional)</label>
        <textarea v-model="createForm.notes" class="field__input" rows="2" placeholder="Observaciones adicionales..."></textarea>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openCreateModal = false">Cancelar</button>
        <button class="button__primary" :disabled="loadingCreate" @click="submitCreate">
          {{ loadingCreate ? 'Guardando...' : 'Crear Reserva' }}
        </button>
      </template>
    </BaseModal>

    <!-- ─── Modal: Detalle ────────────────────────────────────────────── -->
    <BaseModal v-model="openDetailModal" title="Detalle de Reserva" @close="openDetailModal = false">
      <div v-if="detailBooking" class="detail-grid">
        <div class="detail-section">
          <h4 class="detail-section__title">
            <span class="material-symbols-outlined">person</span> Paciente
          </h4>
          <div class="detail-row">
            <span class="detail-label">Nombre</span>
            <span>{{ detailBooking.patient?.name }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Email</span>
            <span>{{ detailBooking.patient?.email }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Teléfono</span>
            <span>{{ detailBooking.patient?.phone }}</span>
          </div>
        </div>

        <div class="detail-section">
          <h4 class="detail-section__title">
            <span class="material-symbols-outlined">event</span> Reserva
          </h4>
          <div class="detail-row">
            <span class="detail-label">Referencia</span>
            <span class="ref-code">{{ detailBooking.reference_id }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Servicio</span>
            <span>{{ detailBooking.service?.title }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Fecha</span>
            <span>{{ formatDate(detailBooking.date) }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Hora</span>
            <span>{{ detailBooking.hour }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Estado</span>
            <span class="status-badge" :class="statusClass(detailBooking.status)">
              {{ statusLabel(detailBooking.status) }}
            </span>
          </div>
          <div v-if="detailBooking.notes" class="detail-row">
            <span class="detail-label">Notas</span>
            <span>{{ detailBooking.notes }}</span>
          </div>
        </div>

        <!-- Cambio de estado -->
        <div class="detail-section" style="grid-column: 1 / -1;">
          <h4 class="detail-section__title">
            <span class="material-symbols-outlined">tune</span> Cambiar Estado
          </h4>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button
              v-for="s in availableStatuses(detailBooking.status)"
              :key="s.value"
              class="button__secondary"
              @click="updateStatus(detailBooking, s.value); openDetailModal = false"
            >
              {{ s.label }}
            </button>
          </div>
        </div>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openDetailModal = false">Cerrar</button>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import BaseModal from '../Common/BaseModal.vue'

export default {
  name: 'ServiceBookingsIndex',
  components: { BaseModal },
  data() {
    return {
      dataList:    [],
      pagination:  { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      filters:     { q: '', service_id: '', status: '', date_from: '', date_to: '' },
      loadingList: false,
      services:    [],
      stats:       { pending: 0, confirmed: 0 },

      openDetailModal: false,
      detailBooking:   null,

      // Create booking modal
      openCreateModal: false,
      loadingCreate:   false,
      createForm:      this.emptyCreateForm(),
      createErrors:    {},
      availableSlots:  [],
      loadingSlots:    false,
      todayStr:        new Date().toISOString().split('T')[0],
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
    this.fetchServices()
    this.fetchData()
  },
  methods: {
    emptyCreateForm() {
      return { service_id: '', date: '', hour: '', name: '', email: '', phone: '', birthday: '', notes: '' }
    },

    openCreate() {
      this.createForm    = this.emptyCreateForm()
      this.createErrors  = {}
      this.availableSlots = []
      this.openCreateModal = true
    },
    resetCreateForm() {
      this.createForm    = this.emptyCreateForm()
      this.createErrors  = {}
      this.availableSlots = []
    },

    async onServiceOrDateChange() {
      this.createForm.hour = ''
      this.availableSlots  = []
      if (!this.createForm.service_id || !this.createForm.date) return
      this.loadingSlots = true
      try {
        const { data } = await axios.get('/api/service-bookings/available-slots', {
          params: { service_id: this.createForm.service_id, date: this.createForm.date },
        })
        this.availableSlots = data.available_slots || []
      } catch {
        this.availableSlots = []
      } finally {
        this.loadingSlots = false
      }
    },

    async submitCreate() {
      this.createErrors = {}
      const f = this.createForm
      if (!f.service_id) { this.createErrors.service_id = 'Selecciona un servicio.'; return }
      if (!f.date)        { this.createErrors.date       = 'La fecha es obligatoria.'; return }
      if (!f.hour)        { this.createErrors.hour       = 'Selecciona un horario.'; return }
      if (!f.name.trim()) { this.createErrors.name       = 'El nombre es obligatorio.'; return }
      if (!f.email.trim()){ this.createErrors.email      = 'El email es obligatorio.'; return }
      if (!f.phone.trim()){ this.createErrors.phone      = 'El teléfono es obligatorio.'; return }
      if (!f.birthday)    { this.createErrors.birthday   = 'La fecha de nacimiento es obligatoria.'; return }

      this.loadingCreate = true
      try {
        await axios.post('/api/service-bookings', f)
        Swal.fire({ icon: 'success', title: 'Reserva creada', timer: 1400, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
        this.openCreateModal = false
        this.fetchData(this.pagination.current_page)
      } catch (e) {
        if (e.response?.status === 422) {
          const errs = e.response.data.errors || {}
          this.createErrors = Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, v[0]]))
          if (e.response.data.message && !Object.keys(errs).length) {
            this.showError(e.response.data.message)
          }
        } else {
          this.showError(e.response?.data?.message || 'Error al crear la reserva.')
        }
      } finally {
        this.loadingCreate = false
      }
    },

    async fetchServices() {
      try {
        const { data } = await axios.get('/api/services', { params: { per_page: 100 } })
        this.services = data.data || data
      } catch { /* silencioso */ }
    },

    async fetchData(page = 1) {
      this.loadingList = true
      try {
        const { data } = await axios.get('/api/service-bookings', { params: { page, per_page: 15, ...this.filters } })
        this.dataList  = data.data || data
        if (data.current_page) {
          this.pagination = { current_page: data.current_page, last_page: data.last_page, total: data.total }
        }
        this.calcStats()
      } catch {
        this.showError('No se pudieron cargar las reservas.')
      } finally {
        this.loadingList = false
      }
    },

    calcStats() {
      this.stats.pending   = this.dataList.filter(b => b.status === 'pending').length
      this.stats.confirmed = this.dataList.filter(b => b.status === 'confirmed').length
    },

    applyFilters() { this.fetchData(1) },
    resetFilters()  {
      this.filters = { q: '', service_id: '', status: '', date_from: '', date_to: '' }
      this.fetchData(1)
    },
    goToPage(page) { if (page >= 1 && page <= this.pagination.last_page) this.fetchData(page) },

    openDetail(row) {
      this.detailBooking  = row
      this.openDetailModal = true
    },

    async updateStatus(booking, status) {
      try {
        await axios.put(`/api/service-bookings/${booking.id}`, { status })
        await this.fetchData(this.pagination.current_page)
        Swal.fire({ icon: 'success', title: 'Estado actualizado', timer: 1200, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
      } catch {
        this.showError('No se pudo actualizar el estado.')
      }
    },

    async confirmDelete(row) {
      const result = await Swal.fire({
        title: `¿Eliminar reserva ${row.reference_id}?`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      })
      if (!result.isConfirmed) return
      try {
        await axios.delete(`/api/service-bookings/${row.id}`)
        Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1400, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
        this.fetchData(this.pagination.current_page)
      } catch {
        this.showError('No se pudo eliminar la reserva.')
      }
    },

    availableStatuses(current) {
      const all = [
        { value: 'pending',   label: 'Pendiente' },
        { value: 'confirmed', label: 'Confirmar' },
        { value: 'completed', label: 'Completar' },
        { value: 'cancelled', label: 'Cancelar' },
      ]
      return all.filter(s => s.value !== current)
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
/* ── Stat chips ────────────────────────────────────────────────── */
.stat-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 600;
}
.stat-chip span.material-symbols-outlined { font-size: 16px; }
.stat-chip--pending   { background: #fff3cd; color: #856404; }
.stat-chip--confirmed { background: #e3f2fd; color: #1565c0; }

/* ── Table cells ───────────────────────────────────────────────── */
.ref-code     { font-family: monospace; font-size: 0.82rem; color: #666; }
.contact-cell { display: flex; flex-direction: column; font-size: 0.8rem; color: #555; gap: 1px; }
.datetime-cell { display: flex; flex-direction: column; gap: 2px; }
.hour-badge {
  display: inline-block;
  background: #e8f5e9;
  color: #2e7d32;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 1px 8px;
  border-radius: 10px;
}

/* ── Status badges ─────────────────────────────────────────────── */
.status-badge      { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; display: inline-block; }
.status--pending   { background: #fff3cd; color: #856404; }
.status--confirmed { background: #e3f2fd; color: #1565c0; }
.status--completed { background: #e8f5e9; color: #2e7d32; }
.status--cancelled { background: #fce4ec; color: #b71c1c; }

/* ── Action buttons ────────────────────────────────────────────── */
.apt-actions { display: flex; gap: 4px; align-items: center; flex-wrap: wrap; }
.apt-btn { border: none; background: #f4f4f4; border-radius: 6px; cursor: pointer; padding: 5px 7px; display: flex; align-items: center; transition: background 0.15s; }
.apt-btn span { font-size: 18px; color: #555; }
.apt-btn:not(:disabled):hover { background: #e0e0e0; }
.apt-btn--green  span { color: #2e7d32; }
.apt-btn--orange span { color: #e65100; }
.apt-btn--red    span { color: #b71c1c; }

/* ── Detail modal ──────────────────────────────────────────────── */
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.detail-section { background: #f8f9fa; border-radius: 8px; padding: 14px; }
.detail-section__title {
  display: flex; align-items: center; gap: 6px;
  margin: 0 0 12px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
}
.detail-section__title span { font-size: 18px; }
.detail-row { display: flex; justify-content: space-between; gap: 8px; font-size: 0.85rem; margin-bottom: 6px; }
.detail-label { color: #888; flex-shrink: 0; }

/* ── Create form ───────────────────────────────────────────────── */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

.slots-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 6px 0;
}
.slot-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  border: 2px solid #ddd;
  background: #fafafa;
  border-radius: 8px;
  padding: 8px 14px;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
  min-width: 90px;
}
.slot-btn:hover { border-color: var(--color-primary, #0ca678); background: #f0fdf4; }
.slot-btn--selected { border-color: var(--color-primary, #0ca678); background: #e6fcf5; }
.slot-hour { font-weight: 700; font-size: 0.9rem; color: #222; }
.slot-cap  { font-size: 0.72rem; color: #666; }
.slot-btn--selected .slot-hour { color: var(--color-primary, #0ca678); }

.no-slots-msg {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  color: #999;
  padding: 8px 0;
}
.no-slots-msg span { font-size: 18px; }

/* ── Table misc ────────────────────────────────────────────────── */
.text-center { text-align: center; padding: 24px; color: #888; }
.field__error { color: #f44336; font-size: 0.8rem; }

@media (max-width: 640px) {
  .detail-grid { grid-template-columns: 1fr; }
  .grid-2 { grid-template-columns: 1fr; }
}
</style>
