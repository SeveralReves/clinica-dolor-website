<script setup>
import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const searchQuery = ref('')
const appointment = ref(null)
const loading = ref(false)
const searched = ref(false) // Para controlar el estado vacío inicial

const handleSearch = async () => {
  if (!searchQuery.value) return

  loading.value = true
  searched.value = true
  appointment.value = null

  try {
    const { data } = await axios.get('/api/appointments/status', {
      params: { search: searchQuery.value }
    })
    appointment.value = data
  } catch (e) {
    console.error(e)
    Swal.fire({
      icon: 'error',
      title: 'No encontrado',
      text: e.response?.data?.message || 'No pudimos encontrar una cita con esos datos.',
      customClass: { confirmButton: 'button__primary' }
    })
  } finally {
    loading.value = false
  }
}

const cancelAppointment = () => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción cancelará tu cita permanentemente.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, cancelar cita',
    cancelButtonText: 'No, mantenerla',
    customClass: {
      confirmButton: 'button__danger',
      cancelButton: 'button__secondary'
    }
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.put(`/api/appointments/${appointment.value.id}/cancel`)
        Swal.fire('¡Cancelada!', 'Tu cita ha sido cancelada.', 'success')
        handleSearch() // Recargamos para ver el log actualizado
      } catch (e) {
        Swal.fire('Error', 'No se pudo cancelar la cita.', 'error')
      }
    }
  })
}

const formatDate = (dateString) => {
  const options = { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }
  return new Date(dateString).toLocaleDateString('es-VE', options)
}
</script>

<template>
  <div class="appointment-status">
    <div class="appointment-status--container container">
      <header class="appointment-status__header">
        <h1 class="appointment-status__title">Consultar el estado de la cita</h1>
        <p class="appointment-status__description">
          Ingresa tu número de referencia o correo electrónico para ver detalles y actualizaciones en tiempo real.
        </p>

        <div class="appointment-status__search-bar">
          <div class="field-group">
            <span class="material-symbols-outlined field-group__icon">info</span>
            <input 
              v-model.trim="searchQuery"
              type="text" 
              class="field-group__input" 
              placeholder="Ref # (ej. A-12345) o Correo"
              @keyup.enter="handleSearch"
            />
          </div>
          <button 
            class="button__primary button--large" 
            :disabled="loading"
            @click="handleSearch"
          >
            <span v-if="!loading" class="material-symbols-outlined">search</span>
            <span v-else class="loader-spinner"></span>
            {{ loading ? 'Buscando...' : 'Buscar Cita' }}
          </button>
        </div>
      </header>

      <main class="appointment-status__content">
        <div v-if="loading" class="appointment-status__state appointment-status__state--loading">
          <div class="skeleton-card"></div>
        </div>

        <div v-else-if="searched && !appointment" class="appointment-status__state appointment-status__state--empty">
          <span class="material-symbols-outlined state-icon">event_busy</span>
          <h3>No se encontraron resultados</h3>
          <p>Verifica que el código de referencia o correo sean correctos.</p>
        </div>

        <article v-else-if="appointment" class="status-card">
          <div class="status-card__main">
            <div class="status-card__top">
              <div class="status-badge" :class="`status-badge--${appointment.status}`">
                <span class="material-symbols-outlined">check_circle</span>
                <div class="status-badge__info">
                  <small>CURRENT STATUS</small>
                  <strong>{{ appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1) }}</strong>
                </div>
              </div>
              <div class="status-card__ref">
                <span class="material-symbols-outlined">info</span>
                Ref: {{ appointment.reference_id }}
              </div>
            </div>

            <div class="status-card__doctor">
              <img :src="appointment.specialist.photo_url" class="status-card__doctor-photo" />
              <div class="status-card__doctor-details">
                <h2 class="status-card__doctor-name">{{ appointment.specialist.name }}</h2>
                <p class="status-card__doctor-role">{{ appointment.specialist.specialty }}</p>
                <!-- <div class="status-card__rating">
                  <span class="material-symbols-outlined">star</span>
                  4.9 (120+ Reviews)
                </div> -->
              </div>
            </div>

            <div class="status-card__grid">
              <div class="info-block">
                <span class="material-symbols-outlined info-block__icon">calendar_month</span>
                <div class="info-block__content">
                  <small>Fecha y Hora</small>
                  <strong>{{ new Date(appointment.date).toLocaleDateString('es-VE') }}</strong>
                  <p>{{ appointment.hour }}</p>
                </div>
              </div>
              <!-- <div class="info-block">
                <span class="material-symbols-outlined info-block__icon">location_on</span>
                <div class="info-block__content">
                  <small>LOCATION</small>
                  <strong>Clínica del Dolor</strong>
                  <p>Consultorio Principal</p>
                </div>
              </div> -->
            </div>

            <!-- <div class="status-card__actions">
              <button class="button__outline" @click="Swal.fire('Función en desarrollo', 'Próximamente podrás reprogramar.', 'info')">
                <span class="material-symbols-outlined">edit_calendar</span>
                Reschedule
              </button>
              <button class="button__outline button__outline--danger" @click="cancelAppointment">
                <span class="material-symbols-outlined">cancel</span>
                Cancel
              </button>
            </div> -->
          </div>

          <aside class="status-card__log">
            <h3 class="status-card__log-title">
              <span class="material-symbols-outlined">history</span>
              Activity Log
            </h3>
            
            <div class="timeline">
              <div 
                v-for="(log, index) in appointment.logs" 
                :key="log.id" 
                class="timeline__item"
                :class="{ 'timeline__item--latest': index === 0 }"
              >
                <div class="timeline__marker">
                  <span class="material-symbols-outlined">{{ index === 0 ? 'notifications_active' : 'check_circle' }}</span>
                </div>
                <div class="timeline__content">
                  <p class="timeline__label">{{ log.status_label }}</p>
                  <p class="timeline__date">{{ formatDate(log.created_at) }}</p>
                </div>
              </div>
            </div>
          </aside>
        </article>
      </main>
    </div>
  </div>
</template>

<style >
</style>