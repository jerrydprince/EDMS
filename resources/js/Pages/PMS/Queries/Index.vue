<template>
  <main class="w-full px-4 sm:px-6 py-6 space-y-6">
    <!-- Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <div class="p-2.5 bg-amber-50 text-amber-700 rounded-xl border border-amber-100">
            <Icon icon="lucide:file-question" width="24" />
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Official Disciplinary Queries</h1>
            <p class="text-xs text-gray-500 mt-0.5">Civil Service Queries, Formal Representations & Administrative Adjudication</p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          v-if="canIssue"
          @click="showIssueModal = true"
          class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:plus-circle" width="16" />
          <span>Issue Disciplinary Query</span>
        </button>
      </div>
    </div>

    <!-- Active Navigation Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 rounded-2xl w-fit">
      <button
        @click="activeTab = 'received'"
        :class="activeTab === 'received' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all"
      >
        <Icon icon="lucide:inbox" width="15" />
        <span>Queries Issued to Me</span>
        <span v-if="receivedQueries.length" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
          {{ receivedQueries.length }}
        </span>
      </button>

      <button
        v-if="canIssue"
        @click="activeTab = 'issued'"
        :class="activeTab === 'issued' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all"
      >
        <Icon icon="lucide:send" width="15" />
        <span>Queries I Have Issued</span>
        <span v-if="issuedQueries.length" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-800">
          {{ issuedQueries.length }}
        </span>
      </button>

      <button
        v-if="props.isHrAdmin"
        @click="activeTab = 'all'"
        :class="activeTab === 'all' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-gray-600 hover:text-gray-900 font-medium'"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all"
      >
        <Icon icon="lucide:shield" width="15" />
        <span>Master HR Registry</span>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
          {{ allQueries.length }}
        </span>
      </button>
    </div>

    <!-- Tab 1: Queries Issued to Me -->
    <div v-if="activeTab === 'received'" class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden">
      <div v-if="receivedQueries.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4">Query Ref & Date</th>
              <th class="py-3.5 px-4">Issuing Superior</th>
              <th class="py-3.5 px-4">Subject & Allegations</th>
              <th class="py-3.5 px-4">Response Deadline</th>
              <th class="py-3.5 px-4">Status / Verdict</th>
              <th class="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="q in receivedQueries" :key="q.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4 font-mono text-xs font-bold text-gray-900">
                {{ q.query_ref }}
                <span class="block text-[10px] text-gray-400 font-sans font-normal">{{ formatDate(q.created_at) }}</span>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <p class="font-bold text-gray-900">{{ q.issuer?.first_name }} {{ q.issuer?.last_name }}</p>
                <p class="text-[10px] text-gray-400">{{ q.issuer?.department?.title || 'OAGF' }}</p>
              </td>
              <td class="py-3.5 px-4 text-xs max-w-xs">
                <p class="font-semibold text-gray-800">{{ q.subject }}</p>
                <p class="text-[11px] text-amber-800 font-medium mt-0.5">{{ q.incident_category }}</p>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <span :class="isOverdue(q.deadline_at) ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-900'" class="px-2.5 py-1 rounded-full font-bold inline-flex items-center gap-1">
                  <Icon icon="lucide:clock" width="13" />
                  <span>{{ formatDeadline(q.deadline_at) }}</span>
                </span>
              </td>
              <td class="py-3.5 px-4">
                <span :class="getVerdictBadge(q.verdict)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatVerdict(q.verdict) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right">
                <button
                  v-if="q.verdict === 'pending_response'"
                  @click="openRespondModal(q)"
                  class="px-3.5 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-2xs transition-all"
                >
                  Write Representation
                </button>
                <span v-else class="text-xs text-gray-500 font-medium">Defense Submitted</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-16">
        <Icon icon="lucide:check-circle" width="44" class="mx-auto text-emerald-500 mb-3" />
        <h3 class="text-lg font-bold text-gray-800">No Disciplinary Queries on Record</h3>
        <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">
          You currently have no outstanding or pending official queries issued to you.
        </p>
      </div>
    </div>

    <!-- Tab 2: Queries I Have Issued -->
    <div v-else-if="activeTab === 'issued'" class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden">
      <div v-if="issuedQueries.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4">Query Ref</th>
              <th class="py-3.5 px-4">Subordinate Recipient</th>
              <th class="py-3.5 px-4">Subject & Grounds</th>
              <th class="py-3.5 px-4">Response Status</th>
              <th class="py-3.5 px-4">Current Verdict</th>
              <th class="py-3.5 px-4 text-right">Adjudication</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="q in issuedQueries" :key="q.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4 font-mono text-xs font-bold text-gray-900">
                {{ q.query_ref }}
                <span class="block text-[10px] text-gray-400 font-sans font-normal">{{ formatDate(q.created_at) }}</span>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <p class="font-bold text-gray-900">{{ q.recipient?.first_name }} {{ q.recipient?.last_name }}</p>
                <p class="text-[10px] text-gray-400">{{ q.recipient?.email }}</p>
              </td>
              <td class="py-3.5 px-4 text-xs max-w-xs">
                <p class="font-semibold text-gray-800">{{ q.subject }}</p>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ q.incident_category }}</p>
              </td>
              <td class="py-3.5 px-4 text-xs">
                <span v-if="q.responded_at" class="text-emerald-700 font-bold flex items-center gap-1">
                  <Icon icon="lucide:check-check" width="14" />
                  <span>Responded ({{ formatDate(q.responded_at) }})</span>
                </span>
                <span v-else-if="isOverdue(q.deadline_at)" class="text-red-600 font-bold flex items-center gap-1">
                  <Icon icon="lucide:alert-circle" width="14" />
                  <span>Deadline Expired</span>
                </span>
                <span v-else class="text-amber-600 font-medium">Awaiting Defense ({{ formatDeadline(q.deadline_at) }})</span>
              </td>
              <td class="py-3.5 px-4">
                <span :class="getVerdictBadge(q.verdict)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatVerdict(q.verdict) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right">
                <button
                  @click="openVerdictModal(q)"
                  class="px-3 py-1.5 text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-all"
                >
                  Review & Verdict
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-16">
        <Icon icon="lucide:send" width="44" class="mx-auto text-gray-300 mb-3" />
        <h3 class="text-lg font-bold text-gray-800">No Queries Issued</h3>
        <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">
          You have not issued any disciplinary queries to subordinate officers.
        </p>
      </div>
    </div>

    <!-- Tab 3: Master HR Registry (Privileged Only) -->
    <div v-else-if="activeTab === 'all' && props.isHrAdmin" class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden">
      <div v-if="allQueries.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4">Query Ref</th>
              <th class="py-3.5 px-4">Issuing Superior</th>
              <th class="py-3.5 px-4">Subordinate</th>
              <th class="py-3.5 px-4">Subject</th>
              <th class="py-3.5 px-4">Category</th>
              <th class="py-3.5 px-4">Verdict</th>
              <th class="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="q in allQueries" :key="q.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4 font-mono text-xs font-bold text-gray-900">{{ q.query_ref }}</td>
              <td class="py-3.5 px-4 text-xs font-semibold">{{ q.issuer?.first_name }} {{ q.issuer?.last_name }}</td>
              <td class="py-3.5 px-4 text-xs text-gray-800">{{ q.recipient?.first_name }} {{ q.recipient?.last_name }}</td>
              <td class="py-3.5 px-4 text-xs">{{ q.subject }}</td>
              <td class="py-3.5 px-4 text-xs text-gray-500">{{ q.incident_category }}</td>
              <td class="py-3.5 px-4">
                <span :class="getVerdictBadge(q.verdict)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatVerdict(q.verdict) }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right">
                <button
                  @click="openVerdictModal(q)"
                  class="px-2.5 py-1 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg"
                >
                  View Case
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- Modals -->
  <IssueQueryModal v-model="showIssueModal" />
  <RespondQueryModal v-model="showRespondModal" :query="selectedQuery" />
  <QueryVerdictModal v-model="showVerdictModal" :query="selectedQuery" />
