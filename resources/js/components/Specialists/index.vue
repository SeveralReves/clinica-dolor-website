<template>
  <div>
    <div class="table__header">
      <div class="table__header--header">
        <div class="table__header--header-content">
          <h3 class="table__header--header-title">Médicos Especialistas</h3>
          <p class="table__header--header-description">
            Administra el staff médico de la clínica, sus especialidades, datos de contacto y estados de actividad.
          </p>
        </div>
        <div class="table__header--header-button">
          <button type="button" class="button__primary" @click="openCreate">
            <span class="material-symbols-outlined">medical_services</span>
            Nuevo Especialista
          </button>
        </div>
      </div>
      
      <div class="table__header--body" style="display:flex; gap: 10px; align-items:center; flex-wrap: wrap;">
        <input
          v-model.trim="filters.q"
          class="field__input"
          style="max-width: 260px;"
          placeholder="Buscar por nombre o especialidad..."
          @keyup.enter="applyFilters"
        />

        <select v-model="filters.status" class="field__input" style="max-width: 200px;" @change="applyFilters">
          <option value="">Todos los estados</option>
          <option value="1">Activos</option>
          <option value="0">Inactivos</option>
        </select>

        <button class="button__secondary button--medium" @click="applyFilters">Filtrar</button>
        <button class="button__secondary button--medium" @click="resetFilters">Limpiar</button>

        <div style="margin-left: auto; display: flex; gap: 10px;">
          <div class="table__header--sort">
            <button class="button__ghost button--small" @click="sortView = !sortView">
              <span class="material-symbols-outlined">sort</span>
            </button>
            <div v-if="sortView" class="table__header--sort--options">
              <button class="button__ghost button--small" @click="sortData('asc')">ASC</button>
              <button class="button__ghost button--small" @click="sortData('desc')">DESC</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <data-table 
      :columns="columns" 
      :data="dataList" 
      :pagination="pagination" 
      :loading="loadingList" 
      @edit="openEdit" 
      @schedules="openSchedules" 
      @delete="openDelete" 
      @page-change="goToPage"
      model="specialist"
    >
    </data-table>

    <BaseModal
      v-model="openCreateModal"
      :title="isEdit ? 'Editar Especialista' : 'Nuevo Especialista'"
      @close="resetForm"
    >
      <form @submit.prevent="save" class="table-form" enctype="multipart/form-data">
        
        <div class="photo-preview-container">
          <div class="photo-preview">
            <img v-if="photoPreview" :src="photoPreview" />
            <img v-else-if="form.photo_path" :src="'/storage/' + form.photo_path" />
            <div v-else class="photo-placeholder">
              <span class="material-symbols-outlined">person</span>
            </div>
          </div>
          <div class="field">
            <label class="field__label">Foto de Perfil</label>
            <input type="file" @change="handleFileUpload" class="field__input" accept="image/*" />
          </div>
        </div>

        <div class="grid-2">
          <div class="field">
            <label class="field__label">Nombre Completo *</label>
            <input v-model="form.name" type="text" class="field__input" placeholder="Ej: Dra. Carmen Carrillo" />
            <small v-if="errors.name" class="field__error">{{ errors.name }}</small>
          </div>
          <div class="field">
            <label class="field__label">Especialidad *</label>
            <input v-model="form.specialty" type="text" class="field__input" placeholder="Ej: Anestesiólogo" />
            <small v-if="errors.specialty" class="field__error">{{ errors.specialty }}</small>
          </div>
        </div>

        <div class="grid-2">
          <div class="field">
            <label class="field__label">Email *</label>
            <input v-model="form.email" type="email" class="field__input" />
            <small v-if="errors.email" class="field__error">{{ errors.email }}</small>
          </div>
          <div class="field">
            <label class="field__label">Teléfono</label>
            <input v-model="form.phone" type="text" class="field__input" placeholder="+58..." />
          </div>
        </div>

        <div class="field">
          <label class="field__label">Descripción / Biografía</label>
          <textarea v-model="form.description" class="field__input" rows="3"></textarea>
        </div>

        <div class="grid-2">
          <div class="field">
            <label class="field__label">Estado</label>
            <select v-model="form.is_active" class="field__input">
              <option :value="true">Activo (Visible en Landing)</option>
              <option :value="false">Inactivo</option>
            </select>
          </div>
        </div>

        <!-- <hr v-if="!isEdit" style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
        <p v-if="!isEdit" class="text-muted">Si el especialista requiere acceso al sistema, define sus credenciales:</p>

        <div class="grid-2">
          <div class="field">
            <label class="field__label">Rol de Sistema</label>
            <select v-model="form.role" class="field__input">
              <option value="">Sin acceso al sistema</option>
              <option value="doctor">Doctor</option>
              <option value="assist">Asistente</option>
              <option value="admin">Administrador</option>
            </select>
          </div>
          <div class="field" v-if="form.role">
            <label class="field__label">Contraseña</label>
            <input v-model="form.password" type="password" class="field__input" />
          </div>
        </div> -->
      </form>

      <template #footer>
        <button class="button__secondary" @click="openCreateModal = false">Cancelar</button>
        <button class="button__primary" :disabled="loadingSave" @click="save">
          {{ loadingSave ? 'Guardando...' : (isEdit ? 'Actualizar' : 'Guardar') }}
        </button>
      </template>
    </BaseModal>

    <BaseModal v-model="openSchedulesModal" :title="'Horarios: ' + currentSpecialist?.name" @close="closeSchedules">
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
          <button class="button__ghost text-danger" @click="removeScheduleRow(index)">
            <span class="material-symbols-outlined">delete</span>
          </button>
        </div>

        <button class="button__secondary button--small" @click="addScheduleRow" style="margin-top: 10px;">
          <span class="material-symbols-outlined">add</span>
          <span>Añadir Turno</span>
        </button>
      </div>

      <!-- TAB 2: Fechas especiales (bloqueos / horario custom por fecha) -->
      <div v-if="scheduleTab === 'custom'" class="schedules-container">
        <!-- Formulario para agregar -->
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

        <!-- Listado de fechas especiales futuras -->
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
          {{ loadingSchedules ? 'Guardando...' : 'Guardar Horarios' }}
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2' 
import BaseModal from '../Common/BaseModal.vue'
import DataTable from '../Common/DataTable.vue'

