<template>
  <div>
    <div class="table__header">
      <div class="table__header--header">
        <div class="table__header--header-content">
          <h3 class="table__header--header-title">Citas</h3>
          <p class="table__header--header-description">
            Administra las citas.
          </p>
        </div>
        <div class="table__header--header-button">
          <!-- <button type="button" class="button__primary" @click="openCreate">
            <span class="material-symbols-outlined">medical_services</span>
            Nuevo Especialista
          </button> -->
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
      model="appointment"
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

  </div>
</template>

<script>
import axios from 'axios'
import Swal from 'sweetalert2' 
import BaseModal from '../Common/BaseModal.vue'
import DataTable from '../Common/DataTable.vue'

export default {
  name: 'AppointmentsIndex',
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
      photoPreview: null,
      columns: [
        { label: 'Referencia', field: 'reference_id' },
        { label: 'Paciente', field: 'patient_name',  type: 'slot'  }, // Acceso anidado
        { 
          label: 'Fecha/Hora', 
          field: 'date', 
          formatter: (value) => value ? new Date(value).toLocaleString('es-VE')  : '' 
        },
        { label: 'Teléfono', field: 'patient_phone',  type: 'slot'  },
        { label: 'Especialista', field: 'specialist_name',  type: 'slot'  },
        { label: 'Estado', field: 'status', formatter: (value) => {
          const statusMap = { 'pending': 'Pendiente', 'confirmed': 'Confirmada', 'cancelled': 'Cancelada', 'completed': 'Completada' }
          return statusMap[value] || value
        }, type: 'slot' },
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
    async fetchData(page = 1) {
      this.loadingList = true
      try {
        const { data } = await axios.get('/api/appointments', {
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
    applyFilters() {
      // al filtrar vuelves a página 1
      this.fetchData(1)
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
      this.photoPreview = null // Limpiar el preview al cerrar
      this.errors = {}
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

/* Horarios */
.schedule-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: 10px;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}
.text-danger { color: #fa5252; }
</style>