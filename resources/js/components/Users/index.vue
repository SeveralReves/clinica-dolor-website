<template>
  <div>
    <div class="table__header">
      <div class="table__header--header">
        <div class="table__header--header-content">
          <h3 class="table__header--header-title">
              Usuarios
          </h3>
          <p class="table__header--header-description">
            Administrar el acceso de usuarios, roles y permisos.
          </p>
        </div>
          <div class="table__header--header-button">
            <button type="button" class="button__primary" @click="openCreate">
              <span class="material-symbols-outlined">add</span>
              Nuevo
            </button>
          </div>
      </div>
      <div class="table__header--body" style="display:flex; gap: 10px; align-items:center; flex-wrap: wrap;">
        <input
          v-model.trim="filters.q"
          class="field__input"
          style="max-width: 260px;"
          placeholder="Buscar por título..."
          @keyup.enter="applyFilters"
        />

        <!-- <select v-model="filters.status" class="field__input" style="max-width: 200px;">
          <option value="">Todos</option>
          <option value="draft">Borrador</option>
          <option value="active">Activa</option>
          <option value="paused">Pausada</option>
          <option value="finished">Finalizada</option>
        </select> -->

        <select v-model.number="filters.per_page" class="field__input" style="max-width: 140px;">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
        <button class="button__secondary" @click="applyFilters">Filtrar</button>
        <button class="button__secondary" @click="resetFilters">Limpiar</button>

        <div style="margin-left: auto; display: flex; gap: 10px;">
          <!-- <button class="button__ghost button--small" @click="exportData">
            <span class="material-symbols-outlined">file_download</span>
          </button> -->
          <div class="table__header--sort">
            <button class="button__ghost button--small" @click="sortView = !sortView">
              <span class="material-symbols-outlined">sort</span>
            </button>
            <div v-if="sortView" class="table__header--sort--options">
              <button class="button__ghost button--small" @click="sortData('asc')">
                <span class="material-symbols-outlined">sort_by_alpha</span>
              </button>
              <button class="button__ghost button--small" @click="sortData('desc')">
                <span class="material-symbols-outlined" style="transform: rotate(180deg);">sort_by_alpha</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Listado -->
    <data-table 
      :columns="columns" 
      :data="dataList" 
      :pagination="pagination" 
      :loading="loadingList" 
      @edit="openEdit" 
      @delete="openDelete" 
      @page-change="goToPage"
      model="user"/>

    <!-- Modales (crear/editar) -->
    <BaseModal
      v-model="openCreateModal"
      :title="isEdit ? 'Editar Usuario' : 'Nuevo Usuario'"
      @close="resetForm"
    >
      <form @submit.prevent="save" class="table-form">
        <!-- Precio + Moneda -->
        <div class="grid-2">
          <div class="field">
            <label class="field__label">Nombre *</label>
            <input
              v-model="form.name"
              type="text"
              class="field__input"
              placeholder="Ej: Juan Pérez"
            />
            <small v-if="errors.name" class="field__error">{{ errors.name }}</small>
          </div>
          <div class="field">
            <label class="field__label">Email *</label>
            <input
              v-model="form.email"
              type="email"
              class="field__input"
              placeholder="Ej: email@mail.com"
            />
            <small v-if="errors.email" class="field__error">{{ errors.email }}</small>
          </div>
        </div>

        <div class="grid-2">
          <!-- Status -->
          <div class="field">
            <label class="field__label">Rol *</label>
            <select v-model="form.role" :disabled="form.role == 'superadmin'" class="field__input">
              <option value="">Selecciona un rol</option>
              <option value="admin">Administrador</option>
              <option value="assist">Asistente</option>
              <option value="doctor">Doctor</option>
              <option value="superadmin" disabled>Superadministrador</option>
            </select>
          </div>

          <div class="field">
            <label class="field__label">Contraseña {{ isEdit ? '(vacío para no cambiar)' : '*' }}</label>
            <div style="position: relative;">
              <span 
                class="material-symbols-outlined" 
                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"
                @click="viewPassword = !viewPassword"
              >
                {{ viewPassword ? 'visibility_off' : 'visibility' }}
              </span>
              <input
                v-model="form.password"
                :type="viewPassword ? 'text' : 'password'"
                class="field__input"
                placeholder="Mínimo 6 caracteres"
              />
            </div>
            <small v-if="errors.password" class="field__error">{{ errors.password }}</small>
          </div>
        </div>
        <div class="grid-2">
          <div class="field">
            <label class="field__label">Repetir Contraseña {{ isEdit ? '(vacío para no cambiar)' : '*' }}</label>
            <div style="position: relative;">
              <span 
                class="material-symbols-outlined" 
                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"
                @click="viewPassword = !viewPassword"
              >
                {{ viewPassword ? 'visibility_off' : 'visibility' }}
              </span>
              <input
                v-model="form.password_confirmation"
                :type="viewPassword ? 'text' : 'password'"
                class="field__input"
                placeholder="Mínimo 6 caracteres"
              />
            </div>
            <small v-if="errors.password_confirmation" class="field__error">{{ errors.password_confirmation }}</small>
          </div>
        </div>


      </form>

      <template #footer>
        <button class="button__secondary" @click="openCreateModal = false; resetForm()">
          Cancelar
        </button>

        <button class="button__primary" @click="save">
          {{ isEdit ? 'Actualizar' : 'Guardar' }}
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
  name: 'usersIndex',
  components: { BaseModal, DataTable },
  props: {
  },
  data() {
    return {
      openCreateModal: false,
      isEdit: false,
      editingId: null,
      form: this.emptyForm(),
      errors: {},
      dataList: [],
      loadingList: false,
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 5,
        total: 0,
      },
      viewPassword: false,
      sortView: false,
      filters: {
        q: '',
        status: '',
        per_page: 5,
        order: '',
      },
      columns: [
        { label: 'ID', field: 'id' },
        { label: 'Nombre', field: 'name' },
        { label: 'Email', field: 'email' },
        { label: 'Rol', field: 'role' },
        { label: 'Creado', field: 'created_at', formatter: (value) => value ? new Date(value).toLocaleDateString('es-VE')  : '' },
        { label: 'Acciones', field: 'actions' },
      ],
    }
  },
  computed: {
  },
  mounted() {
    this.fetchData(1)
  },
  methods: {
    async fetchData(page = 1) {
      this.loadingList = true
      try {
        const { data } = await axios.get('/api/users', {
          params: {
            page,
            per_page: this.filters.per_page,
            q: this.filters.q || undefined,
            order: this.filters.order || undefined,
            status: this.filters.status || undefined,
          },
        })

        // Laravel: { data: paginator }
        const paginator = data?.data
        
        this.dataList = paginator?.data ?? []
        this.pagination = {
          current_page: paginator?.current_page ?? page,
          last_page: paginator?.last_page ?? 1,
          per_page: paginator?.per_page ?? this.filters.per_page,
          total: paginator?.total ?? 0,
        }
      } catch (error) {
        console.error(error)
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'No se pudo cargar la lista.',
          customClass: { container: 'swal-high-z', confirmButton: 'button__primary' },
        })
      } finally {
        this.loadingList = false
      }
    },
    goToPage(page) {
      if (page < 1 || page > this.pagination.last_page) return
      this.fetchData(page)
    },
    sortData(criteria) {
      this.filters.order = criteria
      this.fetchData()
      this.sortView = false
    },
    applyFilters() {
      // al filtrar vuelves a página 1
      this.fetchData(1)
    },

    resetFilters() {
      this.filters.q = ''
      this.filters.status = ''
      this.filters.per_page = 5
      this.fetchData(1)
    },
    emptyForm() {
      return {
        name: '',
        email: '',
        role: '',
        password: '',
        password_confirmation: '',
      }
    },

    openCreate() {
      this.isEdit = false
      this.editingId = null
      this.autoSlug = true
      this.errors = {}
      this.form = this.emptyForm()
      this.openCreateModal = true
    },

    openEdit(user) {
      this.isEdit = true
      this.editingId = user.id
      this.autoSlug = false
      this.errors = {}

      // Mapea lo que venga del backend (ajusta si tus keys cambian)
      this.form = {
        name: user.name ?? '',
        email: user.email ?? '',
        role: user.role ?? '',
        password: '', 
      }

      this.openCreateModal = true
    },

    openDelete(user) {
      Swal.fire({
        title: '¿Estás seguro?',
        text: `Confirma que deseas eliminar el usuario "${user.name}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: { container: 'swal-high-z', confirmButton: 'button__primary', cancelButton: 'button__gray' },
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            await axios.delete(`/api/users/${user.id}`)
            Swal.fire({
              icon: 'success',
              title: 'Eliminado',
              text: 'El usuario ha sido eliminado.',
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
              text: error.response?.data?.message || 'No se pudo eliminar el usuario.',
              customClass: { container: 'swal-high-z', confirmButton: 'button__primary', },
            })
          }
        }
      })
    },

    resetForm() {
      // Se ejecuta al cerrar por overlay/esc también
      this.errors = {}
      this.form = this.emptyForm()
      this.isEdit = false
      this.editingId = null
    },


    validate() {
      const e = {}

      if (!this.form.name) e.name = 'El nombre es obligatorio.'
      if (!this.form.email) e.email = 'El email es obligatorio.'
      if (!this.form.role) e.role = 'El rol es obligatorio.'
      if (!this.isEdit && !this.form.password) e.password = 'La contraseña es obligatoria para nuevos usuarios.'
      else if (this.form.password && this.form.password.length < 6) e.password = 'La contraseña debe tener al menos 6 caracteres.'

      this.errors = e
      return Object.keys(e).length === 0
    },

    async save() {
      if (!this.validate()) return

      const payload = { ...this.form }

      const wasEdit = this.isEdit
      const userId = wasEdit ? this.editingId : null

      try {
        let id = userId

        if (wasEdit) {
          if (!payload.password) {
            delete payload.password
            delete payload.password_confirmation
          }
          await axios.put(`/api/users/${userId}`, payload)
        } else {
          const res = await axios.post('/api/users', payload)
          id = res.data?.data?.id
        }

        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: wasEdit ? 'Usuario actualizado con éxito.' : 'Usuario creado con éxito.',
          timer: 2000,
          showConfirmButton: false,
          customClass: { container: 'swal-high-z', confirmButton: 'button__primary', },
        })

        this.openCreateModal = false
        this.form = this.emptyForm()
        this.isEdit = false
        this.editingId = null
        this.errors = {}

        this.fetchData(this.pagination?.current_page || 1)

      } catch (error) {
        console.error(error)

        // ✅ si backend manda 422 con errors, mapea al form
        if (error.response?.status === 422 && error.response?.data?.errors) {
          const backendErrors = error.response.data.errors
          const e = {}
          Object.keys(backendErrors).forEach((k) => (e[k] = backendErrors[k]?.[0]))
          this.errors = e
        }

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.response?.data?.message || 'No se pudo guardar la rifa.',
          customClass: { container: 'swal-high-z', confirmButton: 'button__primary', },
        })
      }
    },

  },
}
</script>

<style scoped>
</style>
