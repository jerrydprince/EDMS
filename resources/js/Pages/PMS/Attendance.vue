<template>
  <main class="w-full px-4 sm:px-6 py-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
      <Link href="/dashboard/pms" class="hover:text-blue-600 flex items-center gap-1">
        <Icon icon="lucide:arrow-left" width="18" />
        <span>PMS Hub</span>
      </Link>
      <Icon icon="ic:baseline-greater-than" width="16" class="text-gray-300" />
      <Link :href="`/dashboard/pms-user-profile/${props.user?.id}`" class="hover:text-blue-600 font-medium text-gray-700">
        {{ props.user?.first_name }} {{ props.user?.last_name }}'s Scorecard
      </Link>
      <Icon icon="ic:baseline-greater-than" width="16" class="text-gray-300" />
      <span class="px-3 py-1 rounded-md bg-blue-50 text-blue-700 font-semibold text-xs uppercase tracking-wide">
        Attendance Logs
      </span>
    </div>

    <!-- Header & Date Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="font-bold text-2xl text-gray-800">Staff Attendance & Login Logs</h1>
        <p class="text-gray-500 text-sm mt-0.5">Showing recorded sign-in / sign-out sessions for {{ props.user?.first_name }} {{ props.user?.last_name }}</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-1.5 shadow-sm">
          <Icon icon="lucide:calendar" class="text-gray-400" width="18" />
          <input type="date" v-model="startDate" class="border-0 p-0 text-sm focus:ring-0 text-gray-700 bg-transparent" placeholder="From" />
          <span class="text-gray-400">to</span>
          <input type="date" v-model="endDate" class="border-0 p-0 text-sm focus:ring-0 text-gray-700 bg-transparent" placeholder="To" />
          <button v-if="startDate || endDate" @click="startDate = ''; endDate = ''" class="text-xs text-red-500 hover:underline ml-1">Reset</button>
        </div>

        <button
          v-if="isCurrentUser && !isCheckedInToday"
          @click="openCheckInModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all"
          title="Authenticate check-in using Digital Signature PIN"
        >
          <Icon icon="lucide:key-round" width="14" />
          <span>Duty Check-In</span>
        </button>

        <button
          v-else-if="isCurrentUser && isCheckedInToday && !isCheckedOutToday"
          @click="openCheckOutModal"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-lg shadow-sm transition-all"
          title="Authenticate check-out using Digital Signature PIN"
        >
          <Icon icon="lucide:log-out" width="14" />
          <span>Shift Check-Out</span>
        </button>

        <button
          @click="generateAttendanceReport"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-blue-700 bg-blue-50/80 hover:bg-blue-100 border border-blue-200 rounded-lg shadow-sm transition-all"
          title="Download PDF Attendance Log"
        >
          <Icon icon="lucide:file-text" width="15" />
          <span>Generate Report</span>
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
          <Icon icon="lucide:calendar-check" width="24" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Recorded Days</p>
          <p class="text-2xl font-bold text-gray-800">{{ filteredAttendance.length }}</p>
        </div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
          <Icon icon="lucide:clock" width="24" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Average Hours / Day</p>
          <p class="text-2xl font-bold text-gray-800">{{ avgHoursPerDay }}</p>
        </div>
      </div>
      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
          <Icon icon="lucide:shield-check" width="24" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Completed Sessions</p>
          <p class="text-2xl font-bold text-gray-800">{{ completedSessionsCount }}</p>
        </div>
      </div>
    </div>

    <!-- Official Duty Exemption Active Banner -->
    <div v-if="props.user?.is_on_official_assignment == 1" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-sm">
      <div class="p-2 bg-emerald-100 rounded-lg text-emerald-700 mt-0.5">
        <Icon icon="lucide:plane-takeoff" width="20" />
      </div>
      <div>
        <h4 class="font-bold text-sm">Staff Currently on Official Duty Assignment</h4>
        <p class="text-xs text-emerald-700 mt-0.5">
          This staff member is actively deployed on an approved official assignment and is authorized as <strong>Exempt</strong> from physical terminal sign-in. Punctuality and attendance scores are protected.
        </p>
      </div>
    </div>

    <!-- Attendance Grid or Empty State -->
    <div v-if="filteredAttendance.length > 0 || filteredOfficialDuties.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <!-- Official Duty Assignment Cards -->
      <div
        v-for="(duty, dIdx) in filteredOfficialDuties"
        :key="'duty-' + dIdx"
        class="bg-emerald-50/70 p-5 rounded-xl border border-emerald-200 shadow-sm hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between pb-3 border-b border-emerald-200/80">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:plane-takeoff" class="text-emerald-600" width="18" />
            <span class="font-bold text-emerald-950 text-sm">{{ formatDateRange(duty.start_date, duty.end_date) }}</span>
          </div>
          <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
            Official Duty (Exempt)
          </span>
        </div>

        <div class="mt-3 space-y-1.5 text-xs text-emerald-900">
          <div v-if="duty.destination" class="flex items-center gap-1.5 font-bold">
            <Icon icon="lucide:map-pin" width="14" class="text-emerald-700" />
            <span>{{ duty.destination }}</span>
          </div>
          <div v-if="duty.duty_memo_ref" class="text-emerald-800 font-mono text-[11px]">
            Memo Ref: {{ duty.duty_memo_ref }}
          </div>
          <p v-if="duty.reason" class="text-emerald-800 line-clamp-1 italic text-[11px]">
            "{{ duty.reason }}"
          </p>
          <div class="pt-2 border-t border-emerald-200/70 flex items-center justify-between text-[11px] text-emerald-700">
            <span>Special Authorized Duty</span>
            <span class="font-semibold">Excused Sign-in</span>
          </div>
        </div>
      </div>

      <!-- Physical Attendance Cards -->
      <div
        v-for="(item, index) in filteredAttendance"
        :key="index"
        class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow"
      >
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:calendar" class="text-blue-500" width="18" />
            <span class="font-semibold text-gray-800 text-sm">{{ getDate(item.created_at) }}</span>
          </div>
          <span :class="item.logout_time ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'" class="text-xs px-2.5 py-0.5 rounded-full font-medium">
            {{ item.logout_time ? 'Completed' : 'Active / Unclosed' }}
          </span>
        </div>

        <div class="grid grid-cols-3 gap-2 mt-4 text-center">
          <div class="bg-slate-50 p-2.5 rounded-lg">
            <p class="text-xs text-gray-500 mb-1">Check In</p>
            <p class="font-bold text-sm text-gray-800">{{ getCheckIn(item.login_time) }}</p>
          </div>
          <div class="bg-slate-50 p-2.5 rounded-lg">
            <p class="text-xs text-gray-500 mb-1">Check Out</p>
            <p class="font-bold text-sm text-gray-800">{{ getCheckOut(item.logout_time) }}</p>
          </div>
          <div class="bg-blue-50 p-2.5 rounded-lg">
            <p class="text-xs text-blue-600 mb-1 font-medium">Duration</p>
            <p class="font-bold text-sm text-blue-700">{{ totalTimeSpent(item.login_time, item.logout_time) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white border border-gray-100 rounded-2xl p-12 text-center shadow-sm">
      <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
        <Icon icon="lucide:calendar-x-2" width="32" />
      </div>
      <h3 class="text-lg font-bold text-gray-800 mb-1">No Attendance Records Found</h3>
      <p class="text-sm text-gray-500 max-w-md mx-auto">
        {{ (startDate || endDate) ? 'No logs match the selected date range. Try clearing the filter.' : 'There are no recorded attendance or login records for this staff member yet.' }}
      </p>
    </div>
  </main>

  <SignatureAttendanceModal
    v-model="showAttendanceModal"
    :action="attendanceAction"
    @verified="reloadAttendance"
  />
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'
import moment from "moment"
import { Link, usePage, router } from "@inertiajs/vue3"
import SignatureAttendanceModal from "../../components/Modals/PMS/SignatureAttendanceModal.vue"

const props = defineProps(['user'])
const page = usePage()

const startDate = ref('')
const endDate = ref('')
const showAttendanceModal = ref(false)
const attendanceAction = ref('check-in')

const isCurrentUser = computed(() => {
  return page.props.user?.id === props.user?.id
})

const isCheckedInToday = computed(() => {
  const today = moment().format('YYYY-MM-DD')
  return (props.user?.attendance || []).some(a => {
    const aDate = moment(a.created_at || a.login_time).format('YYYY-MM-DD')
    return aDate === today && a.login_time
  })
})

const isCheckedOutToday = computed(() => {
  const today = moment().format('YYYY-MM-DD')
  return (props.user?.attendance || []).some(a => {
    const aDate = moment(a.created_at || a.login_time).format('YYYY-MM-DD')
    return aDate === today && a.logout_time
  })
})

const openCheckInModal = () => {
  attendanceAction.value = 'check-in'
  showAttendanceModal.value = true
}

const openCheckOutModal = () => {
  attendanceAction.value = 'check-out'
  showAttendanceModal.value = true
}

const reloadAttendance = () => {
  router.reload()
}

const attendanceList = computed(() => {
  return props.user?.attendance || []
})

const filteredAttendance = computed(() => {
  return attendanceList.value.filter(item => {
    if (!startDate.value && !endDate.value) return true
    const itemDate = moment(item.created_at || item.login_time)
    if (startDate.value && itemDate.isBefore(moment(startDate.value).startOf('day'))) {
      return false
    }
    if (endDate.value && itemDate.isAfter(moment(endDate.value).endOf('day'))) {
      return false
    }
    return true
  })
})

const completedSessionsCount = computed(() => {
  return filteredAttendance.value.filter(i => i.login_time && i.logout_time).length
})

const avgHoursPerDay = computed(() => {
  const completed = filteredAttendance.value.filter(i => i.login_time && i.logout_time)
  if (completed.length === 0) return '0 hrs'
  let totalMinutes = 0
  completed.forEach(item => {
    const start = moment(item.login_time)
    const end = moment(item.logout_time)
    totalMinutes += Math.max(0, end.diff(start, 'minutes'))
  })
  const avgMins = Math.round(totalMinutes / completed.length)
  const hrs = Math.floor(avgMins / 60)
  const mins = avgMins % 60
  return `${hrs}h ${mins}m`
})

function formatDate(dateString) {
  return moment(dateString).format('h:mm A')
}

const getCheckIn = (date) => {
  return date ? formatDate(date) : "NIL"
}

const getCheckOut = (date) => {
  return date ? formatDate(date) : "--"
}

const totalTimeSpent = (login_time, logout_time) => {
  if (login_time && logout_time) {
    const start = moment(login_time)
    const end = moment(logout_time)
    const differenceInHours = end.diff(start, 'hours')
    const differenceInMinutes = end.diff(start, 'minutes') % 60
    return `${differenceInHours}h ${differenceInMinutes}m`
  }
  return "--"
}

const getDate = (date) => {
  return moment(date).format('ddd, MMM Do, YYYY')
}

const generateAttendanceReport = () => {
  if (props.user?.id) {
    let url = `/dashboard/pms/report/attendance/${props.user.id}?`
    if (startDate.value) url += `start_date=${startDate.value}&`
    if (endDate.value) url += `end_date=${endDate.value}&`
    window.open(url, '_blank')
  }
}

const filteredOfficialDuties = computed(() => {
  const leaves = props.user?.leave || []
  return leaves.filter(l => {
    if (l.leave_type !== 'official_assignment' || l.status !== 'approved') return false
    if (!startDate.value && !endDate.value) return true
    const s = moment(l.start_date)
    const e = moment(l.end_date)
    if (startDate.value && e.isBefore(moment(startDate.value).startOf('day'))) return false
    if (endDate.value && s.isAfter(moment(endDate.value).endOf('day'))) return false
    return true
  })
})

const formatDateRange = (start, end) => {
  if (!start) return 'Official Duty'
  if (!end || start === end) return moment(start).format('DD MMM YYYY')
  return `${moment(start).format('DD MMM')} - ${moment(end).format('DD MMM YYYY')}`
}
</script>
