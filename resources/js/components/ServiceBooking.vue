<script setup>
import { ref, computed, onMounted } from 'vue'
import { Field, Form, ErrorMessage } from 'vee-validate'
import * as Yup from 'yup'
import DatePicker from 'vue-datepicker-next'
import 'vue-datepicker-next/index.css'
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
  services: { type: Array, default: () => [] },
})

// ── State ──────────────────────────────────────────────────────────────────
const availableSlots = ref([])
const loadingSlots   = ref(false)
const vvForm         = ref(null)

const form = ref({
  name:     '',
  email:    '',
  phone:    '',
  birthday: null,
  date:     null,
  hour:     null,
  notes:    '',
  service:  null,
})

const todayStart = computed(() => {
  const d = new Date()
  d.setHours(0, 0, 0, 0)
  return d
})

const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']

// ── URL param: preselect service ───────────────────────────────────────────
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  const id     = Number(params.get('service'))
  if (!id) return
  const svc = props.services.find(s => Number(s.id) === id)
  if (svc) changeService(svc)
})

// ── Helpers ────────────────────────────────────────────────────────────────
function toLocalDateStr(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

// ── Calendar disable logic ─────────────────────────────────────────────────
const isDateDisabled = (d) => {
  const x = new Date(d)
  x.setHours(0, 0, 0, 0)
  if (x < todayStart.value) return true
  if (!form.value.service)  return true

  const dateStr = toLocalDateStr(x)
  const svc     = form.value.service

  if (svc.blocked_dates?.includes(dateStr))        return true
  if (svc.custom_enabled_dates?.includes(dateStr)) return false

  const dayName = dayNames[x.getDay()]
  return !svc.scheduled_days?.includes(dayName)
}

// ── Fetch available slots ──────────────────────────────────────────────────
async function fetchAvailableSlots() {
  if (!form.value.service || !form.value.date) return
  loadingSlots.value = true
  availableSlots.value = []
  try {
    const { data } = await axios.get('/api/service-bookings/available-slots', {
      params: {
        service_id: form.value.service.id,
        date:       toLocalDateStr(form.value.date),
      },
    })
    availableSlots.value = data.available_slots ?? []
  } catch (e) {
    console.error('Error cargando disponibilidad', e)
  } finally {
    loadingSlots.value = false
  }
}

// ── Event handlers ─────────────────────────────────────────────────────────
const changeService = (svc) => {
  form.value.service = svc
  form.value.date    = null
  form.value.hour    = null
  availableSlots.value = []
}

const changeDate = (d) => {
  form.value.date = d
  form.value.hour = null
  fetchAvailableSlots()
}

const changeBirthday = (d) => { form.value.birthday = d }
const changeHour = (h) => { form.value.hour = h }

const dayShortLabel = (day) => ({
  monday:'Lun', tuesday:'Mar', wednesday:'Mié',
  thursday:'Jue', friday:'Vie', saturday:'Sáb', sunday:'Dom',
})[day] ?? day

// ── Submit disable check ───────────────────────────────────────────────────
const isDisabledForm = computed(() =>
  !form.value.service  ||
  !form.value.date     ||
  !form.value.hour     ||
  !form.value.name     ||
  !form.value.email    ||
  !form.value.phone    ||
  !form.value.birthday
)

// ── Validation schema ──────────────────────────────────────────────────────
const schema = Yup.object({
  name:     Yup.string().required('El nombre es obligatorio.'),
  email:    Yup.string().required('El correo es obligatorio.').email('Correo inválido.'),
  phone:    Yup.string().required('El teléfono es obligatorio.'),
  birthday: Yup.date()
    .required('La fecha de nacimiento es obligatoria.')
    .max(new Date(), 'No puede ser en el futuro.'),
})

const submitFromAside = () => { onSubmit(vvForm.value.values) }

async function onSubmit(values) {
  if (!form.value.service || !form.value.date || !form.value.hour) {
    Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debes seleccionar un servicio, fecha y hora.' })
    return
  }

  const payload = {
    ...values,
    service_id: form.value.service.id,
    date:       toLocalDateStr(form.value.date),
    hour:       form.value.hour,
    birthday:   form.value.birthday
      ? toLocalDateStr(form.value.birthday)
      : null,
    notes: form.value.notes || null,
  }

  try {
    const res = await axios.post('/api/service-bookings', payload)
    const reference = res.data.booking.reference_id

    Swal.fire({
      icon: 'success',
      title: '¡Reserva Registrada!',
      text: `Tu reserva fue enviada correctamente. Referencia: ${reference}`,
      customClass: { container: 'swal-high-z', confirmButton: 'button__primary' },
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = `/reservar-servicio/gracias/${reference}`
      }
    })
  } catch (e) {
    const msg = e.response?.data?.message || 'Hubo un problema al enviar tu reserva.'
    Swal.fire({ icon: 'error', title: 'Error', text: msg })
  }
}
</script>

