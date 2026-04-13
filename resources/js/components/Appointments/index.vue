<template>
  <div>
    <!-- ─── Header ──────────────────────────────────────────────────────── -->
    <div class="table__header">
      <div class="table__header--header">
        <div class="table__header--header-content">
          <h3 class="table__header--header-title">Gestión de Citas</h3>
          <p class="table__header--header-description">
            Administra todas las citas: confirma, pospone, cancela y agenda nuevas consultas.
          </p>
        </div>
        <div class="table__header--header-button">
          <button type="button" class="button__primary" @click="openCreate">
            <span class="material-symbols-outlined">add</span>
            Nueva Cita
          </button>
        </div>
      </div>

      <div class="table__header--body" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input
          v-model.trim="filters.q"
          class="field__input"
          style="max-width:260px;"
          placeholder="Ref, paciente, email..."
          @keyup.enter="applyFilters"
        />
        <select v-model="filters.status" class="field__input" style="max-width:180px;" @change="applyFilters">
          <option value="">Todos los estados</option>
          <option value="pending">Pendientes</option>
          <option value="confirmed">Confirmadas</option>
          <option value="completed">Completadas</option>
          <option value="cancelled">Canceladas</option>
        </select>
        <input v-model="filters.date" type="date" class="field__input" style="max-width:160px;" @change="applyFilters" />
        <button class="button__secondary button--medium" @click="applyFilters">Filtrar</button>
        <button class="button__secondary button--medium" @click="resetFilters">Limpiar</button>
      </div>
    </div>

    <!-- ─── Table ───────────────────────────────────────────────────────── -->
    <div class="datatable">
      <table class="table">
        <thead>
          <tr>
            <th>Referencia</th>
            <th>Paciente</th>
            <th>Especialista</th>
            <th>Fecha / Hora</th>
            <th>Motivo</th>
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
              <td><span class="ref-badge">{{ row.reference_id }}</span></td>
              <td>
                <div class="patient-cell">
                  <span class="patient-name">{{ row.patient?.name }}</span>
                  <span class="patient-sub">{{ row.patient?.email }}</span>
                  <span class="patient-sub">{{ row.patient?.phone }}</span>
                </div>
              </td>
              <td>{{ row.specialist?.name }}</td>
              <td>
                <div class="datetime-cell">
                  <span>{{ formatDate(row.date) }}</span>
                  <span class="patient-sub">{{ row.hour }}</span>
                </div>
              </td>
              <td>
                <span class="reason-text">{{ row.reason || '—' }}</span>
              </td>
              <td>
                <span class="status-badge" :class="'status--' + row.status">
                  {{ statusLabel(row.status) }}
                </span>
              </td>
              <td>
                <div class="apt-actions">
                  <!-- Ver detalle -->
                  <button class="apt-btn" title="Ver detalle" @click="openDetail(row)">
                    <span class="material-symbols-outlined">visibility</span>
                  </button>
                  <!-- Reprogramar -->
                  <button class="apt-btn apt-btn--blue" title="Reprogramar" @click="openPostpone(row)" :disabled="row.status === 'cancelled' || row.status === 'completed'">
                    <span class="material-symbols-outlined">event_upcoming</span>
                  </button>
                  <!-- Confirmar -->
                  <button v-if="row.status === 'pending'" class="apt-btn apt-btn--green" title="Confirmar" @click="quickStatus(row, 'confirmed')">
                    <span class="material-symbols-outlined">check_circle</span>
                  </button>
                  <!-- Completar -->
                  <button v-if="row.status === 'confirmed'" class="apt-btn apt-btn--teal" title="Marcar completada" @click="quickStatus(row, 'completed')">
                    <span class="material-symbols-outlined">task_alt</span>
                  </button>
                  <!-- Recordatorio -->
                  <button class="apt-btn apt-btn--orange" title="Enviar recordatorio" @click="doReminder(row)" :disabled="row.status === 'cancelled' || row.status === 'completed'">
                    <span class="material-symbols-outlined">notifications</span>
                  </button>
                  <!-- Cancelar -->
                  <button v-if="row.status !== 'cancelled' && row.status !== 'completed'" class="apt-btn apt-btn--red" title="Cancelar cita" @click="quickStatus(row, 'cancelled')">
                    <span class="material-symbols-outlined">cancel</span>
                  </button>
                </div>
              </td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="7" class="text-center">No hay citas registradas.</td>
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
                <div class="pagination__meta">Página {{ pagination.current_page }} de {{ pagination.last_page }} · {{ pagination.total }} citas</div>
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- ─── Modal: Detalle de Cita ───────────────────────────────────────── -->
    <BaseModal v-model="openDetailModal" :title="'Cita ' + (detailItem?.reference_id ?? '')" @close="detailItem = null">
      <div v-if="detailItem" class="detail-grid">
        <!-- Paciente -->
        <div class="detail-section">
          <h4 class="detail-section__title"><span class="material-symbols-outlined">person</span> Paciente</h4>
          <div class="detail-row"><span>Nombre</span><strong>{{ detailItem.patient?.name }}</strong></div>
          <div class="detail-row"><span>Email</span><strong>{{ detailItem.patient?.email }}</strong></div>
          <div class="detail-row"><span>Teléfono</span>
            <a :href="'https://wa.me/' + cleanPhone(detailItem.patient?.phone)" target="_blank" class="wa-link">
              <span class="material-symbols-outlined" style="font-size:16px;">chat</span>
              <span class="wa-link-text">
                {{ detailItem.patient?.phone }}
              </span>
            </a>
          </div>
          <div class="detail-row"><span>Nacimiento</span><strong>{{ formatDate(detailItem.patient?.birthday) }}</strong></div>
        </div>

        <!-- Cita -->
        <div class="detail-section">
          <h4 class="detail-section__title"><span class="material-symbols-outlined">calendar_month</span> Cita</h4>
          <div class="detail-row"><span>Especialista</span><strong>{{ detailItem.specialist?.name }}</strong></div>
          <div class="detail-row"><span>Fecha</span><strong>{{ formatDate(detailItem.date) }}</strong></div>
          <div class="detail-row"><span>Hora</span><strong>{{ detailItem.hour }}</strong></div>
          <div class="detail-row"><span>Estado</span>
            <span class="status-badge" :class="'status--' + detailItem.status">{{ statusLabel(detailItem.status) }}</span>
          </div>
          <div v-if="detailItem.reason" class="detail-row detail-row--full"><span>Motivo</span><strong>{{ detailItem.reason }}</strong></div>
        </div>

        <!-- Acciones rápidas de estado -->
        <div class="detail-section detail-section--full">
          <h4 class="detail-section__title"><span class="material-symbols-outlined">tune</span> Cambiar estado</h4>
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button v-for="s in availableStatuses(detailItem.status)" :key="s.value"
              class="button__secondary button--small"
              :style="{ borderColor: s.color, color: s.color }"
              :disabled="loadingAction"
              @click="quickStatus(detailItem, s.value); openDetailModal = false"
            >
              <span class="material-symbols-outlined" style="font-size:16px;">{{ s.icon }}</span>
              {{ s.label }}
            </button>
          </div>
        </div>

        <!-- Logs -->
        <div class="detail-section detail-section--full" v-if="detailItem.logs?.length">
          <h4 class="detail-section__title"><span class="material-symbols-outlined">history</span> Historial</h4>
          <div class="logs-list">
            <div v-for="log in detailItem.logs" :key="log.id" class="log-item">
              <span class="log-dot"></span>
              <div>
                <strong>{{ log.status_label }}</strong>
                <p>{{ log.description }}</p>
                <small>{{ formatDatetime(log.created_at) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openDetailModal = false">Cerrar</button>
        <button class="button__primary" @click="openPostpone(detailItem); openDetailModal = false" :disabled="detailItem?.status === 'cancelled' || detailItem?.status === 'completed'">
          <span class="material-symbols-outlined" style="font-size:16px;">event_upcoming</span>
          Reprogramar
        </button>
      </template>
    </BaseModal>

    <!-- ─── Modal: Reprogramar Cita ──────────────────────────────────────── -->
    <BaseModal v-model="openPostponeModal" title="Reprogramar Cita" @close="resetPostponeForm">
      <div v-if="postponeItem" class="postpone-info">
        <span class="material-symbols-outlined">info</span>
        <span><strong>{{ postponeItem.patient?.name }}</strong> · {{ postponeItem.specialist?.name }} · Actual: {{ formatDate(postponeItem.date) }} {{ postponeItem.hour }}</span>
      </div>

      <div class="grid-2" style="margin-top:16px;">
        <div class="field">
          <label class="field__label">Nueva fecha *</label>
          <input v-model="postponeForm.date" type="date" class="field__input" :min="todayStr" @change="fetchAvailableHours('postpone')" />
        </div>
        <div class="field">
          <label class="field__label">Especialista</label>
          <select v-model="postponeForm.specialist_id" class="field__input" @change="postponeForm.hour = ''; fetchAvailableHours('postpone')">
            <option v-for="s in specialists" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
      </div>

      <div class="field" style="margin-top:8px;">
        <label class="field__label">Hora disponible *</label>
        <div v-if="loadingHours" class="hours-loading">Cargando horarios...</div>
        <div v-else-if="postponeForm.date && hoursForModal.length === 0" class="no-hours-msg">Sin horarios disponibles para esa fecha.</div>
        <div v-else class="hours-grid">
          <button
            v-for="h in hoursForModal"
            :key="h"
            type="button"
            class="hour-btn"
            :class="{ selected: postponeForm.hour === h }"
            @click="postponeForm.hour = h"
          >{{ h }}</button>
        </div>
      </div>

      <div class="field" style="margin-top:8px;">
        <label class="field__label">Nota (opcional)</label>
        <textarea v-model="postponeForm.note" class="field__input" rows="2" placeholder="Motivo del cambio..."></textarea>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openPostponeModal = false">Cancelar</button>
        <button class="button__primary" :disabled="loadingPostpone || !postponeForm.date || !postponeForm.hour" @click="savePostpone">
          {{ loadingPostpone ? 'Guardando...' : 'Confirmar reprogramación' }}
        </button>
      </template>
    </BaseModal>

    <!-- ─── Modal: Nueva Cita (desde admin) ─────────────────────────────── -->
    <BaseModal v-model="openCreateModal" title="Agendar Nueva Cita" @close="resetCreateForm">
      <div class="grid-2">
        <div class="field">
          <label class="field__label">Nombre del paciente *</label>
          <input v-model="createForm.name" type="text" class="field__input" placeholder="Nombre completo" />
          <small v-if="createErrors.name" class="field__error">{{ createErrors.name }}</small>
        </div>
        <div class="field">
          <label class="field__label">Email *</label>
          <input v-model="createForm.email" type="email" class="field__input" placeholder="correo@mail.com" />
          <small v-if="createErrors.email" class="field__error">{{ createErrors.email }}</small>
        </div>
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

      <hr style="margin:16px 0; border:0; border-top:1px solid #eee;" />

      <div class="grid-2">
        <div class="field">
          <label class="field__label">Especialista *</label>
          <select v-model="createForm.specialist_id" class="field__input" @change="createForm.date = ''; createForm.hour = ''; hoursForModal = []">
            <option value="">— Seleccionar —</option>
            <option v-for="s in specialists" :key="s.id" :value="s.id">{{ s.name }} · {{ s.specialty }}</option>
          </select>
          <small v-if="createErrors.specialist_id" class="field__error">{{ createErrors.specialist_id }}</small>
        </div>
        <div class="field">
          <label class="field__label">Fecha *</label>
          <input v-model="createForm.date" type="date" class="field__input" :min="todayStr" :disabled="!createForm.specialist_id" @change="fetchAvailableHours('create')" />
          <small v-if="createErrors.date" class="field__error">{{ createErrors.date }}</small>
        </div>
      </div>

      <div class="field">
        <label class="field__label">Hora disponible *</label>
        <div v-if="loadingHours" class="hours-loading">Cargando horarios...</div>
        <div v-else-if="createForm.date && hoursForModal.length === 0" class="no-hours-msg">Sin horarios disponibles para esa fecha.</div>
        <div v-else-if="!createForm.date" class="no-hours-msg">Selecciona una fecha primero.</div>
        <div v-else class="hours-grid">
          <button
            v-for="h in hoursForModal"
            :key="h"
            type="button"
            class="hour-btn"
            :class="{ selected: createForm.hour === h }"
            @click="createForm.hour = h"
          >{{ h }}</button>
        </div>
        <small v-if="createErrors.hour" class="field__error">{{ createErrors.hour }}</small>
      </div>

      <div class="field">
        <label class="field__label">Estado inicial</label>
        <select v-model="createForm.status" class="field__input">
          <option value="pending">Pendiente</option>
          <option value="confirmed">Confirmada</option>
        </select>
      </div>

      <div class="field">
        <label class="field__label">Motivo de la consulta</label>
        <textarea v-model="createForm.reason" class="field__input" rows="2" placeholder="Describe brevemente..."></textarea>
      </div>

      <template #footer>
        <button class="button__secondary" @click="openCreateModal = false">Cancelar</button>
        <button class="button__primary" :disabled="loadingCreate" @click="saveCreate">
          {{ loadingCreate ? 'Guardando...' : 'Registrar cita' }}
        </button>
      </template>
    </BaseModal>

    <!-- ─── Modal: Recordatorio ──────────────────────────────────────────── -->
    <BaseModal v-model="openReminderModal" title="Recordatorio registrado" @close="reminderItem = null">
      <div v-if="reminderItem" class="reminder-body">
        <div class="reminder-check">
          <span class="material-symbols-outlined">check_circle</span>
          <p>El recordatorio quedó registrado en el historial de la cita.</p>
        </div>
        <div class="detail-section" style="margin-top:16px;">
          <h4 class="detail-section__title">Contactar al paciente</h4>
          <div class="detail-row"><span>Nombre</span><strong>{{ reminderItem.patient?.name }}</strong></div>
          <div class="detail-row"><span>Cita</span><strong>{{ formatDate(reminderItem.date) }} {{ reminderItem.hour }}</strong></div>
          <div class="detail-row"><span>Especialista</span><strong>{{ reminderItem.specialist?.name }}</strong></div>
        </div>
        <div class="reminder-contacts">
          <a :href="'https://wa.me/' + cleanPhone(reminderItem.patient?.phone)" target="_blank" class="button__primary">
            <span class="material-symbols-outlined">chat</span> WhatsApp
          </a>
          <a :href="'mailto:' + reminderItem.patient?.email + '?subject=Recordatorio de cita&body=Estimado/a ' + reminderItem.patient?.name + ', le recordamos su cita el ' + formatDate(reminderItem.date) + ' a las ' + reminderItem.hour + ' con ' + reminderItem.specialist?.name + '.'" class="button__secondary">
            <span class="material-symbols-outlined">mail</span> Email
          </a>
        </div>
      </div>
      <template #footer>
        <button class="button__secondary" @click="openReminderModal = false">Cerrar</button>
      </template>
    </BaseModal>

  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2'
import BaseModal from '../Common/BaseModal.vue'

export default {
  name: 'AppointmentsIndex',
  components: { BaseModal },
  data() {
    return {
      // ── List ──
      dataList: [],
      pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
      filters: { q: '', status: '', date: '', per_page: 10 },
      loadingList: false,

      // ── Specialists (for forms) ──
      specialists: [],

      // ── Detail modal ──
      openDetailModal: false,
      detailItem: null,

      // ── Postpone modal ──
      openPostponeModal: false,
      postponeItem: null,
      postponeForm: { specialist_id: null, date: '', hour: '', note: '' },
      loadingPostpone: false,

      // ── Create modal ──
      openCreateModal: false,
      createForm: this.emptyCreateForm(),
      createErrors: {},
      loadingCreate: false,

      // ── Reminder modal ──
      openReminderModal: false,
      reminderItem: null,

      // ── Shared ──
      hoursForModal: [],
      loadingHours: false,
      loadingAction: false,
      todayStr: new Date().toISOString().split('T')[0],
    }
  },
  computed: {
    pagesToShow() {
      const total = this.pagination.last_page || 1
      const current = this.pagination.current_page || 1
      const delta = 3
      const start = Math.max(1, current - delta)
      const end = Math.min(total, current + delta)
      const pages = []
      for (let i = start; i <= end; i++) pages.push(i)
      return pages
    },
  },
  mounted() {
    this.fetchData()
    this.fetchSpecialists()
  },
  methods: {
    emptyCreateForm() {
      return { name: '', email: '', phone: '', birthday: '', specialist_id: '', date: '', hour: '', reason: '', status: 'pending' }
    },

    // ── Data fetching ─────────────────────────────────────────────────────
    async fetchData(page = 1) {
      this.loadingList = true
      try {
        const { data } = await axios.get('/api/appointments', { params: { page, ...this.filters } })
        this.dataList = data.data || data
        if (data.current_page) {
          this.pagination = { current_page: data.current_page, last_page: data.last_page, total: data.total }
        }
      } catch {
        this.showError('No se pudieron cargar las citas.')
      } finally {
        this.loadingList = false
      }
    },

    async fetchSpecialists() {
      try {
        const { data } = await axios.get('/api/specialists')
        this.specialists = data.data || data
      } catch { /* silent */ }
    },

    async fetchAvailableHours(context) {
      const specialistId = context === 'postpone' ? this.postponeForm.specialist_id : this.createForm.specialist_id
      const date = context === 'postpone' ? this.postponeForm.date : this.createForm.date
      if (!specialistId || !date) return

      this.loadingHours = true
      this.hoursForModal = []
      try {
        const { data } = await axios.get('/api/appointments/available-slots', {
          params: { specialist_id: specialistId, date }
        })
        this.hoursForModal = data.available_hours ?? []
      } catch {
        this.showError('Error al cargar horarios disponibles.')
      } finally {
        this.loadingHours = false
      }
    },

    // ── Filters & pagination ──────────────────────────────────────────────
    applyFilters() { this.fetchData(1) },
    resetFilters() {
      this.filters = { q: '', status: '', date: '', per_page: 10 }
      this.fetchData(1)
    },
    goToPage(page) {
      if (page >= 1 && page <= this.pagination.last_page) this.fetchData(page)
    },

    // ── Detail ────────────────────────────────────────────────────────────
    async openDetail(row) {
      try {
        const { data } = await axios.get(`/api/appointments?q=${row.reference_id}`)
        const found = (data.data || data).find(a => a.id === row.id)
        // If logs aren't loaded, reload with a targeted endpoint or just use the row
        this.detailItem = found || row
        // Load logs by checking status endpoint
        const res = await axios.get('/api/appointments/status', { params: { search: row.reference_id } })
        this.detailItem = res.data
      } catch {
        this.detailItem = row
      }
      this.openDetailModal = true
    },

    // ── Postpone ──────────────────────────────────────────────────────────
    openPostpone(row) {
      this.postponeItem = row
      this.postponeForm = { specialist_id: row.specialist_id, date: '', hour: '', note: '' }
      this.hoursForModal = []
      this.openPostponeModal = true
    },
    resetPostponeForm() {
      this.postponeItem = null
      this.postponeForm = { specialist_id: null, date: '', hour: '', note: '' }
      this.hoursForModal = []
    },
    async savePostpone() {
      if (!this.postponeForm.date || !this.postponeForm.hour) return
      this.loadingPostpone = true
      try {
        await axios.put(`/api/appointments/${this.postponeItem.id}`, {
          date: this.postponeForm.date,
          hour: this.postponeForm.hour,
          reason: this.postponeForm.note || undefined,
        })
        Swal.fire({ icon: 'success', title: 'Cita reprogramada', timer: 1500, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
        this.openPostponeModal = false
        this.fetchData(this.pagination.current_page)
      } catch (e) {
        this.showError(e.response?.data?.message || 'Error al reprogramar la cita.')
      } finally {
        this.loadingPostpone = false
      }
    },

    // ── Create ────────────────────────────────────────────────────────────
    openCreate() {
      this.createForm = this.emptyCreateForm()
      this.createErrors = {}
      this.hoursForModal = []
      this.openCreateModal = true
    },
    resetCreateForm() {
      this.createForm = this.emptyCreateForm()
      this.createErrors = {}
      this.hoursForModal = []
    },
    async saveCreate() {
      this.loadingCreate = true
      this.createErrors = {}
      try {
        await axios.post('/api/appointments', this.createForm)
        // If admin wants "confirmed" directly, update status
        // (store always creates as pending; we patch immediately if needed)
        Swal.fire({ icon: 'success', title: 'Cita registrada', timer: 1800, showConfirmButton: false, customClass: { container: 'swal-high-z' } })
        this.openCreateModal = false
        this.fetchData(1)
      } catch (e) {
        if (e.response?.status === 422) {
          const errs = e.response.data.errors || {}
          this.createErrors = Object.fromEntries(Object.entries(errs).map(([k, v]) => [k, v[0]]))
        } else {
          this.showError(e.response?.data?.message || 'Error al registrar la cita.')
        }
      } finally {
        this.loadingCreate = false
      }
    },

    // ── Quick status change ───────────────────────────────────────────────
    async quickStatus(row, status) {
      const confirmMessages = {
        confirmed: { title: '¿Confirmar esta cita?', confirmText: 'Sí, confirmar' },
        cancelled:  { title: '¿Cancelar esta cita?', confirmText: 'Sí, cancelar' },
        completed:  { title: '¿Marcar como completada?', confirmText: 'Sí, completar' },
        pending:    { title: '¿Volver a pendiente?', confirmText: 'Sí' },
      }
      const msg = confirmMessages[status] || { title: '¿Cambiar estado?', confirmText: 'Sí' }

      const result = await Swal.fire({
        title: msg.title,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: msg.confirmText,
        cancelButtonText: 'No',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      })
      if (!result.isConfirmed) return

      this.loadingAction = true
      try {
        await axios.put(`/api/appointments/${row.id}`, { status })
        this.fetchData(this.pagination.current_page)
      } catch (e) {
        this.showError(e.response?.data?.message || 'Error al actualizar el estado.')
      } finally {
        this.loadingAction = false
      }
    },

    // ── Reminder ─────────────────────────────────────────────────────────
    async doReminder(row) {
      try {
        const { data } = await axios.post(`/api/appointments/${row.id}/reminder`)
        this.reminderItem = data.appointment
        this.openReminderModal = true
        this.fetchData(this.pagination.current_page)
      } catch (e) {
        this.showError('Error al registrar el recordatorio.')
      }
    },

    // ── Helpers ──────────────────────────────────────────────────────────
    statusLabel(s) {
      return { pending: 'Pendiente', confirmed: 'Confirmada', completed: 'Completada', cancelled: 'Cancelada' }[s] ?? s
    },
    availableStatuses(current) {
      const all = [
        { value: 'pending',   label: 'Pendiente',  icon: 'schedule',     color: '#e65100' },
        { value: 'confirmed', label: 'Confirmar',  icon: 'check_circle', color: '#fff' },
        { value: 'completed', label: 'Completar',  icon: 'task_alt',     color: '#124c14' },
        { value: 'cancelled', label: 'Cancelar',   icon: 'cancel',       color: '#f44336' },
      ]
      return all.filter(s => s.value !== current)
    },
    formatDate(d) {
      if (!d) return '—'
      const [y, m, day] = String(d).substring(0, 10).split('-')
      return `${day}/${m}/${y}`
    },
    formatDatetime(dt) {
      if (!dt) return ''
      const d = new Date(dt)
      return d.toLocaleString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    },
    cleanPhone(phone) {
      return phone ? String(phone).replace(/\D/g, '') : ''
    },
    showError(msg) {
      Swal.fire({ icon: 'error', title: 'Error', text: msg, customClass: { container: 'swal-high-z' } })
    },
  },
}
</script>

<style scoped>
/* ── Status badges ─────────────────────────────────────────────────── */
.status-badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.status--pending   { background: #fff3e0; color: #e65100; }
.status--confirmed { background: #e3f2fd; color: #1565c0; }
.status--completed { background: #e8f5e9; color: #2e7d32; }
.status--cancelled { background: #fce4ec; color: #b71c1c; }

/* ── Table cells ───────────────────────────────────────────────────── */
.ref-badge { font-family: monospace; font-size: 12px; background: #f4f4f4; padding: 2px 6px; border-radius: 4px; }
.patient-cell { display: flex; flex-direction: column; gap: 2px; }
.patient-name { font-weight: 600; font-size: 0.9rem; }
.patient-sub { font-size: 0.78rem; color: #777; }
.datetime-cell { display: flex; flex-direction: column; gap: 2px; font-size: 0.88rem; }
.reason-text { font-size: 0.82rem; color: #555; max-width: 140px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Action buttons per row ────────────────────────────────────────── */
.apt-actions { display: flex; gap: 4px; align-items: center; flex-wrap: wrap; }
.apt-btn { border: none; background: #f4f4f4; border-radius: 6px; cursor: pointer; padding: 4px 6px; display: flex; align-items: center; transition: background 0.15s; }
.apt-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.apt-btn span { font-size: 18px; color: #555; }
.apt-btn:not(:disabled):hover { background: #e0e0e0; }
.apt-btn--green span  { color: #2e7d32; }
.apt-btn--teal span   { color: #00695c; }
.apt-btn--blue span   { color: #1565c0; }
.apt-btn--orange span { color: #e65100; }
.apt-btn--red span    { color: #b71c1c; }

/* ── Detail modal ──────────────────────────────────────────────────── */
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.detail-section { background: #f8f9fa; border-radius: 8px; padding: 14px; }
.detail-section--full { grid-column: 1 / -1; }
.detail-section__title { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: #444; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 0.04em; }
.detail-section__title span { font-size: 18px; }
.detail-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.88rem; padding: 4px 0; border-bottom: 1px solid #eee; gap: 8px; }
.detail-row:last-child { border-bottom: none; }
.detail-row span:first-child { color: #777; min-width: 80px; }
.detail-row--full { flex-direction: column; align-items: flex-start; gap: 4px; }
.wa-link { display: flex; align-items: center; gap: 4px; color: #25d366; font-weight: 600; text-decoration: none; }
.wa-link:hover .wa-link-text { text-decoration: underline; }

/* ── Logs ──────────────────────────────────────────────────────────── */
.logs-list { display: flex; flex-direction: column; gap: 0; }
.log-item { display: flex; gap: 12px; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 0.85rem; }
.log-item:last-child { border-bottom: none; }
.log-dot { width: 8px; height: 8px; border-radius: 50%; background: #2196f3; margin-top: 5px; flex-shrink: 0; }
.log-item strong { display: block; font-size: 0.88rem; }
.log-item p { margin: 2px 0; color: #555; }
.log-item small { color: #aaa; }

/* ── Postpone / Create modal helpers ───────────────────────────────── */
.postpone-info { display: flex; align-items: center; gap: 8px; background: #e3f2fd; border-radius: 8px; padding: 10px 14px; font-size: 0.88rem; color: #1565c0; }
.postpone-info span { font-size: 18px; }
.hours-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
.hour-btn { padding: 6px 14px; border-radius: 6px; border: 1px solid #ddd; background: white; cursor: pointer; font-size: 0.88rem; transition: all 0.15s; }
.hour-btn:hover { border-color: var(--color-primary, #0ca678); color: var(--color-primary, #0ca678); }
.hour-btn.selected { background: var(--color-primary, #0ca678); color: white; border-color: var(--color-primary, #0ca678); }
.hours-loading { font-size: 0.85rem; color: #888; padding: 8px 0; }
.no-hours-msg { font-size: 0.85rem; color: #f44336; padding: 8px 0; }

/* ── Reminder modal ────────────────────────────────────────────────── */
.reminder-check { display: flex; align-items: center; gap: 10px; color: #2e7d32; font-size: 0.95rem; }
.reminder-check span { font-size: 28px; }
.reminder-contacts { display: flex; gap: 12px; margin-top: 16px; }
.reminder-contacts a { display: flex; align-items: center; gap: 6px; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 0.9rem; }

/* ── Misc ──────────────────────────────────────────────────────────── */
.text-center { text-align: center; padding: 24px; color: #888; }
.field__error { color: #f44336; font-size: 0.8rem; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media (max-width: 600px) {
  .detail-grid, .grid-2 { grid-template-columns: 1fr; }
}
</style>
