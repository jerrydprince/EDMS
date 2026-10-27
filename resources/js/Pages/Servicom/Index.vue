<template>
  <main class="w-full px-4 sm:px-6 py-6 space-y-6">
    <!-- Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <div class="p-2.5 bg-red-50 text-red-700 rounded-xl border border-red-100">
            <Icon icon="lucide:shield-alert" width="24" />
          </div>
          <div>
            <h1 class="text-2xl font-bold text-gray-900">SERVICOM & Whistleblower Portal</h1>
            <p class="text-xs text-gray-500 mt-0.5">Civil Service Grievance Triage, Harassment Inquiries & Disciplinary Panel Administration</p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="showLodgeModal = true"
          class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition-all"
        >
          <Icon icon="lucide:plus-circle" width="16" />
          <span>Lodge New Grievance / Report</span>
        </button>
      </div>
    </div>

    <!-- Summary Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-3.5">
        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
          <Icon icon="lucide:file-text" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Reports</p>
          <p class="text-xl font-bold text-gray-800">{{ props.stats?.total || 0 }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-3.5">
        <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
          <Icon icon="lucide:clock-3" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Under Triage</p>
          <p class="text-xl font-bold text-gray-800">{{ props.stats?.under_review || 0 }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-3.5">
        <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
          <Icon icon="lucide:users-round" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Panels Active</p>
          <p class="text-xl font-bold text-gray-800">{{ props.stats?.panels_active || 0 }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-3.5">
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
          <Icon icon="lucide:check-circle-2" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Cases Resolved</p>
          <p class="text-xl font-bold text-gray-800">{{ props.stats?.resolved || 0 }}</p>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex items-center gap-3.5">
        <div class="p-3 bg-gray-50 text-gray-600 rounded-lg">
          <Icon icon="lucide:x-circle" width="22" />
        </div>
        <div>
          <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Dismissed</p>
          <p class="text-xl font-bold text-gray-800">{{ props.stats?.dismissed || 0 }}</p>
        </div>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-2xl border border-gray-100 shadow-2xs">
      <div class="flex items-center gap-2 overflow-x-auto">
        <button
          v-for="tab in filterTabs"
          :key="tab.value"
          @click="activeFilter = tab.value"
          :class="activeFilter === tab.value ? 'bg-red-50 text-red-700 font-bold border-red-200' : 'text-gray-600 hover:bg-slate-50 border-transparent'"
          class="px-3.5 py-1.5 text-xs rounded-xl border transition-all whitespace-nowrap"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="flex items-center gap-2">
        <div class="relative w-full sm:w-64">
          <Icon icon="lucide:search" width="16" class="absolute left-3 top-2.5 text-gray-400" />
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search code, staff, or department..."
            class="w-full pl-9 pr-3 py-1.5 text-xs border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-red-500/20"
          />
        </div>
      </div>
    </div>

    <!-- Reports Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden">
      <div v-if="filteredReports.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-700">
          <thead class="bg-slate-50 text-xs uppercase text-gray-500 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4">Case Tracking Ref</th>
              <th class="py-3.5 px-4">Complainant / Source</th>
              <th class="py-3.5 px-4">Subject / Accused Officer</th>
              <th class="py-3.5 px-4">Allegation Category</th>
              <th class="py-3.5 px-4">Statement & Evidence</th>
              <th class="py-3.5 px-4">Investigation Panel</th>
              <th class="py-3.5 px-4">Status</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="report in filteredReports" :key="report.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-4 font-mono text-xs font-bold text-gray-900">
                {{ report.tracking_code }}
                <span class="block text-[10px] text-gray-400 font-sans font-normal">{{ formatDate(report.created_at) }}</span>
              </td>

              <td class="py-3.5 px-4 text-xs">
                <div v-if="report.is_anonymous" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 font-semibold border border-slate-200">
                  <Icon icon="lucide:shield" width="13" class="text-emerald-600" />
                  <span>Whistleblower (Anonymous)</span>
                </div>
                <div v-else-if="report.complainant" class="space-y-0.5">
                  <p class="font-bold text-gray-900">{{ report.complainant.first_name }} {{ report.complainant.last_name }}</p>
                  <p class="text-[10px] text-gray-400">{{ report.complainant.email }}</p>
                </div>
                <span v-else class="text-gray-400 italic">Confidential</span>
              </td>

              <td class="py-3.5 px-4 text-xs">
                <div v-if="report.accused" class="space-y-0.5">
                  <p class="font-bold text-red-950">{{ report.accused.first_name }} {{ report.accused.last_name }}</p>
                  <p class="text-[11px] text-gray-500">{{ report.accused.department?.title || 'Headquarters' }}</p>
                </div>
                <span v-else class="text-gray-400 italic">—</span>
              </td>

              <td class="py-3.5 px-4">
                <span :class="getCategoryBadge(report.category)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatCategory(report.category) }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-xs max-w-xs">
                <p class="line-clamp-2 text-gray-700" :title="report.complaint_details">
                  {{ report.complaint_details }}
                </p>
                <a
                  v-if="report.evidence_file_path"
                  :href="report.evidence_file_path"
                  target="_blank"
                  class="inline-flex items-center gap-1 mt-1 text-[11px] text-blue-700 font-semibold hover:underline"
                >
                  <Icon icon="lucide:paperclip" width="12" />
                  <span>View Evidence Attachment</span>
                </a>
              </td>

              <td class="py-3.5 px-4 text-xs">
                <div v-if="report.panel" class="space-y-0.5">
                  <span class="font-semibold text-purple-900 block truncate max-w-[180px]" :title="report.panel.panel_title">
                    {{ report.panel.panel_title }}
                  </span>
                  <p class="text-[11px] text-gray-500">
                    Chair: {{ report.panel.chairperson?.first_name }} {{ report.panel.chairperson?.last_name }}
                  </p>
                  <p v-if="report.panel.hearing_date" class="text-[10px] text-purple-700 font-medium">
                    Sitting: {{ formatDate(report.panel.hearing_date) }}
                  </p>
                </div>
                <span v-else class="text-gray-400 italic text-[11px]">No Panel Constituted</span>
              </td>

              <td class="py-3.5 px-4">
                <span :class="getStatusBadge(report.status)" class="px-2.5 py-1 rounded-full text-xs font-bold capitalize">
                  {{ formatStatus(report.status) }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-right">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    v-if="!report.panel && report.status !== 'closed' && report.status !== 'dismissed'"
                    @click="openPanelModal(report)"
                    class="px-2.5 py-1 text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-all"
                    title="Constitute Investigation Committee"
                  >
                    Open Panel
                  </button>

                  <button
                    v-if="report.panel"
                    @click="openFindingsModal(report.panel)"
                    class="px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-all"
                    title="Record Findings & Recommendations"
                  >
                    Findings
                  </button>

                  <select
                    :value="report.status"
                    @change="updateReportStatus(report.id, $event.target.value)"
                    class="p-1 border border-gray-200 rounded-lg text-xs bg-white text-gray-700 outline-none"
                  >
                    <option value="submitted">Submitted</option>
                    <option value="under_review">Under Review</option>
                    <option value="panel_convened">Panel Convened</option>
                    <option value="investigation_complete">Investigation Complete</option>
                    <option value="closed">Closed / Sanctioned</option>
                    <option value="dismissed">Dismissed (Baseless)</option>
                  </select>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-16">
        <Icon icon="lucide:shield-check" width="44" class="mx-auto text-emerald-500 mb-3" />
        <h3 class="text-lg font-bold text-gray-800">No SERVICOM Reports Found</h3>
        <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">
          No reports match the selected filter criteria. The civil service service delivery desk is currently clear.
        </p>
      </div>
    </div>
  </main>

  <!-- Modals -->
  <ReportMisconduct v-model="showLodgeModal" />
  <ConstitutePanelModal v-model="showPanelModal" :report="activeReport" :staffUsers="props.staffUsers" />
  <PanelFindingsModal v-model="showFindingsModal" :panel="activePanel" />
</template>

<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import moment from "moment";
import ReportMisconduct from "../../components/Modals/PMS/ReportMisconduct.vue";
import ConstitutePanelModal from "../../components/Modals/Servicom/ConstitutePanelModal.vue";
import PanelFindingsModal from "../../components/Modals/Servicom/PanelFindingsModal.vue";

const props = defineProps({
  reports: { type: Array, default: () => [] },
  staffUsers: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
});

const toast = useToast();

const showLodgeModal = ref(false);
const showPanelModal = ref(false);
const showFindingsModal = ref(false);
const activeReport = ref(null);
const activePanel = ref(null);

const activeFilter = ref("all");
const searchQuery = ref("");

const filterTabs = [
  { label: "All Cases", value: "all" },
  { label: "Under Triage", value: "under_review" },
  { label: "Active Panels", value: "panel_convened" },
  { label: "Resolved", value: "resolved" },
  { label: "Dismissed", value: "dismissed" },
];

const filteredReports = computed(() => {
  return props.reports.filter((r) => {
    // Filter by tab
    if (activeFilter.value === "under_review" && !["submitted", "under_review"].includes(r.status)) {
      return false;
    }
    if (activeFilter.value === "panel_convened" && r.status !== "panel_convened") {
      return false;
    }
    if (activeFilter.value === "resolved" && !["investigation_complete", "closed"].includes(r.status)) {
      return false;
    }
    if (activeFilter.value === "dismissed" && r.status !== "dismissed") {
      return false;
    }

    // Filter by search query
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const codeMatch = (r.tracking_code || "").toLowerCase().includes(q);
      const accusedMatch = `${r.accused?.first_name || ""} ${r.accused?.last_name || ""}`.toLowerCase().includes(q);
      const deptMatch = (r.accused?.department?.title || "").toLowerCase().includes(q);
      const catMatch = (r.category || "").toLowerCase().includes(q);
      return codeMatch || accusedMatch || deptMatch || catMatch;
    }

    return true;
  });
});

const formatDate = (dateStr) => {
  if (!dateStr) return "—";
  return moment(dateStr).format("DD MMM YYYY");
};

const formatCategory = (cat) => {
  if (!cat) return "Misconduct";
  return cat.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
};

const formatStatus = (status) => {
  if (!status) return "Submitted";
  return status.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase());
};

const getCategoryBadge = (cat) => {
  switch (cat) {
    case "harassment":
      return "bg-rose-100 text-rose-800 border border-rose-200";
    case "intimidation":
      return "bg-amber-100 text-amber-800 border border-amber-200";
    case "victimization":
      return "bg-purple-100 text-purple-800 border border-purple-200";
    case "extortion":
      return "bg-red-100 text-red-800 border border-red-200";
    case "misconduct":
      return "bg-orange-100 text-orange-800 border border-orange-200";
    default:
      return "bg-slate-100 text-slate-800 border border-slate-200";
  }
};

const getStatusBadge = (status) => {
  switch (status) {
    case "submitted":
      return "bg-blue-100 text-blue-800";
    case "under_review":
      return "bg-amber-100 text-amber-800 animate-pulse";
    case "panel_convened":
      return "bg-purple-100 text-purple-800 font-bold";
    case "investigation_complete":
      return "bg-indigo-100 text-indigo-800";
    case "closed":
      return "bg-emerald-100 text-emerald-800";
    case "dismissed":
      return "bg-gray-100 text-gray-600";
    default:
      return "bg-slate-100 text-slate-800";
  }
};

const openPanelModal = (report) => {
  activeReport.value = report;
  showPanelModal.value = true;
};

const openFindingsModal = (panel) => {
  activePanel.value = panel;
  showFindingsModal.value = true;
};

const updateReportStatus = (reportId, newStatus) => {
  router.post(`/dashboard/servicom/status/${reportId}`, { status: newStatus }, {
    onSuccess: () => {
      toast.add({
        severity: "info",
        summary: "Status Updated",
        detail: `Report status updated to: ${newStatus}`,
        life: 3000,
      });
    },
  });
};
</script>