<template>
  <div class="booking">
    <div class="booking__content">

      <!-- ── Step 1: Select service ──────────────────────────────────── -->
      <div class="booking__card">
        <h2 class="booking__card--title">
          <span class="booking__card--icon">1</span>
          Elige un servicio
        </h2>
        <div class="srv-grid">
          <article
            v-for="svc in services"
            :key="svc.id"
            class="srv-card"
            :class="{ active: form.service?.id === svc.id }"
            @click="changeService(svc)"
          >
            <div class="srv-card__img-wrap">
              <img :src="svc.photo_url" :alt="svc.title" class="srv-card__img" />
              <span v-if="form.service?.id === svc.id" class="srv-card__check material-symbols-outlined">check_circle</span>
            </div>
            <div class="srv-card__body">
              <h3 class="srv-card__name">{{ svc.title }}</h3>
              <p v-if="svc.description" class="srv-card__desc">{{ svc.description }}</p>
              <div v-if="svc.scheduled_days?.length" class="srv-card__days">
                <span
                  v-for="day in svc.scheduled_days"
                  :key="day"
                  class="day-chip"
                >{{ dayShortLabel(day) }}</span>
              </div>
            </div>
          </article>
        </div>
      </div>

      <!-- ── Step 2: Date + time ─────────────────────────────────────── -->
      <div class="booking__card">
        <h2 class="booking__card--title">
          <span class="booking__card--icon">2</span>
          Selecciona fecha y hora
        </h2>
        <div class="booking__card--content">
          <div class="booking__card--date">
            <DatePicker
              :value="form.date"
              :disabled-date="isDateDisabled"
              :clearable="false"
              :disabled="form.service === null"
              :input-attr="{ placeholder: 'Selecciona una fecha' }"
              format="DD/MM/YYYY"
              @change="changeDate($event)"
              class="booking__card--datepicker"
            />
          </div>
          <div class="booking__card--divider"></div>
          <div class="booking__card--hours">
            <div class="booking__card--hours-label">
              <span>Horarios disponibles:</span>
            </div>
            <div v-if="form.date !== null" class="booking__card--hours-list">
              <div v-if="loadingSlots" class="loading-text">Consultando disponibilidad...</div>
              <template v-else>
                <button
                  v-for="slot in availableSlots"
                  :key="slot.hour"
                  class="booking__card--hour srv-hour-btn"
                  :class="{ selected: form.hour === slot.hour }"
                  @click="changeHour(slot.hour)"
                >
                  <span class="srv-hour-btn__time">{{ slot.hour }}</span>
                  <span class="srv-hour-btn__cap">{{ slot.remaining }}/{{ slot.capacity }} cupos</span>
                </button>
                <div v-if="availableSlots.length === 0" class="no-hours">
                  No hay horarios disponibles para este día.
                </div>
              </template>
            </div>
            <div v-else class="no-hours">
              {{ form.service ? 'Selecciona una fecha para ver los horarios.' : 'Primero elige un servicio.' }}
            </div>
          </div>
        </div>
      </div>

      <!-- ── Step 3: Patient details ────────────────────────────────── -->
      <div class="booking__card">
        <h2 class="booking__card--title">
          <span class="booking__card--icon">3</span>
          Datos del paciente
        </h2>
        <div class="booking__card--form">
          <Form ref="vvForm" :validation-schema="schema" @submit="onSubmit" class="booking__card--form-grid">
            <div class="booking__card--form-group">
              <label for="sb-name">Nombre completo</label>
              <Field id="sb-name" name="name" v-model="form.name" type="text" placeholder="Ingresa tu nombre completo" class="booking__card--form-input" />
              <ErrorMessage name="name" class="booking__card--form-error" />
            </div>
            <div class="booking__card--form-group">
              <label for="sb-phone">Teléfono</label>
              <Field id="sb-phone" name="phone" v-model="form.phone" type="text" placeholder="+58..." class="booking__card--form-input" />
              <ErrorMessage name="phone" class="booking__card--form-error" />
            </div>
            <div class="booking__card--form-group">
              <label for="sb-email">Correo electrónico</label>
              <Field id="sb-email" name="email" v-model="form.email" type="email" placeholder="correo@ejemplo.com" class="booking__card--form-input" />
              <ErrorMessage name="email" class="booking__card--form-error" />
            </div>
            <div class="booking__card--form-group">
              <label for="sb-birthday">Fecha de nacimiento</label>
              <DatePicker
                id="sb-birthday"
                :value="form.birthday"
                :clearable="false"
                :input-attr="{ placeholder: 'Selecciona una fecha' }"
                format="DD/MM/YYYY"
                @change="changeBirthday($event)"
                class="booking__card--form-input"
              />
            </div>
            <div class="booking__card--form-group booking__card--form-group--full">
              <label for="sb-notes">Notas adicionales <span style="font-weight:400; opacity:0.6;">(opcional)</span></label>
              <Field id="sb-notes" name="notes" v-model="form.notes" as="textarea" rows="3" placeholder="Indica si tienes alguna condición especial o requieres información adicional..." class="booking__card--form-input" />
            </div>
          </Form>
        </div>
        <div class="booking__card--info">
          <h4 class="booking__card--info-title">
            <span class="booking__card--icon">i</span>
            <span style="flex: 1">
              Información importante
            </span>
          </h4>
          <div class="booking__card--info-content">
            <p>Llega 10 minutos antes de tu reserva. Si necesitas cancelar o reprogramar, avísanos con al menos 24 horas de anticipación.</p>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Aside: Summary ───────────────────────────────────────────── -->
    <div class="booking__aside">
      <div class="booking__aside--sticky">
        <div class="booking__aside--card">
          <h3 class="booking__aside--title">
            <span class="material-symbols-outlined" style="font-size:22px;">event_available</span>
            Resumen de la reserva
          </h3>

          <!-- Service info -->
          <div v-if="form.service" class="srv-aside-service">
            <img :src="form.service.photo_url" :alt="form.service.title" class="srv-aside-service__img" />
            <div>
              <p class="srv-aside-service__name">{{ form.service.title }}</p>
            </div>
          </div>

          <div class="booking__aside--content">
            <p><strong>Fecha:</strong> {{ form.date ? toLocalDateStr(form.date) : 'No seleccionada' }}</p>
            <p><strong>Hora:</strong> {{ form.hour ?? 'No seleccionada' }}</p>
          </div>

          <button
            type="button"
            class="booking__aside--submit"
            :disabled="isDisabledForm"
            @click="submitFromAside"
          >
            Confirmar reserva
          </button>
          <span class="booking__aside--legend">Al reservar, acepta nuestra política de cancelación.</span>
        </div>

        <div class="booking__card">
          <h2 class="booking__card--title">
            <span class="booking__card--icon">
              <span class="material-symbols-outlined" style="font-size:18px;">help</span>
            </span>
            ¿Necesitas ayuda?
          </h2>
          <div class="booking__card--help">
            <p>Si tienes dudas o necesitas asistencia, contáctanos.</p>
            <p><strong>Teléfono:</strong> <a href="tel:+582473413436">+247 3413436</a></p>
            <p><strong>Email:</strong> <a href="mailto:clinicadeldolorapure@gmail.com">clinicadeldolorapure@gmail.com</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── Service selection grid ────────────────────────────────────── */
