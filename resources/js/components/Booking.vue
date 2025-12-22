<script setup>
import { ref, computed } from 'vue'
import { Field, Form, ErrorMessage } from 'vee-validate'
import * as Yup from 'yup'
import DatePicker from 'vue-datepicker-next'
import 'vue-datepicker-next/index.css'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'
import axios from 'axios'
import Swal from 'sweetalert2' 

// Props
const props = defineProps({
  wp_action: { type: String, default: 'success' },
  specialists: { type: Array, default: () => [] },
  disabledDates: { type: Array, default: () => [] }
})


const vvForm = ref(null) 

const form = ref({
  name: '',
  email: '',
  phone: '',
  birthday: null,
  date: null,
  hour: null,
  reason: '',
  specialist: null,
})
// Date limits
const todayStart = computed(() => {
  const d = new Date()
  d.setHours(0, 0, 0, 0)
  return d
})

// Validation schema
const schema = Yup.object({
  name: Yup.string().required('El nombre es obligatorio.'),
  email: Yup.string()
    .required('El correo es obligatorio.')
    .email('Ingresa un correo válido.'),
  phone: Yup.string().required('El teléfono es obligatorio.'),
  birthday: Yup.date()
    .required('La fecha de nacimiento es obligatoria.')
    .max(new Date(), 'La fecha de nacimiento no puede ser en el futuro.'),
  reason: Yup.string()
    .max(500, 'Máximo 500 caracteres.'),
  date: Yup.date()
    .required('La fecha de la cita es obligatoria.')
    .min(todayStart.value, 'La fecha no puede ser en el pasado.'),
  hour: Yup.string().required('La hora de la cita es obligatoria.'),
  specialist_id: Yup.mixed().required('Debes seleccionar un especialista.'),
})

// Utils
const normalizeDay = (d) => {
  const x = new Date(d)
  x.setHours(0, 0, 0, 0)
  return x
}

const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']

const isDisabledForm = computed(() => {
  return !form.value.name ||
         !form.value.email ||
         !form.value.phone ||
         !form.value.birthday ||
         !form.value.date ||
         !form.value.hour ||
         !form.value.reason ||
         !form.value.specialist
})

const isDateDisabled = (d) => {
  const x = normalizeDay(d)
  if (x < todayStart.value) return true 
  if (form.value.specialist === null){
    return true
  }else{
    const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
    const dayName = dayNames[x.getDay()]
    const specialistHours = form.value.specialist.hours
    if (!specialistHours.hasOwnProperty(dayName) || specialistHours[dayName].length === 0) {
      return true 
    }
  }
  if (x.getDay() === 0 || x.getDay() === 6) return true // weekends
}

const changeSpecialist = (s) => {
  form.value.specialist = s
  form.value.date = null
  form.value.hour = null
}

const changeDate = (d) => {
  form.value.date = d
  form.value.hour = null
}

const changeBirthday = (d) => {
  form.value.birthday = d
}

const changeHour = (h) => {
  form.value.hour = h
}

const submitFromAside = () => {
  onSubmit(vvForm.value.values)
}

async function onSubmit(values) {
  const payload = {
    ...values,
    date: form.value.date,
    hour: form.value.hour,
    birthday: form.value.birthday,
    specialist_id: form.value.specialist?.id || null,
  }

  try {
    const res = await axios.post(props.wp_action, payload) // ✅ props.wp_action
    Swal?.fire({
      icon: 'success',
      title: '¡Listo!',
      text: 'Tu solicitud de cita fue enviada correctamente.',
      timer: 2000,
      showConfirmButton: false,
    })
  } catch (e) {
    console.error(e)
    Swal?.fire({
      icon: 'error',
      title: 'Error',
      text: e.response?.data?.message || 'Hubo un problema enviando tu solicitud.',
    })
  }
}

</script>