</template>

<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import moment from "moment";
import IssueQueryModal from "../../../components/Modals/PMS/IssueQueryModal.vue";
import RespondQueryModal from "../../../components/Modals/PMS/RespondQueryModal.vue";
import QueryVerdictModal from "../../../components/Modals/PMS/QueryVerdictModal.vue";

const props = defineProps({
  receivedQueries: { type: Array, default: () => [] },
  issuedQueries: { type: Array, default: () => [] },
  allQueries: { type: Array, default: () => [] },
  isHrAdmin: { type: Boolean, default: false },
  canIssueQuery: { type: Boolean, default: false },
});

const page = usePage();

const canIssue = computed(() => {
  if (typeof props.canIssueQuery === "boolean" && props.canIssueQuery === true) {
    return true;
  }
  const allowed = [
    "super user", "super admin", "admin", "director", "deputy director", "assistant director", "accountant-general", "agf"
  ];
  const userRoles = (page.props.user_roles || []).concat(page.props.roles || []).concat(page.props.user?.roles || []);
  return userRoles.some((r) => {
    const roleName = typeof r === "string" ? r : (r?.name || "");
    return allowed.includes(roleName.toLowerCase());
  });
});

const activeTab = ref(props.receivedQueries.length > 0 ? "received" : (canIssue.value ? "issued" : "received"));
const showIssueModal = ref(false);
const showRespondModal = ref(false);
const showVerdictModal = ref(false);
const selectedQuery = ref(null);

const formatDate = (dateStr) => {
  if (!dateStr) return "—";
  return moment(dateStr).format("DD MMM YYYY");
};

const isOverdue = (deadlineStr) => {
  if (!deadlineStr) return false;
  return moment().isAfter(moment(deadlineStr));
};

const formatDeadline = (deadlineStr) => {
  if (!deadlineStr) return "—";
  const end = moment(deadlineStr);
  const now = moment();
  if (now.isAfter(end)) {
    return `Expired (${end.fromNow()})`;
  }
  return `Due ${now.to(end)}`;
};

const formatVerdict = (v) => {
  switch (v) {
    case "accepted":
      return "Exonerated (Accepted)";
    case "cautioned":
      return "Letter of Advice Issued";
    case "escalated_to_dc":
      return "Escalated to DC";
    case "under_review":
      return "Under Review";
    case "pending_response":
      return "Pending Representation";
    default:
      return v || "Pending";
  }
};

const getVerdictBadge = (v) => {
  switch (v) {
    case "accepted":
      return "bg-emerald-100 text-emerald-800";
    case "cautioned":
      return "bg-amber-100 text-amber-800";
    case "escalated_to_dc":
      return "bg-red-100 text-red-800 font-bold";
    case "under_review":
      return "bg-purple-100 text-purple-800";
    case "pending_response":
      return "bg-blue-100 text-blue-800 animate-pulse";
    default:
      return "bg-slate-100 text-slate-800";
  }
};

const openRespondModal = (query) => {
  selectedQuery.value = query;
  showRespondModal.value = true;
};

const openVerdictModal = (query) => {
  selectedQuery.value = query;
  showVerdictModal.value = true;
};
</script>