.srv-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
  gap: 16px;
  margin-top: 20px;
}
.srv-card {
  border-radius: 16px;
  border: 2px solid rgba(38, 70, 123, 0.15);
  background: #fff;
  cursor: pointer;
  overflow: hidden;
  transition: border-color 0.2s, box-shadow 0.2s;
  position: relative;
}
.srv-card:hover { border-color: #26467b; box-shadow: 0 4px 16px rgba(38,70,123,.15); }
.srv-card.active { border-color: #41d28f; box-shadow: 0 4px 16px rgba(65,210,143,.25); }

.srv-card__img-wrap { position: relative; height: 130px; }
.srv-card__img      { width: 100%; height: 100%; object-fit: cover; display: block; }
.srv-card__check    {
  position: absolute; top: 8px; right: 8px;
  font-size: 26px; color: #41d28f;
  background: white; border-radius: 50%; line-height: 1;
}

.srv-card__body { padding: 12px 14px; }
.srv-card__name { font-size: 0.95rem; font-weight: 700; color: #26467b; margin: 0 0 5px; }
.srv-card__desc {
  font-size: 0.78rem; color: #666; margin: 0 0 8px;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.srv-card__days { display: flex; flex-wrap: wrap; gap: 4px; }
.day-chip {
  background: #e8f5e9; color: #2e7d32;
  font-size: 0.68rem; font-weight: 700;
  padding: 2px 7px; border-radius: 10px;
}

/* ── Hour buttons with capacity ────────────────────────────────── */
.srv-hour-btn {
  display: flex !important;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  min-width: 90px;
}
.srv-hour-btn__time { font-size: 0.88rem; font-weight: 600; }
.srv-hour-btn__cap  { font-size: 0.68rem; opacity: 0.75; }
.srv-hour-btn.selected .srv-hour-btn__cap { opacity: 1; }

/* ── Aside service display ─────────────────────────────────────── */
.srv-aside-service {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,.2);
}
.srv-aside-service__img {
  width: 52px; height: 52px; border-radius: 10px; object-fit: cover;
  border: 2px solid rgba(255,255,255,.3);
}
.srv-aside-service__name { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }

/* ── General helpers ───────────────────────────────────────────── */
.loading-text { font-size: 0.85rem; color: #888; padding: 8px 0; }
.no-hours     { font-size: 0.85rem; color: #999; padding: 8px 0; font-style: italic; }
</style>
