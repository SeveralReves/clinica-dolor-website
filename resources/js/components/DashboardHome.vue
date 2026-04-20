<template>
  <div class="dashboard-home">

    <!-- ── Stats Grid ──────────────────────────────────────────────── -->
    <div class="stats-grid">
      <div class="stat-card" v-for="stat in statCards" :key="stat.label">
        <div class="stat-card__icon-wrap" :style="{ background: stat.bg }">
          <span class="material-symbols-outlined" :style="{ color: stat.color }">{{ stat.icon }}</span>
        </div>
        <div class="stat-card__content">
          <div class="stat-card__value">{{ stat.value }}</div>
          <div class="stat-card__label">{{ stat.label }}</div>
        </div>
      </div>
    </div>

    <!-- ── Calendar + Sidebar ──────────────────────────────────────── -->
    <div class="dashboard-main">

      <!-- Calendar -->
      <div class="cal-card">
        <div class="cal-header">
          <button class="cal-nav-btn" @click="prevMonth">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <h3 class="cal-title">{{ monthLabel }}</h3>
          <button class="cal-nav-btn" @click="nextMonth">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
        <div class="cal-weekdays">
          <span v-for="d in weekdays" :key="d">{{ d }}</span>
        </div>
        <div class="cal-days">
          <div
            v-for="(day, i) in calDays"
            :key="i"
            class="cal-day"
            :class="{
              'cal-day--empty':    !day,
              'cal-day--today':    day && isToday(day),
              'cal-day--selected': day && isSelected(day),
              'cal-day--has-apt':  day && hasAppointments(day),
            }"
            @click="day && selectDay(day)"
          >
            <span v-if="day" class="cal-day__num">{{ day.getDate() }}</span>
            <div v-if="day && hasAppointments(day)" class="cal-day__dots">
              <span
                v-for="dot in getDots(day)"
                :key="dot.status"
                class="cal-dot"
                :class="'cal-dot--' + dot.status"
              ></span>
            </div>
          </div>
        </div>
        <!-- Legend -->
        <div class="cal-legend">
          <div class="cal-legend__item"><span class="cal-dot cal-dot--pending"></span> Pendiente</div>
          <div class="cal-legend__item"><span class="cal-dot cal-dot--confirmed"></span> Confirmada</div>
          <div class="cal-legend__item"><span class="cal-dot cal-dot--completed"></span> Completada</div>
          <div class="cal-legend__item"><span class="cal-dot cal-dot--cancelled"></span> Cancelada</div>
        </div>
      </div>

      <!-- Day sidebar -->
      <div class="apt-sidebar">
        <div class="apt-sidebar__header">
          <span class="material-symbols-outlined">calendar_today</span>
          <h4>{{ selectedDayLabel }}</h4>
          <span class="apt-sidebar__count" v-if="selectedDayApts.length">{{ selectedDayApts.length }}</span>
        </div>

        <div v-if="selectedDayApts.length === 0" class="apt-empty">
          <span class="material-symbols-outlined">event_busy</span>
          <p>Sin citas para este día</p>
        </div>

        <div v-else class="apt-list">
          <div
            v-for="apt in selectedDayApts"
            :key="apt.id"
            class="apt-item"
            :class="'apt-item--' + apt.status"
          >
            <div class="apt-item__time">{{ apt.hour }}</div>
            <div class="apt-item__body">
              <div class="apt-item__patient">{{ apt.patient?.name ?? '—' }}</div>
              <div class="apt-item__specialist">{{ apt.specialist?.name ?? '—' }}</div>
              <span class="status-badge" :class="'status--' + apt.status">
                {{ statusLabel(apt.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Próximas citas ───────────────────────────────────────────── -->
    <div class="upcoming-card">
      <div class="upcoming-header">
        <div>
          <h4 class="upcoming-header__title">Próximas citas</h4>
          <p class="upcoming-header__sub">Citas pendientes y confirmadas desde hoy</p>
        </div>
        <a href="/dashboard/citas" class="button__secondary button--small">
          <span class="material-symbols-outlined" style="font-size:16px;">open_in_new</span>
          Ver todas
        </a>
      </div>

      <div class="datatable">
        <table class="table">
          <thead>
            <tr>
              <th>Referencia</th>
              <th>Paciente</th>
              <th>Especialista</th>
              <th>Fecha / Hora</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="upcomingApts.length === 0">
              <td colspan="5" class="text-center">Sin citas próximas</td>
            </tr>
            <tr v-for="apt in upcomingApts" :key="apt.id">
              <td><span class="ref-badge">{{ apt.reference_id }}</span></td>
              <td>
                <div class="patient-cell">
                  <span class="patient-name">{{ apt.patient?.name ?? '—' }}</span>
                  <span class="patient-sub">{{ apt.patient?.email }}</span>
                </div>
              </td>
              <td>
                <div class="patient-cell">
                  <span class="patient-name">{{ apt.specialist?.name ?? '—' }}</span>
                  <span class="patient-sub">{{ apt.specialist?.specialty }}</span>
                </div>
              </td>
              <td>
                <div class="datetime-cell">
                  <span>{{ formatDate(apt.date) }}</span>
                  <span class="patient-sub">{{ apt.hour }}</span>
                </div>
              </td>
              <td>
                <span class="status-badge" :class="'status--' + apt.status">
                  {{ statusLabel(apt.status) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'DashboardHome',
  props: {
    stats:        { type: Object, default: () => ({}) },
    appointments: { type: Array,  default: () => [] },
  },
  data() {
    const today = new Date()
    return {
      calYear:      today.getFullYear(),
      calMonth:     today.getMonth(),
      selectedDate: today.toISOString().split('T')[0],
    }
  },
  computed: {
    statCards() {
      return [
        { label: 'Total citas',   value: this.stats.total       ?? 0, icon: 'calendar_month',   color: '#1565c0', bg: '#e3f2fd' },
        { label: 'Pendientes',    value: this.stats.pending      ?? 0, icon: 'pending',           color: '#e65100', bg: '#fff3e0' },
        { label: 'Confirmadas',   value: this.stats.confirmed    ?? 0, icon: 'check_circle',      color: '#1565c0', bg: '#e8f0fe' },
        { label: 'Completadas',   value: this.stats.completed    ?? 0, icon: 'task_alt',          color: '#2e7d32', bg: '#e8f5e9' },
        { label: 'Canceladas',    value: this.stats.cancelled    ?? 0, icon: 'cancel',            color: '#b71c1c', bg: '#fce4ec' },
        { label: 'Pacientes',     value: this.stats.patients     ?? 0, icon: 'group',             color: '#6a1b9a', bg: '#f3e5f5' },
        { label: 'Especialistas', value: this.stats.specialists  ?? 0, icon: 'medical_services',  color: '#00695c', bg: '#e0f2f1' },
      ]
    },
    weekdays() {
      return ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do']
    },
    monthLabel() {
      const months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
      return `${months[this.calMonth]} ${this.calYear}`
    },
    calDays() {
      const firstDay     = new Date(this.calYear, this.calMonth, 1)
      const startPad     = (firstDay.getDay() + 6) % 7 // Mon=0 … Sun=6
      const daysInMonth  = new Date(this.calYear, this.calMonth + 1, 0).getDate()
      const cells        = []
      for (let i = 0; i < startPad; i++)          cells.push(null)
      for (let d = 1; d <= daysInMonth; d++)       cells.push(new Date(this.calYear, this.calMonth, d))
      return cells
    },
    aptsByDate() {
      const map = {}
      for (const apt of this.appointments) {
        if (!map[apt.date]) map[apt.date] = []
        map[apt.date].push(apt)
      }
      return map
    },
    selectedDayApts() {
      return this.aptsByDate[this.selectedDate] ?? []
    },
    selectedDayLabel() {
      if (!this.selectedDate) return 'Selecciona un día'
      const [y, m, d] = this.selectedDate.split('-')
      return `${d}/${m}/${y}`
    },
    upcomingApts() {
      const today = new Date().toISOString().split('T')[0]
      return this.appointments
        .filter(a => a.date >= today && (a.status === 'pending' || a.status === 'confirmed'))
        .slice(0, 10)
    },
  },
  methods: {
    prevMonth() {
      if (this.calMonth === 0) { this.calMonth = 11; this.calYear-- }
      else this.calMonth--
    },
    nextMonth() {
      if (this.calMonth === 11) { this.calMonth = 0; this.calYear++ }
      else this.calMonth++
    },
    isToday(date) {
      const t = new Date()
      return date.getDate() === t.getDate() && date.getMonth() === t.getMonth() && date.getFullYear() === t.getFullYear()
    },
    isSelected(date) {
      return this.dateStr(date) === this.selectedDate
    },
    hasAppointments(date) {
      return !!this.aptsByDate[this.dateStr(date)]
    },
    getDots(date) {
      const apts = this.aptsByDate[this.dateStr(date)] ?? []
      const seen = new Set()
      return apts
        .filter(a => { if (seen.has(a.status)) return false; seen.add(a.status); return true })
        .slice(0, 4)
        .map(a => ({ status: a.status }))
    },
    selectDay(date) {
      this.selectedDate = this.dateStr(date)
    },
    dateStr(date) {
      const y = date.getFullYear()
      const m = String(date.getMonth() + 1).padStart(2, '0')
      const d = String(date.getDate()).padStart(2, '0')
      return `${y}-${m}-${d}`
    },
    statusLabel(s) {
      return { pending: 'Pendiente', confirmed: 'Confirmada', completed: 'Completada', cancelled: 'Cancelada' }[s] ?? s
    },
    formatDate(d) {
      if (!d) return '—'
      const [y, m, day] = String(d).substring(0, 10).split('-')
      return `${day}/${m}/${y}`
    },
  },
}
</script>

<style scoped>
/* ── Wrapper ───────────────────────────────────────────────────── */
.dashboard-home {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* ── Stats ─────────────────────────────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 16px;
}
.stat-card {
  background: white;
  border-radius: 12px;
  padding: 18px 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
  transition: box-shadow 0.15s;
}
.stat-card:hover { box-shadow: 0 3px 10px rgba(0,0,0,0.12); }
.stat-card__icon-wrap {
  width: 46px;
  height: 46px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-card__icon-wrap span { font-size: 24px; }
.stat-card__value  { font-size: 1.7rem; font-weight: 700; line-height: 1; }
.stat-card__label  { font-size: 0.78rem; color: #777; margin-top: 3px; }

/* ── Main grid ─────────────────────────────────────────────────── */
.dashboard-main {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 20px;
  align-items: start;
}

/* ── Calendar ──────────────────────────────────────────────────── */
.cal-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
}
.cal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.cal-title { font-size: 1rem; font-weight: 700; }
.cal-nav-btn {
  background: none;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  transition: background 0.15s;
}
.cal-nav-btn:hover { background: #f4f4f4; }
.cal-nav-btn span  { font-size: 20px; color: #555; }

.cal-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  text-align: center;
  font-size: 0.72rem;
  font-weight: 700;
  color: #999;
  padding-bottom: 8px;
  border-bottom: 1px solid #f0f0f0;
  margin-bottom: 4px;
}
.cal-days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}
.cal-day {
  min-height: 58px;
  border-radius: 8px;
  padding: 4px;
  cursor: pointer;
  transition: background 0.12s;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}
.cal-day--empty   { cursor: default; }
.cal-day:not(.cal-day--empty):hover { background: #f4f4f4; }

.cal-day--today .cal-day__num {
  background: var(--color-primary, #0ca678);
  color: white;
  border-radius: 50%;
}
.cal-day--selected { background: #e8f5e9; }
.cal-day--selected .cal-day__num { font-weight: 700; }

.cal-day__num {
  font-size: 0.85rem;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}
.cal-day__dots {
  display: flex;
  gap: 3px;
  flex-wrap: wrap;
  justify-content: center;
}
.cal-dot               { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
.cal-dot--pending      { background: #e65100; }
.cal-dot--confirmed    { background: #1565c0; }
.cal-dot--completed    { background: #2e7d32; }
.cal-dot--cancelled    { background: #b71c1c; }

.cal-legend {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  margin-top: 14px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}
.cal-legend__item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.75rem;
  color: #666;
}

/* ── Day Sidebar ───────────────────────────────────────────────── */
.apt-sidebar {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
  max-height: 520px;
  overflow-y: auto;
}
.apt-sidebar__header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  font-weight: 700;
}
.apt-sidebar__header span.material-symbols-outlined { font-size: 20px; color: #555; }
.apt-sidebar__header h4 { margin: 0; font-size: 0.95rem; flex: 1; }
.apt-sidebar__count {
  background: var(--color-primary, #0ca678);
  color: white;
  border-radius: 12px;
  padding: 1px 8px;
  font-size: 0.75rem;
  font-weight: 700;
}

.apt-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 40px 0;
  color: #bbb;
}
.apt-empty span { font-size: 38px; }
.apt-empty p    { font-size: 0.85rem; }

.apt-list { display: flex; flex-direction: column; gap: 10px; }
.apt-item {
  display: flex;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 8px;
  background: #f8f9fa;
  border-left: 3px solid #ddd;
}
.apt-item--pending   { border-left-color: #e65100; }
.apt-item--confirmed { border-left-color: #1565c0; }
.apt-item--completed { border-left-color: #2e7d32; }
.apt-item--cancelled { border-left-color: #b71c1c; opacity: 0.7; }

.apt-item__time {
  font-size: 0.8rem;
  font-weight: 700;
  color: #555;
  min-width: 44px;
  padding-top: 2px;
}
.apt-item__patient    { font-size: 0.88rem; font-weight: 600; }
.apt-item__specialist { font-size: 0.76rem; color: #888; margin-bottom: 4px; }

/* ── Upcoming table ────────────────────────────────────────────── */
.upcoming-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07);
}
.upcoming-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 16px;
  gap: 12px;
}
.upcoming-header__title { font-size: 1rem; font-weight: 700; margin: 0 0 2px; }
.upcoming-header__sub   { font-size: 0.78rem; color: #888; margin: 0; }
.upcoming-header a {
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
  text-decoration: none;
}

/* ── Status badges ─────────────────────────────────────────────── */
.status-badge      { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.status--pending   { background: #fff3e0; color: #e65100; }
.status--confirmed { background: #e3f2fd; color: #1565c0; }
.status--completed { background: #e8f5e9; color: #2e7d32; }
.status--cancelled { background: #fce4ec; color: #b71c1c; }

/* ── Shared table helpers ──────────────────────────────────────── */
.ref-badge     { font-family: monospace; font-size: 12px; background: #f4f4f4; padding: 2px 6px; border-radius: 4px; }
.patient-cell  { display: flex; flex-direction: column; gap: 1px; }
.patient-name  { font-weight: 600; font-size: 0.88rem; }
.patient-sub   { font-size: 0.75rem; color: #777; }
.datetime-cell { display: flex; flex-direction: column; gap: 2px; font-size: 0.85rem; }
.text-center   { text-align: center; padding: 24px; color: #aaa; font-size: 0.88rem; }

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 960px) {
  .dashboard-main { grid-template-columns: 1fr; }
  .apt-sidebar    { max-height: 300px; }
}
@media (max-width: 640px) {
  .stats-grid      { grid-template-columns: repeat(2, 1fr); }
  .dashboard-home  { padding: 16px; }
}
</style>
