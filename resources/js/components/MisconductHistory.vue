<template>
  <div class="space-y-6">
    <!-- Header / Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-amber-50 text-amber-700 rounded-lg">
          <Icon icon="lucide:file-question" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Official Queries</p>
          <p class="text-xl font-bold text-gray-800">{{ queriesList.length }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-red-50 text-red-600 rounded-lg">
          <Icon icon="lucide:alert-triangle" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Misconduct Reports</p>
          <p class="text-xl font-bold text-gray-800">{{ misconductList.length }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
          <Icon icon="lucide:clock" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Awaiting Action</p>
          <p class="text-xl font-bold text-gray-800">{{ pendingQueriesCount + underReviewMisconductCount }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-4">
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
          <Icon icon="lucide:shield-check" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Disciplinary Standing</p>
          <span
            v-if="queriesList.length === 0 && misconductList.length === 0"
            class="inline-block mt-0.5 px-2 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800"
          >
            Clean Record
          </span>
          <span
            v-else
            class="inline-block mt-0.5 px-2 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800"
          >
            {{ queriesList.length + misconductList.length }} Entry Logged
          </span>
        </div>
      </div>
    </div>

    <!-- Official Disciplinary Queries Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs p-5 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Official Disciplinary Queries (PSR)</h2>
          <p class="text-xs text-gray-500">Formal queries issued by superiors, written representations, and administrative verdicts</p>
        </div>
      </div>

      <div v-if="queriesList.length > 0" class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4">Query Ref</th>
              <th class="py-3.5 px-4">Issuing Superior</th>
              <th class="py-3.5 px-4">Subject & Category</th>
              <th class="py-3.5 px-4">Response Deadline</th>
              <th class="py-3.5 px-4">Verdict / Status</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="q in queriesList" :key="'qry-' + q.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4 font-mono text-xs font-bold text-gray-900">
                {{ q.query_ref }}
                <span class="block text-[10px] text-gray-400 font-sans font-normal">{{ formatDate(q.created_at) }}</span>
              </td>
              <td class="py-3.5 px-4 text-xs font-semibold text-gray-800">
                {{ q.issuer?.first_name }} {{ q.issuer?.last_name }}
              </td>
              <td class="py-3.5 px-4 text-xs max-w-xs">
                <p class="font-bold text-gray-900">{{ q.subject }}</p>
                <p class="text-gray-500 text-[11px]">{{ q.incident_category }}</p>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <span :class="isOverdue(q.deadline_at) ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-900'" class="px-2 py-0.5 rounded-full font-bold inline-flex items-center gap-1 text-[11px]">
                  <Icon icon="lucide:clock" width="12" />
                  <span>{{ formatDeadline(q.deadline_at) }}</span>
                </span>
              </td>
              <td class="py-3.5 px-4">
                <span :class="getVerdictBadge(q.verdict)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatVerdict(q.verdict) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    v-if="isTargetUser && q.verdict === 'pending_response'"
                    @click="openRespondModal(q)"
                    class="px-3 py-1 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-2xs"
                  >
                    Respond
                  </button>
                  <button
                    v-if="canAdjudicate && (q.verdict === 'under_review' || q.verdict === 'pending_response')"
                    @click="openVerdictModal(q)"
                    class="px-2.5 py-1 text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg"
                  >
                    Verdict
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-8 border border-dashed border-gray-200 rounded-xl">
        <Icon icon="lucide:check-circle" width="32" class="mx-auto text-emerald-500 mb-1" />
        <h4 class="text-sm font-bold text-gray-700">No Queries Issued</h4>
        <p class="text-xs text-gray-400 max-w-sm mx-auto">This officer has no recorded official disciplinary queries under Public Service Rules.</p>
      </div>
    </div>

    <!-- Misconduct & SERVICOM Reports Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs p-5 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-bold text-gray-900">Misconduct & Incident Reports</h2>
          <p class="text-xs text-gray-500">Confidential log of reported misconduct, harassment, or compliance infractions</p>
        </div>
      </div>

      <div v-if="misconductList.length > 0" class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4">Incident Category</th>
              <th class="py-3.5 px-4">Incident Date</th>
              <th class="py-3.5 px-4">Reported By</th>
              <th class="py-3.5 px-4">Complaint Description</th>
              <th class="py-3.5 px-4">Supporting Document</th>
              <th class="py-3.5 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in misconductList" :key="'misc-' + item.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4 font-semibold text-gray-900">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                  <Icon icon="lucide:alert-circle" width="13" />
                  {{ item.ticket }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-xs text-gray-600">
                {{ formatDate(item.incident_date || item.created_at) }}
              </td>
              <td class="py-3.5 px-4 text-xs">
                <div v-if="item.reporter" class="font-medium text-gray-800">
                  {{ item.reporter.first_name }} {{ item.reporter.last_name }}
                  <span class="block text-[11px] text-gray-400">{{ item.reporter.email }}</span>
                </div>
                <span v-else class="text-gray-400 italic">Confidential / Unit Head</span>
              </td>
              <td class="py-3.5 px-4 text-xs text-gray-700 max-w-xs">
                <p class="line-clamp-2" :title="item.complaint">{{ item.complaint || 'No detailed note provided.' }}</p>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <a
                  v-if="item.file"
                  :href="item.file"
                  target="_blank"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                >
                  <Icon icon="lucide:paperclip" width="13" />
                  <span>View Evidence</span>
                </a>
                <span v-else class="text-gray-400 italic">None Attached</span>
              </td>
              <td class="py-3.5 px-4">
                <span :class="getStatusBadge(item.status)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatStatus(item.status) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-8 border border-dashed border-gray-200 rounded-xl">
        <Icon icon="lucide:shield-check" width="32" class="mx-auto text-emerald-500 mb-1" />
        <h4 class="text-sm font-bold text-gray-700">No Misconduct Reports Recorded</h4>
        <p class="text-xs text-gray-400 max-w-sm mx-auto">This staff member has a clean disciplinary record with zero reported compliance or misconduct infractions.</p>
      </div>
    </div>

    <!-- Modals -->
    <RespondQueryModal v-model="showRespondModal" :query="selectedQuery" />
    <QueryVerdictModal v-model="showVerdictModal" :query="selectedQuery" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import moment from 'moment'
import RespondQueryModal from './Modals/PMS/RespondQueryModal.vue'
import QueryVerdictModal from './Modals/PMS/QueryVerdictModal.vue'

const props = defineProps({
  user: { type: Object, default: () => ({}) }
})

const page = usePage()

const showRespondModal = ref(false)
const showVerdictModal = ref(false)
const selectedQuery = ref(null)

const isTargetUser = computed(() => {
  return page.props.user?.id === props.user?.id
})

const userRoles = computed(() => page.props.user_roles || [])
const canAdjudicate = computed(() => {
  return userRoles.value.some(r => {
    const role = (r || '').toLowerCase()
    return ['super user', 'super admin', 'admin', 'director', 'accountant-general', 'head of division', 'head of unit'].includes(role)
  })
})

const misconductList = computed(() => {
  return props.user?.misconducts || []
})

const queriesList = computed(() => {
  return props.user?.received_queries || []
})

const underReviewMisconductCount = computed(() => {
  return misconductList.value.filter(m => m.status === 'submitted' || m.status === 'under_investigation').length
})

const pendingQueriesCount = computed(() => {
  return queriesList.value.filter(q => q.verdict === 'pending_response' || q.verdict === 'under_review').length
})

const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  return moment(dateStr).format('DD MMM YYYY')
}

const isOverdue = (deadlineStr) => {
  if (!deadlineStr) return false
  return moment().isAfter(moment(deadlineStr))
}

const formatDeadline = (deadlineStr) => {
  if (!deadlineStr) return '—'
  const end = moment(deadlineStr)
  const now = moment()
  if (now.isAfter(end)) {
    return `Expired (${end.fromNow()})`
  }
  return `Due ${now.to(end)}`
}

const formatStatus = (status) => {
  if (!status) return 'Submitted'
  return status.replace(/_/g, ' ')
}

const formatVerdict = (v) => {
  switch (v) {
    case 'accepted':
      return 'Exonerated'
    case 'cautioned':
      return 'Cautioned'
    case 'escalated_to_dc':
      return 'Escalated to DC'
    case 'under_review':
      return 'Under Review'
    case 'pending_response':
      return 'Pending Defense'
    default:
      return v || 'Pending'
  }
}

const getVerdictBadge = (v) => {
  switch (v) {
    case 'accepted':
      return 'bg-emerald-100 text-emerald-800'
    case 'cautioned':
      return 'bg-amber-100 text-amber-800'
    case 'escalated_to_dc':
      return 'bg-red-100 text-red-800 font-bold'
    case 'under_review':
      return 'bg-purple-100 text-purple-800'
    case 'pending_response':
      return 'bg-blue-100 text-blue-800 animate-pulse'
    default:
      return 'bg-slate-100 text-slate-800'
  }
}

const getStatusBadge = (status) => {
  switch ((status || '').toLowerCase()) {
    case 'resolved':
      return 'bg-emerald-100 text-emerald-800'
    case 'under_investigation':
    case 'submitted':
      return 'bg-amber-100 text-amber-800'
    case 'dismissed':
      return 'bg-gray-100 text-gray-600'
    default:
      return 'bg-red-100 text-red-800'
  }
}

const openRespondModal = (q) => {
  selectedQuery.value = q
  showRespondModal.value = true
}

const openVerdictModal = (q) => {
  selectedQuery.value = q
  showVerdictModal.value = true
}
</script>