export default {
  name: 'SpecialistIndex',
  components: { BaseModal, DataTable },
  data() {
    return {
      openCreateModal: false,
      isEdit: false,
      editingId: null,
      loadingSave: false,
      loadingList: false,
      form: this.emptyForm(),
      errors: {},
      dataList: [],
      pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
      filters: { q: '', status: '', per_page: 10, order: '' },
      openSchedulesModal: false,
      loadingSchedules: false,
      currentSpecialist: null,
      scheduleForm: [],
      scheduleTab: 'regular',
      customAvailabilities: [],
      customForm: { date: '', is_available: false, start_time: '08:00', end_time: '12:00' },
      loadingCustom: false,
      todayStr: new Date().toISOString().split('T')[0],
      photoPreview: null,
      columns: [
        { label: 'Foto', field: 'photo_path', type: 'slot' },
        { label: 'Nombre', field: 'name' },
        { label: 'Especialidad', field: 'specialty' },
        { label: 'Email', field: 'email' },
        { label: 'Teléfono', field: 'phone' },
        { label: 'Estado', field: 'is_active', type: 'slot' },
        { label: 'Acciones', field: 'actions' },
      ],
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    emptyForm() {
      return {
        name: '',
        specialty: '',
        email: '',
        phone: '',
        description: '',
        is_active: true,
        role: '',
        password: '',
        photo: null // Para el archivo binario
      }
    },
    applyFilters() {
      // al filtrar vuelves a página 1
      this.fetchData(1)
    },
    async fetchData(page = 1) {
      this.loadingList = true
      try {
        const { data } = await axios.get('/api/specialists', {
          params: { page, ...this.filters }
        })
        this.dataList = data.data || data
        // Ajustar según si tu API devuelve paginación pura o el objeto data
        if (data.current_page) {
           this.pagination = {
             current_page: data.current_page,
             last_page: data.last_page,
             total: data.total
           }
        }
      } catch (error) {
        this.showError('No se pudieron cargar los especialistas.')
      } finally {
        this.loadingList = false
      }
    },
    openCreate() {
      this.isEdit = false; this.editingId = null; this.errors = {};
      this.form = this.emptyForm(); this.openCreateModal = true;
    },
    openEdit(item) {
      this.isEdit = true; this.editingId = item.id; this.errors = {};
      this.form = { ...item, is_active: item.is_active == '1' ? true : false, password: '', photo: null };
      this.openCreateModal = true;
    },
    async save() {
      this.loadingSave = true;
      this.errors = {};

      // Usamos FormData para soportar la subida de imagen
      const formData = new FormData();
      Object.keys(this.form).forEach(key => {
        if (this.form[key] !== null && this.form[key] !== '') {
          // Convertir booleanos a 1 o 0 para PHP
          if(key !== 'schedules') {
            const value = typeof this.form[key] === 'boolean' ? (this.form[key] ? 1 : 0) : this.form[key];
            formData.append(key, value);
          }
        }
      });

      // Si es un PUT, Laravel a veces tiene problemas con FormData directo. 
      // Truco: Usar POST y añadir _method: PUT
      if (this.isEdit) formData.append('_method', 'PUT');

      try {
        const url = this.isEdit ? `/api/specialists/${this.editingId}` : '/api/specialists';
        // Si usamos _method PUT, enviamos por POST
        await axios.post(url, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        Swal.fire({ icon: 'success', title: 'Guardado', showConfirmButton: false, timer: 1500 });
        this.openCreateModal = false;
        this.fetchData(this.pagination.current_page);
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = Object.fromEntries(Object.entries(error.response.data.errors).map(([k, v]) => [k, v[0]]));
        } else {
          this.showError('Ocurrió un error al guardar.');
        }
      } finally {
        this.loadingSave = false;
      }
    },
    showError(msg) {
      Swal.fire({ icon: 'error', title: 'Error', text: msg });
    },
    handleFileUpload(event) {
      const file = event.target.files[0]
      if (!file) return
      
      this.form.photo = file
      // Crear URL temporal para el preview
      this.photoPreview = URL.createObjectURL(file)
    },

    // --- Lógica de Horarios ---
    openSchedules(specialist) {
      this.currentSpecialist = specialist
      // Clonamos los horarios existentes o empezamos con uno vacío
      this.scheduleForm = specialist.schedules.length > 0 
        ? JSON.parse(JSON.stringify(specialist.schedules)) 
        : [{ day: 'monday', start_time: '08:00', end_time: '12:00' }]
      
      this.openSchedulesModal = true
    },

    addScheduleRow() {
      this.scheduleForm.push({ day: 'monday', start_time: '08:00', end_time: '12:00' })
    },

    removeScheduleRow(index) {
      this.scheduleForm.splice(index, 1)
    },

    openDelete(data) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: `Confirma que deseas eliminar el especialista "${data.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            await axios.delete(`/api/specialists/${data.id}`)
            Swal.fire({
              icon: 'success',
              title: 'Eliminado',
              text: 'El especialista ha sido eliminado.',
              timer: 2000,
              showConfirmButton: false,
              customClass: { container: 'swal-high-z', confirmButton: 'button__primary', },
            })
            this.fetchData(this.pagination.current_page)
          } catch (error) {
            console.error(error)
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: error.response?.data?.message || 'No se pudo eliminar el especialista.',
              customClass: { container: 'swal-high-z', confirmButton: 'button__primary', },
            })
          }
        }
      })
    },
    async saveSchedules() {
      this.loadingSchedules = true
      try {
        // En tu controlador API, el update debe manejar la llave 'schedules'
        await axios.put(`/api/specialists/${this.currentSpecialist.id}`, {
          schedules: this.scheduleForm
        })
        
        Swal.fire({ icon: 'success', title: 'Horarios actualizados', timer: 1500, showConfirmButton: false })
        this.openSchedulesModal = false
        this.fetchData(this.pagination.current_page)
      } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error al guardar horarios' })
      } finally {
        this.loadingSchedules = false
      }
    },

    resetForm() {
      this.form = this.emptyForm()
      this.photoPreview = null
      this.errors = {}
    },

    closeSchedules() {
      this.openSchedulesModal = false
      this.scheduleTab = 'regular'
      this.customAvailabilities = []
      this.customForm = { date: '', is_available: false, start_time: '08:00', end_time: '12:00' }
    },

    // ── Custom Availabilities ──────────────────────────────────────
    async fetchCustomAvailabilities() {
      if (!this.currentSpecialist) return
      this.loadingCustom = true
      try {
        const { data } = await axios.get(`/api/specialists/${this.currentSpecialist.id}/custom-availabilities`)
        this.customAvailabilities = data
      } catch (e) {
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
          `/api/specialists/${this.currentSpecialist.id}/custom-availabilities`,
          this.customForm
        )
        this.customForm = { date: '', is_available: false, start_time: '08:00', end_time: '12:00' }
        await this.fetchCustomAvailabilities()
        Swal.fire({ icon: 'success', title: 'Fecha guardada', timer: 1500, showConfirmButton: false })
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al guardar la fecha especial.'
        this.showError(msg)
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
        await axios.delete(`/api/specialists/${this.currentSpecialist.id}/custom-availabilities/${id}`)
        await this.fetchCustomAvailabilities()
      } catch (e) {
        this.showError('No se pudo eliminar.')
      }
    },

    formatDate(dateStr) {
      if (!dateStr) return ''
      const [y, m, d] = String(dateStr).substring(0, 10).split('-')
      return `${d}/${m}/${y}`
    },

    sortData(order) {
      this.filters.order = order
      this.sortView = false
      this.fetchData(1)
    },

    resetFilters() {
      this.filters = { q: '', status: '', per_page: 10, order: '' }
      this.fetchData(1)
    },
  }
}
</script>

<style scoped>
.table-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
.table-avatar-placeholder { 
  width: 40px; height: 40px; border-radius: 50%; 
  background: #eee; display: flex; align-items: center; justify-content: center;
  font-weight: bold; color: #888;
}
.badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge--success { background: #e6fcf5; color: #0ca678; }
.badge--danger { background: #fff5f5; color: #fa5252; }
.text-muted { font-size: 0.85rem; color: #666; margin-bottom: 1rem; }
.photo-preview-container {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
  background: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
}
.photo-preview, .photo-placeholder {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
}
.photo-preview img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder span { font-size: 40px; color: #ccc; }

/* Tabs */
.schedule-tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 16px;
  border-bottom: 2px solid #eee;
  padding-bottom: 0;
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

/* Horarios semanales */
.schedule-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: 10px;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

/* Custom availability */
.custom-form { background: #f8f9fa; padding: 12px; border-radius: 8px; }
.custom-form__row {
  display: grid;
  grid-template-columns: 1fr 1fr;
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
.custom-row__info { color: #555; }

.text-danger { color: #fa5252; }
</style>