<template>
  <div class="booking">
    <div class="booking__content">
      <div class="booking__card" ref="cardSpecialist">
        <h2 class="booking__card--title">
          <span class="booking__card--icon">
            1
          </span>
          Escoge un especialista
        </h2>
        <div class="booking__card--cards">
          <div class="js-specialist-slider">
            <!-- Specialist cards go here -->
            <template v-for="specialist in specialists" :key="specialist.id">
              <article @click="changeSpecialist(specialist)" class="booking__card--specialist" :class="{active: form.specialist?.id === specialist.id}">
                <img :src="specialist.photo.url" :alt="specialist.name" class="booking__card--specialist-photo" />
                <div class="booking__card--specialist-content">
                  <h3 class="booking__card--specialist-name">{{ specialist.name }}</h3>
                  <p class="booking__card--specialist-specialty">{{ specialist.role }}</p>

                </div>
              </article>
            </template>
          </div>
        </div>
      </div>
      <div class="booking__card" ref="cardDate">
        <h2 class="booking__card--title">
          <span class="booking__card--icon">
            2
          </span>
          Selecciona fecha y hora
        </h2>
        <div class="booking__card--content">
          <div class="booking__card--date">

          <DatePicker
            :value="form.date"
            :disabled-date="isDateDisabled"
            :clearable="false"
            :disabled="form.specialist === null "
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
            <div class="booking__card--hours-list" v-if="form.date !== null ">
              <button class="booking__card--hour" v-for="hour in form.specialist?.hours[dayNames[normalizeDay(form.date).getDay()]]" :class="{selected: form.hour === hour}" :key="hour" @click="changeHour(hour)">
                {{ hour }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="booking__card" ref="cardPatientDetails">
        <h2 class="booking__card--title">
          <span class="booking__card--icon">
            3
          </span>
          Detalles de paciente
        </h2>
        <div class="booking__card--form">
          <Form ref="vvForm" :validation-schema="schema" @submit="onSubmit"  class="booking__card--form-grid">
            <div class="booking__card--form-group">
              <label for="name">Nombre completo</label>
              <Field id="name" name="name" v-model="form.name" placeholder="Ingresa tu nombre completo" type="text" class="booking__card--form-input" />
              <ErrorMessage name="name" class="booking__card--form-error" />
            </div>
            <div class="booking__card--form-group">
              <label for="phone">Número de teléfono</label>
              <Field id="phone" name="phone" v-model="form.phone" placeholder="Ingresa tu número de teléfono" type="text" class="booking__card--form-input" />
              <ErrorMessage name="phone" class="booking__card--form-error" />
            </div>
            <div class="booking__card--form-group">
              <label for="email">Correo electrónico</label>
              <Field id="email" name="email" v-model="form.email" placeholder="Correo@mail.com" type="email" class="booking__card--form-input" />
              <ErrorMessage name="email" class="booking__card--form-error" />
            </div>
            <div class="booking__card--form-group">
              <label for="birthday">Fecha de nacimiento</label>
              <DatePicker
                id="birthday"
                :value="form.birthday"
                :clearable="false"
                :input-attr="{ placeholder: 'Selecciona una fecha' }"
                format="DD/MM/YYYY"
                @change="changeBirthday($event)"
                class="booking__card--form-input"
              />
              <!-- <ErrorMessage name="birthday" class="booking__card--form-error" /> -->
            </div>

            <div class="booking__card--form-group booking__card--form-group--full">
              <label for="reason">Razón de la consulta</label>
              <Field id="reason" name="reason" v-model="form.reason" placeholder="Describe brevemente la razón de tu consulta" as="textarea" rows="4" class="booking__card--form-input" />
              <ErrorMessage name="reason" class="booking__card--form-error" />
            </div>
          </Form>
        </div>
        <div class="booking__card--info">
          <h4 class="booking__card--info-title">
            <span class="booking__card--icon">
              i
            </span>
            Información importante
          </h4>
          <div class="booking__card--info-content">
            <p>Por favor, llega 10 minutos antes de tu cita. Si necesitas cancelar o reprogramar, avísanos con al menos 24 horas de anticipación. Trae cualquier documentación médica relevante para tu consulta.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="booking__aside">
      <div class="booking__aside--sticky">
        <div class="booking__aside--card">
          <h3 class="booking__aside--title">
              <i class="bx bx-file"></i>
            <span>Resumen de la cita</span>
          </h3>
          <div class="booking__aside--header">
            <div v-if="form.specialist" class="booking__aside--specialist">
              <img  :src="form.specialist.photo.url" :alt="form.specialist.name" class="booking__aside--specialist-photo" />
              <div class="booking__aside--specialist-info">
                <p class="booking__aside--specialist-name">{{ form.specialist ? form.specialist.name : 'Especialista no seleccionado' }}</p>
                <p class="booking__aside--specialist-role">{{ form.specialist ? form.specialist.role : '' }}</p>
              </div>
            </div>
          </div>
          <div class="booking__aside--content">
            <!-- <p><strong>Especialista:</strong> {{ form.specialist ? form.specialist.name : 'No seleccionado' }}</p> -->
            <p><strong>Fecha:</strong> {{ form.date ? new Date(form.date).toLocaleDateString() : 'No seleccionada' }}</p>
            <p><strong>Hora:</strong> {{ form.hour ? form.hour : 'No seleccionada' }}</p>
          </div>
          <button
            type="button"
            class="booking__aside--submit"
            :disabled="isDisabledForm"
            @click="submitFromAside"
          >
            Enviar solicitud de cita
          </button>
          <span class="booking__aside--legend">Al reservar, acepta nuestra política de cancelación.</span>
        </div>
        <div class="booking__card">
          <h2 class="booking__card--title">
            <span class="booking__card--icon">
              <i class="bx bx-confused"></i>
            </span>
            ¿Necesitas ayuda?
          </h2>
          <div class="booking__card--help">
            <p>Si tienes alguna duda o necesitas asistencia para reservar tu cita, no dudes en contactarnos.</p>
            <p><strong>Teléfono:</strong> <a href="tel:+582473413436">+247 3413436</a></p>
            <p><strong>Correo electrónico:</strong> <a href="mailto:clinicadeldolorapure@gmail.com">clinicadeldolorapure@gmail.com</a></p>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Fade animation between steps */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
