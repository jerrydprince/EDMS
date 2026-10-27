<template>
  <main class="w-full px-4 sm:px-6 py-6 space-y-6 pb-28">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-gray-500">
      <Link href="/dashboard/pms" class="hover:text-blue-600 flex items-center gap-1.5 transition-colors">
        <Icon icon="lucide:arrow-left" width="16" />
        <span>PMS Hub</span>
      </Link>
      <Icon icon="lucide:chevron-right" width="14" class="text-gray-300" />
      <span class="font-semibold text-gray-800">
        {{ user.first_name }} {{ user.last_name }}'s Profile & Scorecard
      </span>
    </div>

    <!-- User Header Profile Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <!-- Avatar & Details -->
        <div class="flex items-start sm:items-center gap-5">
          <div class="relative shrink-0">
            <img
              v-if="user.profile_image"
              :src="page.props.base + '/' + user.profile_image"
              alt="Profile"
              class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-100 shadow-sm"
            />
            <div
              v-else
              class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-black text-2xl shadow-md uppercase"
            >
              {{ (user.first_name?.[0] || '') + (user.last_name?.[0] || '') }}
            </div>
            <span
              :class="user.is_on_leave == 1 ? 'bg-amber-500' : (user.is_on_official_assignment == 1 ? 'bg-indigo-500' : 'bg-emerald-500')"
              class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white"
              :title="user.is_on_leave == 1 ? 'On Leave' : (user.is_on_official_assignment == 1 ? 'On Official Assignment' : 'Active Duty')"
            ></span>
          </div>

          <div>
            <div class="flex items-center gap-3 flex-wrap">
              <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                {{ user.first_name }} {{ user.last_name }}
              </h1>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                {{ user.roles?.[0]?.name || 'Staff' }}
              </span>
              <span
                v-if="user.is_on_leave == 1"
                class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200"
              >
                On Leave
              </span>
              <span
                v-else-if="user.is_on_official_assignment == 1"
                class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200"
              >
                Official Assignment
              </span>
              <span
                v-else
                class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200"
              >
                On Duty
              </span>
            </div>

            <!-- Branch & Metadata -->
            <div class="flex items-center gap-4 flex-wrap mt-2 text-xs font-medium text-gray-500">
              <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-lg border border-gray-100">
                <Icon icon="lucide:building" width="14" class="text-gray-400" />
                <span>{{ getBranchName() }}</span>
              </div>
              <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-lg border border-gray-100">
                <Icon icon="lucide:mail" width="14" class="text-gray-400" />
                <span>{{ user.email }}</span>
              </div>
              <div v-if="user.phone" class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1 rounded-lg border border-gray-100">
                <Icon icon="lucide:phone" width="14" class="text-gray-400" />
                <span>{{ user.phone }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Management Actions -->
        <div class="flex items-center flex-wrap gap-2.5 self-start lg:self-center shrink-0">
          <button
            @click="generateReport"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-blue-700 bg-blue-50/80 hover:bg-blue-100 border border-blue-200 rounded-xl shadow-sm transition-all"
            title="Generate & Download Official PDF Scorecard"
          >
            <Icon icon="lucide:file-text" width="15" />
            <span>Generate Report</span>
          </button>

          <Link
            :href="'/dashboard/pms-attendance/' + user.id"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-slate-50 border border-gray-200 rounded-xl shadow-sm transition-all"
          >
            <Icon icon="lucide:calendar-clock" width="15" />
            <span>Attendance Logs</span>
          </Link>

          <!-- Self-Service Apply Leave Button for the Staff Member -->
          <button
            v-if="isCurrentUser && user.is_on_leave != 1 && user.is_on_official_assignment != 1"
            @click="leaveModal = true"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl shadow-sm transition-all"
          >
            <Icon icon="lucide:plane-takeoff" width="15" />
            <span>Apply for Leave / Duty</span>
          </button>

          <!-- Leave Actions (Supervisor / Admin Only) -->
          <button
            v-if="isStillOnLeave() && (user.is_on_leave == 1 || user.is_on_official_assignment == 1) && !isRegularUserOnly"
            @click="cancelLeaveModal = true"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition-all"
          >
            <Icon icon="lucide:x-circle" width="15" />
            <span>Cancel Leave / Duty</span>
          </button>

          <button
            v-else-if="!isRegularUserOnly && !isCurrentUser"
            @click="leaveModal = true"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white hover:bg-slate-50 border border-gray-200 rounded-xl shadow-sm transition-all"
          >
            <Icon icon="lucide:pause-circle" width="15" />
            <span>Add Leave / Duty</span>
          </button>

          <!-- Issue Official Query (Superior Only) -->
          <button
            v-if="canIssueQuery"
            @click="issueQueryModal = true"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl transition-all"
          >
            <Icon icon="lucide:file-question" width="15" />
            <span>Issue Query</span>
          </button>

          <!-- Report to SERVICOM -->
          <button
            @click="reportModal = true"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl transition-all"
          >
            <Icon icon="lucide:shield-alert" width="15" />
            <span>Report to SERVICOM</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 p-1.5 bg-slate-100 rounded-2xl w-fit">
      <button
        @click="activebutton = 1"
        :class="activebutton === 1 ? 'bg-white text-blue-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm transition-all"
      >
        <Icon icon="lucide:gauge" width="16" />
        <span>KPIs & Scorecard</span>
      </button>

      <button
        @click="activebutton = 2"
        :class="activebutton === 2 ? 'bg-white text-blue-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm transition-all"
      >
        <Icon icon="lucide:check-square" width="16" />
        <span>Assigned Tasks</span>
        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
          {{ props.user?.task?.length || 0 }}
        </span>
      </button>

      <button
        @click="activebutton = 3"
        :class="activebutton === 3 ? 'bg-white text-blue-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm transition-all"
      >
        <Icon icon="lucide:calendar-range" width="16" />
        <span>Leave & Duty Records</span>
        <span
          v-if="props.user?.leave?.length"
          class="px-2 py-0.5 rounded-full text-xs font-bold"
          :class="hasPendingLeave ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-gray-200 text-gray-700'"
        >
          {{ props.user?.leave?.length }}
        </span>
      </button>

      <button
        v-if="!isRegularUserOnly || isCurrentUser"
        @click="activebutton = 4"
        :class="activebutton === 4 ? 'bg-white text-blue-700 shadow-sm font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm transition-all"
      >
        <Icon icon="lucide:shield-alert" width="16" />
        <span>Disciplinary & Queries</span>
        <span
          v-if="(props.user?.misconducts?.length || 0) + (props.user?.received_queries?.length || 0)"
          class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"
        >
          {{ (props.user?.misconducts?.length || 0) + (props.user?.received_queries?.length || 0) }}
        </span>
      </button>
    </div>

    <!-- Tab Contents -->
    <div>
      <KPI
        v-if="activebutton === 1"
        :ratings="props.ratings"
        :user="props.user"
        :taskCompletionRate="props.taskCompletionRate"
      />
      <Tasks v-else-if="activebutton === 2" :task="props.user?.task" />
      <LeaveHistory v-else-if="activebutton === 3" :user="props.user" />
      <MisconductHistory v-else-if="activebutton === 4" :user="props.user" />
    </div>
  </main>

  <!-- Modals -->
  <ReportMisconduct v-model="reportModal" :user="props.user" />
  <IssueQueryModal v-model="issueQueryModal" :user="props.user" />
  <AddLeave v-model="leaveModal" :user="props.user" />
  <CancelLeave v-model="cancelLeaveModal" :user="props.user" />
</template>

<script setup>
import { ref, defineProps, computed } from "vue"
import { usePage, Link } from "@inertiajs/vue3"
import KPI from "../../components/KPI.vue"
import Tasks from "../../components/Tasks.vue"
import LeaveHistory from "../../components/LeaveHistory.vue"
import MisconductHistory from "../../components/MisconductHistory.vue"
import ReportMisconduct from '../../components/Modals/PMS/ReportMisconduct.vue'
import IssueQueryModal from '../../components/Modals/PMS/IssueQueryModal.vue'
import AddLeave from '../../components/Modals/PMS/AddLeave.vue'
import CancelLeave from "../../components/Modals/PMS/CancelLeave.vue"

const props = defineProps(['user', 'taskCompletionRate', 'ratings'])
const page = usePage()

const reportModal = ref(false)
const issueQueryModal = ref(false)
const leaveModal = ref(false)
const cancelLeaveModal = ref(false)
const activebutton = ref(1)

const roles = computed(() => page.props.roles || [])
const user_roles = computed(() => page.props.user_roles || [])

const isRegularUserOnly = computed(() => {
  if (user_roles.value.length === 0) return true
  return user_roles.value.every(r => r?.toLowerCase() === 'regular user')
})

const canIssueQuery = computed(() => {
  if (isCurrentUser.value) return false
  const validSupervisorRoles = [
    'super user',
    'super admin',
    'admin',
    'accountant-general',
    'agf',
    'director',
    'deputy director',
    'assistant director'
  ]
  const currentRoles = (user_roles.value || []).concat(roles.value || []).concat(page.props.user?.roles || [])
  return currentRoles.some(r => {
    const roleName = typeof r === 'string' ? r : (r?.name || '')
    return validSupervisorRoles.includes(roleName.toLowerCase())
  })
})

const isCurrentUser = computed(() => {
  return page.props.user?.id === props.user?.id
})

const hasPendingLeave = computed(() => {
  return (props.user?.leave || []).some(l => l.status === 'pending')
})

const generateReport = () => {
  if (props.user?.id) {
    window.open(`/dashboard/pms/report/user/${props.user.id}`, '_blank');
  }
}

const getBranchName = () => {
  if (props.user?.unit) {
    return props.user.unit.title || props.user.unit.name
  }
  if (props.user?.division) {
    return props.user.division.title || props.user.division.name
  }
  if (props.user?.department) {
    return props.user.department.title || props.user.department.name
  }
  return 'Headquarters'
}

const isStillOnLeave = () => {
  if (props.user?.leave && props.user.leave.length > 0) {
    const leaveObj = props.user.leave[0]
    const dateStr = typeof leaveObj === 'object' ? leaveObj.end_date : leaveObj
    if (!dateStr) return false
    const end_date = new Date(dateStr)
    const today = new Date()

    today.setHours(0, 0, 0, 0)
    end_date.setHours(0, 0, 0, 0)

    return today.getTime() <= end_date.getTime()
  }
  return false
}
</script>
