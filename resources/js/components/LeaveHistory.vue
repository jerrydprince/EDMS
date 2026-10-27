<template>
  <div class="space-y-6">
    <!-- Header / Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
          <Icon icon="lucide:calendar-range" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Requests</p>
          <p class="text-xl font-bold text-gray-800">{{ leavesList.length }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
          <Icon icon="lucide:plane-takeoff" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Official Assignments</p>
          <p class="text-xl font-bold text-gray-800">{{ officialAssignmentsCount }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
          <Icon icon="lucide:clock-3" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Pending Approvals</p>
          <p class="text-xl font-bold text-gray-800">{{ pendingCount }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
          <Icon icon="lucide:user-check" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Current Status</p>
          <span
            v-if="user?.is_on_official_assignment == 1"
            class="inline-block mt-0.5 px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800"
          >
            On Official Duty
          </span>
          <span
            v-else-if="user?.is_on_leave == 1"
            class="inline-block mt-0.5 px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800"
          >
            On Leave
          </span>
          <span
            v-else
            class="inline-block mt-0.5 px-2 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800"
          >
            Active on Duty
          </span>
        </div>
      </div>
    </div>

    <!-- Supervisor Pending Approval Banner / Action Section -->
    <div v-if="canApprove && pendingLeaves.length > 0" class="bg-amber-50/70 border border-amber-200 rounded-2xl p-5 shadow-sm space-y-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-amber-900 font-bold">
          <Icon icon="lucide:alert-circle" width="20" class="text-amber-600" />
          <span>Pending Leave & Duty Authorizations Awaiting Action</span>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 bg-amber-200 text-amber-900 rounded-full">
          {{ pendingLeaves.length }} Pending
        </span>
      </div>

      <div class="overflow-x-auto bg-white rounded-xl border border-amber-200/80 shadow-sm">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3 px-4">Staff Member</th>
              <th class="py-3 px-4">Category</th>
              <th class="py-3 px-4">Schedule</th>
              <th class="py-3 px-4">Relief Officer</th>
              <th class="py-3 px-4">Destination / Memo</th>
              <th class="py-3 px-4 text-right">Decide</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="req in pendingLeaves" :key="req.id" class="hover:bg-amber-50/40">
              <td class="py-3 px-4 font-semibold text-gray-900">
                {{ req.user?.first_name || user?.first_name }} {{ req.user?.last_name || user?.last_name }}
              </td>
              <td class="py-3 px-4">
                <span :class="getTypeBadge(req.leave_type)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatType(req.leave_type) }}
                </span>
              </td>
              <td class="py-3 px-4 text-xs text-gray-600">
                <span class="font-medium text-gray-800">{{ formatDate(req.start_date) }}</span> to
                <span class="font-medium text-gray-800">{{ formatDate(req.end_date) }}</span>
                <span class="text-gray-400 block">({{ calcDuration(req.start_date, req.end_date) }} days)</span>
              </td>
              <td class="py-3 px-4 text-xs text-gray-700">
                <span v-if="req.relief_user" class="font-medium text-blue-700">
                  {{ req.relief_user.first_name }} {{ req.relief_user.last_name }}
                </span>
                <span v-else class="text-gray-400 italic">None Designated</span>
              </td>
              <td class="py-3 px-4 text-xs text-gray-600">
                <div v-if="req.destination || req.duty_memo_ref">
                  <p v-if="req.destination" class="font-semibold text-gray-800">{{ req.destination }}</p>
                  <p v-if="req.duty_memo_ref" class="text-gray-400 font-mono">{{ req.duty_memo_ref }}</p>
                </div>
                <div v-else-if="req.reason" class="truncate max-w-xs text-gray-600">
                  {{ req.reason }}
                </div>
                <span v-else class="text-gray-400 italic">—</span>
              </td>
              <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-2">
                  <button
                    @click="handleApprove(req.id)"
                    class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-all"
                  >
                    Approve
                  </button>
                  <button
                    @click="handleReject(req.id)"
                    class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-all"
                  >
                    Reject
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Complete Leave & Duty History Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Recorded Leave & Official Duty Log</h2>
          <p class="text-xs text-gray-500">Historical records of annual leave, casual absence, and official duty assignments</p>
        </div>
      </div>

      <div v-if="leavesList.length > 0" class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4">Type</th>
              <th class="py-3.5 px-4">Duration & Schedule</th>
              <th class="py-3.5 px-4">Relief Officer</th>
              <th class="py-3.5 px-4">Duty Details / Reason</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4">Approved By</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in leavesList" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-2">
                  <span :class="getTypeBadge(item.leave_type)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                    {{ formatType(item.leave_type) }}
                  </span>
                </div>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <p class="font-semibold text-gray-900">{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</p>
                <p class="text-gray-400 mt-0.5">{{ calcDuration(item.start_date, item.end_date) }} days</p>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <div v-if="item.relief_user" class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                    {{ item.relief_user.first_name?.[0] || 'U' }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-800">{{ item.relief_user.first_name }} {{ item.relief_user.last_name }}</p>
                    <p class="text-[10px] text-gray-400">{{ item.relief_user.email }}</p>
                  </div>
                </div>
                <span v-else class="text-gray-400 italic">None</span>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <div v-if="item.destination || item.duty_memo_ref" class="space-y-0.5">
                  <p v-if="item.destination" class="font-semibold text-emerald-800 flex items-center gap-1">
                    <Icon icon="lucide:map-pin" width="12" />
                    {{ item.destination }}
                  </p>
                  <p v-if="item.duty_memo_ref" class="text-gray-500 font-mono text-[11px]">
                    Ref: {{ item.duty_memo_ref }}
                  </p>
                </div>
                <p v-else-if="item.reason" class="text-gray-700 max-w-sm">
                  {{ item.reason }}
                </p>
                <span v-else class="text-gray-400 italic">—</span>
              </td>
              <td class="py-3.5 px-4">
                <span :class="getStatusBadge(item.status)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ item.status || 'Approved' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-xs text-gray-600">
                <div v-if="item.approver" class="font-medium text-gray-800">
                  {{ item.approver.first_name }} {{ item.approver.last_name }}
                </div>
                <span v-else-if="item.status === 'pending'" class="text-amber-600 font-semibold italic">
                  Awaiting Review
                </span>
                <span v-else class="text-gray-400 italic">System / Direct</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-12 border border-dashed border-gray-200 rounded-xl">
        <Icon icon="lucide:calendar-x-2" width="40" class="mx-auto text-gray-300 mb-2" />
        <h3 class="text-base font-bold text-gray-700">No Leave or Duty Records</h3>
        <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
          No leaves or official duty assignments have been recorded for this staff member yet.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import moment from 'moment'

const props = defineProps({
  user: { type: Object, default: () => ({}) }
})

const page = usePage()
const toast = useToast()

const userRoles = computed(() => page.props.user_roles || [])
const canApprove = computed(() => {
  return userRoles.value.some(r => {
    const role = (r || '').toLowerCase()
    return ['super user', 'super admin', 'admin', 'director', 'accountant-general', 'head of division', 'head of unit'].includes(role)
  })
})

const leavesList = computed(() => {
  return props.user?.leave || []
})

const pendingLeaves = computed(() => {
  return leavesList.value.filter(l => l.status === 'pending')
})

const officialAssignmentsCount = computed(() => {
  return leavesList.value.filter(l => l.leave_type === 'official_assignment').length
})

const pendingCount = computed(() => {
  return pendingLeaves.value.length
})

const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  return moment(dateStr).format('DD MMM YYYY')
}

const calcDuration = (start, end) => {
  if (!start || !end) return 0
  const s = moment(start)
  const e = moment(end)
  return Math.max(1, e.diff(s, 'days') + 1)
}

const formatType = (type) => {
  if (!type) return 'Annual Leave'
  return type.replace(/_/g, ' ')
}

const getTypeBadge = (type) => {
  switch (type) {
    case 'official_assignment':
      return 'bg-emerald-100 text-emerald-800 border border-emerald-200'
    case 'annual':
      return 'bg-blue-100 text-blue-800 border border-blue-200'
    case 'sick':
      return 'bg-rose-100 text-rose-800 border border-rose-200'
    case 'casual':
      return 'bg-amber-100 text-amber-800 border border-amber-200'
    case 'maternity':
      return 'bg-purple-100 text-purple-800 border border-purple-200'
    case 'study':
      return 'bg-indigo-100 text-indigo-800 border border-indigo-200'
    default:
      return 'bg-slate-100 text-slate-800 border border-slate-200'
  }
}

const getStatusBadge = (status) => {
  switch ((status || '').toLowerCase()) {
    case 'approved':
      return 'bg-emerald-100 text-emerald-800'
    case 'pending':
      return 'bg-amber-100 text-amber-800 animate-pulse'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    case 'cancelled':
      return 'bg-gray-100 text-gray-700'
    default:
      return 'bg-blue-100 text-blue-800'
  }
}

const handleApprove = (leaveId) => {
  router.post('/dashboard/approve-leave', { leave_id: leaveId }, {
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Request Approved',
        detail: 'The leave / assignment request has been approved successfully.',
        life: 3000
      })
    }
  })
}

const handleReject = (leaveId) => {
  const reason = prompt('Please enter reason for rejection (optional):')
  router.post('/dashboard/reject-leave', { leave_id: leaveId, comment: reason }, {
    onSuccess: () => {
      toast.add({
        severity: 'info',
        summary: 'Request Rejected',
        detail: 'The leave / assignment request has been rejected.',
        life: 3000
      })
    }
  })
}
</script>
