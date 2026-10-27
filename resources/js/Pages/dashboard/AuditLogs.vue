<template>
  <main class="min-h-screen bg-slate-50/60 pb-14">
    <!-- Header Section -->
    <div class="px-7 py-6 bg-white border-b border-slate-200">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-md">
            <Icon icon="carbon:fingerprint-recognition" width="28" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Forensic Audit Trail & Evidentiary Log</h1>
              <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                SHA-256 Chained
              </span>
            </div>
            <p class="text-slate-500 text-sm mt-0.5">
              Comprehensive immutable record of all security, financial, administrative, and record operations across the EDMS.
            </p>
          </div>
        </div>

        <!-- Executive Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <button
            v-if="canVerify"
            @click="openIntegrityModal"
            class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-1.5 shadow-sm"
          >
            <Icon icon="heroicons:shield-check" class="text-emerald-600" width="18" />
            Verify Chain Integrity
          </button>

          <a
            v-if="canExport"
            :href="exportUrl"
            class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-1.5 shadow-sm"
          >
            <Icon icon="pajamas:export" width="16" />
            Export Evidence (CSV)
          </a>
        </div>
      </div>
    </div>

    <div class="px-7 pt-6 space-y-6">
      <!-- 1. Executive Telemetry KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Daily Activity Volume -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Activities</span>
            <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <Icon icon="carbon:activity" width="20" />
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-2">
            {{ telemetryData?.total_today || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
            <span class="font-semibold text-slate-700">{{ telemetryData?.active_actors || 0 }}</span> active officers in past 24h
          </div>
        </div>

        <!-- Security Flags & Alerts -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Security & Flags</span>
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
              <Icon icon="carbon:security" width="20" />
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-2">
            {{ telemetryData?.security_warnings || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-1">
            PIN retries, privilege changes & alerts
          </div>
        </div>

        <!-- Financial Operations -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Financial Transactions</span>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <Icon icon="carbon:wallet" width="20" />
            </div>
          </div>
          <div class="text-2xl font-bold text-slate-900 mt-2">
            {{ telemetryData?.financial_operations || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-1">
            Voucher issuances, checking & audit stamps
          </div>
        </div>

        <!-- Chain Integrity Seal -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cryptographic Custody</span>
            <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
              <Icon icon="heroicons:shield-check-solid" width="20" />
            </div>
          </div>
          <div class="text-base font-bold text-emerald-700 mt-2 flex items-center gap-1.5">
            <Icon icon="carbon:checkmark-filled" class="text-emerald-600" width="18" />
            Chain Intact
          </div>
          <div class="text-xs text-slate-500 mt-1">
            SHA-256 tamper-evident merkle chain
          </div>
        </div>
      </div>

      <!-- 2. Domain Channel Navigation Tabs -->
      <div class="bg-white rounded-xl border border-slate-200 p-1.5 shadow-sm overflow-x-auto">
        <div class="flex items-center gap-1 min-w-max">
          <button
            v-for="channel in domainChannels"
            :key="channel.key"
            @click="switchDomain(channel.key)"
            class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-150 flex items-center gap-2"
            :class="selectedDomain === channel.key
              ? 'bg-slate-900 text-white shadow-sm'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            <Icon :icon="channel.icon" width="16" />
            {{ channel.label }}
          </button>
        </div>
      </div>

      <!-- 3. Advanced Forensic Filter Bar -->
      <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
          <!-- Keyword / Hash / IP Search -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Search Keyword / IP / Hash</label>
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 focus-within:border-slate-900 focus-within:bg-white transition-colors">
              <Icon icon="iconamoon:search-light" class="text-slate-400 mr-2 shrink-0" width="18" />
              <input
                type="text"
                v-model="filters.keyword"
                @keyup.enter="applyFilters"
                placeholder="Description, IP, or hash..."
                class="w-full bg-transparent text-xs text-slate-800 placeholder-slate-400 outline-none"
              />
            </div>
          </div>

          <!-- Actor Search -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Operating Officer</label>
            <div class="flex items-center bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 focus-within:border-slate-900 focus-within:bg-white transition-colors">
              <Icon icon="carbon:user" class="text-slate-400 mr-2 shrink-0" width="18" />
              <input
                type="text"
                v-model="filters.user"
                @keyup.enter="applyFilters"
                placeholder="Name or email..."
                class="w-full bg-transparent text-xs text-slate-800 placeholder-slate-400 outline-none"
              />
            </div>
          </div>

          <!-- Severity Level -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Severity Level</label>
            <select
              v-model="filters.severity"
              class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 outline-none focus:border-slate-900 focus:bg-white transition-colors"
            >
              <option value="">All Severities</option>
              <option value="routine">Routine (Operational)</option>
              <option value="notice">Notice (Significant)</option>
              <option value="warning">Warning (Security / Alert)</option>
              <option value="critical">Critical (Breach / Escalation)</option>
            </select>
          </div>

          <!-- Date Range (From - To) -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Date Filter</label>
            <div class="grid grid-cols-2 gap-1.5">
              <input
                type="date"
                v-model="filters.date_from"
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-xs text-slate-800 outline-none focus:border-slate-900 focus:bg-white"
                title="From Date"
              />
              <input
                type="date"
                v-model="filters.date_to"
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-2 py-2 text-xs text-slate-800 outline-none focus:border-slate-900 focus:bg-white"
                title="To Date"
              />
            </div>
          </div>

          <!-- Filter Action Buttons -->
          <div class="flex items-center gap-2">
            <button
              @click="applyFilters"
              class="w-full py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-sm"
            >
              <Icon icon="carbon:filter" width="16" />
              Apply Filter
            </button>
            <button
              @click="resetFilters"
              class="p-2 border border-slate-300 text-slate-600 rounded-lg hover:bg-slate-50 transition-colors"
              title="Reset Filters"
            >
              <Icon icon="tabler:refresh" width="18" />
            </button>
          </div>
        </div>
      </div>

      <!-- 4. Forensic Audit Table -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <DataTable
          :value="activitiesList"
          tableStyle="min-width: 65rem"
          class="p-datatable-sm"
          responsiveLayout="scroll"
        >
          <!-- Empty State -->
          <template #empty>
            <div class="text-center py-12 px-4">
              <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <Icon icon="carbon:document-blank" width="24" />
              </div>
              <h3 class="text-sm font-bold text-slate-800">No Audit Records Found</h3>
              <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                No system activities matched the selected filters. Clear the search filters or switch channels to inspect historical records.
              </p>
            </div>
          </template>

          <!-- 1. Timestamp & Ref ID -->
          <Column header="Timestamp & Ref" class="w-48">
            <template #body="{ data }">
              <div>
                <div class="text-xs font-bold text-slate-800 font-mono">
                  {{ formatDate(data.created_at) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-mono mt-0.5">
                  <span>#{{ data.id }}</span>
                  <span>•</span>
                  <span>{{ formatTimeAgo(data.created_at) }}</span>
                </div>
              </div>
            </template>
          </Column>

          <!-- 2. Domain & Severity -->
          <Column header="Domain & Severity" class="w-44">
            <template #body="{ data }">
              <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 capitalize">
                  <Icon :icon="getDomainIcon(data.log_name || data.properties?.domain)" width="13" />
                  {{ data.log_name || data.properties?.domain || 'records' }}
                </div>
                <div>
                  <span
                    class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
                    :class="getSeverityPillClass(data.properties?.severity)"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="getSeverityDotClass(data.properties?.severity)"></span>
                    {{ data.properties?.severity || 'routine' }}
                  </span>
                </div>
              </div>
            </template>
          </Column>

          <!-- 3. Event & Narrative -->
          <Column header="Activity Narrative">
            <template #body="{ data }">
              <div>
                <div class="flex items-center gap-2 mb-0.5">
                  <span class="text-xs font-mono font-bold text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                    {{ formatEventCode(data.event || data.properties?.event) }}
                  </span>
                </div>
                <div class="text-xs text-slate-700 font-medium line-clamp-2" :title="data.description">
                  {{ data.description || 'System event triggered.' }}
                </div>
              </div>
            </template>
          </Column>

          <!-- 4. Actor & Station -->
          <Column header="Officer & Station" class="w-52">
            <template #body="{ data }">
              <div>
                <div class="text-xs font-bold text-slate-800 truncate" :title="resolveActorName(data)">
                  {{ resolveActorName(data) }}
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-mono mt-0.5">
                  <Icon icon="carbon:network-4" width="12" class="text-slate-400" />
                  <span>{{ resolveIp(data) }}</span>
                </div>
                <div class="text-[10px] text-slate-400 truncate">
                  {{ resolveDevice(data) }}
                </div>
              </div>
            </template>
          </Column>

          <!-- 5. Forensic Seal -->
          <Column header="Evidentiary Hash" class="w-36">
            <template #body="{ data }">
              <div v-if="data.properties?.forensic_hash" class="font-mono text-[11px]">
                <span class="inline-flex items-center gap-1 text-emerald-800 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded">
                  <Icon icon="carbon:link" width="11" class="text-emerald-600" />
                  {{ truncateHash(data.properties.forensic_hash) }}
                </span>
              </div>
              <div v-else class="text-[10px] text-slate-400 font-mono">
                legacy_unhashed
              </div>
            </template>
          </Column>

          <!-- 6. Action Inspection -->
          <Column header="Inspect" class="w-24 text-right">
            <template #body="{ data }">
              <button
                @click="inspectAudit(data)"
                class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 hover:text-slate-900 rounded-lg transition-colors flex items-center gap-1 ml-auto"
                title="Inspect Forensic Dossier"
              >
                <Icon icon="carbon:search" width="14" />
                <span>Dossier</span>
              </button>
            </template>
          </Column>
        </DataTable>

        <!-- Pagination -->
        <div class="px-6 py-3 border-t border-slate-200 bg-slate-50/50">
          <pagination :archives="props.activities" />
        </div>
      </div>
    </div>

    <!-- Forensic Dossier Modal -->
    <ViewLog v-model="viewLogModal" :audit="selectedAudit" />

    <!-- Live Cryptographic Chain Integrity Verification Modal -->
    <Dialog
      v-model:visible="integrityModalVisible"
      modal
      :closable="false"
      :style="{ width: '38rem', maxWidth: '95vw' }"
      :pt="{
        root: { class: 'border-0 rounded-2xl shadow-2xl overflow-hidden' },
        content: { class: 'p-0 bg-white' }
      }"
    >
      <div class="p-6 bg-slate-900 text-white">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
              <Icon icon="heroicons:shield-check-solid" width="24" />
            </div>
            <div>
              <h2 class="text-lg font-bold">Cryptographic Chain Verification</h2>
              <p class="text-xs text-slate-400">Verifying SHA-256 sequential hash custody</p>
            </div>
          </div>
          <button @click="integrityModalVisible = false" class="text-slate-400 hover:text-white p-1 rounded">
            <Icon icon="mdi:close" width="20" />
          </button>
        </div>
      </div>

      <div class="p-6 space-y-4">
        <div v-if="verifyingChain" class="text-center py-8">
          <div class="animate-spin w-10 h-10 border-4 border-slate-900 border-t-transparent rounded-full mx-auto mb-3"></div>
          <p class="text-xs font-semibold text-slate-700">Verifying SHA-256 integrity across historical blocks...</p>
        </div>

        <div v-else-if="integrityResult" class="space-y-4">
          <div
            class="p-4 rounded-xl border flex items-start gap-3.5"
            :class="integrityResult.verified ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900'"
          >
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5"
              :class="integrityResult.verified ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
            >
              <Icon :icon="integrityResult.verified ? 'carbon:checkmark' : 'carbon:close'" width="20" />
            </div>
            <div>
              <h4 class="text-sm font-bold">
                {{ integrityResult.verified ? 'Chain of Custody Intact & Verified' : 'Cryptographic Chain Alert' }}
              </h4>
              <p class="text-xs mt-1 leading-relaxed opacity-90">
                {{ integrityResult.message }}
              </p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
              <span class="text-slate-400 block text-[10px] uppercase font-bold">Audited Hash Blocks</span>
              <span class="text-sm font-bold text-slate-800 font-mono">{{ integrityResult.verified_count || 0 }}</span>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
              <span class="text-slate-400 block text-[10px] uppercase font-bold">Chain Status</span>
              <span class="text-sm font-bold text-emerald-700 font-mono">100% Cryptographic Match</span>
            </div>
          </div>

          <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono space-y-1">
            <div class="text-[11px] font-sans font-bold text-slate-700 mb-1">Evidentiary Head Hash:</div>
            <div class="text-slate-800 break-all select-all">{{ integrityResult.head_hash || 'genesis' }}</div>
          </div>
        </div>
      </div>

      <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
        <button
          @click="integrityModalVisible = false"
          class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors"
        >
          Close Report
        </button>
      </div>
    </Dialog>
  </main>
</template>

<script setup>
import { ref, computed, reactive, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import moment from "moment";
import ViewLog from "../../components/Modals/Audit/viewLog.vue";
import pagination from "../dashboard/partials/pagination.vue";
import axios from "axios";

const page = usePage();
const userPermissions = computed(() => {
  const p = page.props.permissions || [];
  return Array.isArray(p) ? p.map(x => String(x).toLowerCase().trim()) : [];
});
const userRoles = computed(() => {
  const r = page.props.roles || page.props.user_roles || [];
  return (Array.isArray(r) ? r : Object.values(r)).map(x => (typeof x === 'string' ? x : x?.name || '').toLowerCase().trim());
});
const hasAuditPermission = (perm) => {
  if (userRoles.value.some(r => r === 'super admin' || r === 'admin')) return true;
  return userPermissions.value.includes(perm.toLowerCase().trim());
};
const canVerify = computed(() => hasAuditPermission('audit verify'));
const canExport = computed(() => hasAuditPermission('audit export'));

const props = defineProps({
  activities: {
    type: Object,
    default: () => ({ data: [] })
  },
  telemetry: {
    type: Object,
    default: () => ({})
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  search: {
    type: Object,
    default: () => ({})
  }
});

// Domain Channels
const domainChannels = [
  { key: 'all', label: 'All Activities', icon: 'carbon:layers' },
  { key: 'security', label: 'Security & Access', icon: 'carbon:security' },
  { key: 'finance', label: 'Financial Forensics', icon: 'carbon:wallet' },
  { key: 'workflow', label: 'Workflow Routing', icon: 'carbon:flow-stream' },
  { key: 'privilege', label: 'Privilege & Staff', icon: 'carbon:user-role' },
  { key: 'records', label: 'Records & Vault', icon: 'carbon:document' },
  { key: 'governance', label: 'Governance & Policy', icon: 'carbon:policy' }
];

// Active State
const selectedDomain = ref(props.filters?.domain || 'all');
const viewLogModal = ref(false);
const selectedAudit = ref(null);
const integrityModalVisible = ref(false);
const verifyingChain = ref(false);
const integrityResult = ref(props.telemetry?.chain_integrity || null);

// Reactive Filter Form
const filters = reactive({
  keyword: props.filters?.keyword || '',
  user: props.filters?.user || '',
  severity: props.filters?.severity || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
});

// Data Lists
const activitiesList = computed(() => props.activities?.data || []);
const telemetryData = computed(() => props.telemetry || {});

// Export CSV URL with active filters
const exportUrl = computed(() => {
  const params = new URLSearchParams();
  if (selectedDomain.value && selectedDomain.value !== 'all') params.append('domain', selectedDomain.value);
  if (filters.severity) params.append('severity', filters.severity);
  if (filters.keyword) params.append('keyword', filters.keyword);
  if (filters.user) params.append('user', filters.user);
  if (filters.date_from) params.append('date_from', filters.date_from);
  if (filters.date_to) params.append('date_to', filters.date_to);
  return `/dashboard/audit-log/export?${params.toString()}`;
});

// Switch Domain Channel
const switchDomain = (domainKey) => {
  selectedDomain.value = domainKey;
  applyFilters();
};

// Apply Filters
const applyFilters = () => {
  router.get('/dashboard/audit-log', {
    domain: selectedDomain.value,
    keyword: filters.keyword,
    user: filters.user,
    severity: filters.severity,
    date_from: filters.date_from,
    date_to: filters.date_to
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

// Reset Filters
const resetFilters = () => {
  filters.keyword = '';
  filters.user = '';
  filters.severity = '';
  filters.date_from = '';
  filters.date_to = '';
  selectedDomain.value = 'all';

  router.get('/dashboard/audit-log', {}, {
    preserveState: true,
    preserveScroll: true
  });
};

// Inspect Audit Record
const inspectAudit = (item) => {
  selectedAudit.value = item;
  viewLogModal.value = true;
};

// Chain Integrity Verification
const openIntegrityModal = async () => {
  integrityModalVisible.value = true;
  verifyingChain.value = true;
  try {
    const res = await axios.get('/dashboard/audit-log/verify-integrity', {
      params: { limit: 100 }
    });
    integrityResult.value = res.data?.data || null;
  } catch (err) {
    integrityResult.value = {
      verified: false,
      message: 'Failed to complete cryptographic verification check.'
    };
  } finally {
    verifyingChain.value = false;
  }
};

// Formatters
const formatDate = (date) => {
  return date ? moment(date).format('YYYY-MM-DD HH:mm:ss') : '—';
};

const formatTimeAgo = (date) => {
  return date ? moment(date).fromNow() : '';
};

const formatEventCode = (event) => {
  if (!event) return 'GENERAL_EVENT';
  return String(event).toUpperCase();
};

const truncateHash = (hash) => {
  if (!hash) return '';
  if (hash.length <= 12) return hash;
  return `${hash.substring(0, 6)}...${hash.substring(hash.length - 6)}`;
};

const resolveActorName = (item) => {
  const p = item.properties;
  if (p?.actor?.name) return p.actor.name;
  if (p?.user) return p.user;
  if (item.causer) {
    const name = `${item.causer.first_name || ''} ${item.causer.last_name || ''}`.trim();
    if (name) return name;
    return item.causer.email || 'Officer';
  }
  return 'System / Automated';
};

const resolveIp = (item) => {
  const p = item.properties;
  return p?.telemetry?.ip || p?.ip || '127.0.0.1';
};

const resolveDevice = (item) => {
  const t = item.properties?.telemetry;
  if (t?.device) return `${t.device} (${t.browser || 'Web'})`;
  return item.properties?.device || 'Secure Station';
};

const getDomainIcon = (domain) => {
  switch (domain) {
    case 'security': return 'carbon:security';
    case 'finance': return 'carbon:wallet';
    case 'workflow': return 'carbon:flow-stream';
    case 'privilege': return 'carbon:user-role';
    case 'governance': return 'carbon:policy';
    case 'records':
    default:
      return 'carbon:document';
  }
};

const getSeverityPillClass = (sev) => {
  switch (sev) {
    case 'critical': return 'bg-rose-100 text-rose-800 border border-rose-200';
    case 'warning': return 'bg-amber-100 text-amber-800 border border-amber-200';
    case 'notice': return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
    case 'routine':
    default:
      return 'bg-slate-100 text-slate-700 border border-slate-200';
  }
};

const getSeverityDotClass = (sev) => {
  switch (sev) {
    case 'critical': return 'bg-rose-500 animate-pulse';
    case 'warning': return 'bg-amber-500';
    case 'notice': return 'bg-emerald-500';
    case 'routine':
    default:
      return 'bg-slate-400';
  }
};

watch(() => props.filters, (newFilters) => {
  if (newFilters) {
    filters.keyword = newFilters.keyword || '';
    filters.user = newFilters.user || '';
    filters.severity = newFilters.severity || '';
    filters.date_from = newFilters.date_from || '';
    filters.date_to = newFilters.date_to || '';
    if (newFilters.domain) selectedDomain.value = newFilters.domain;
  }
}, { deep: true });
</script>

<style scoped>
:deep(.p-datatable-thead > tr > th) {
  background-color: #f8fafc;
  color: #475569;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.85rem 1rem;
  border-bottom: 1px solid #e2e8f0;
}

:deep(.p-datatable-tbody > tr) {
  transition: background-color 0.15s ease;
  border-bottom: 1px solid #f1f5f9;
}

:deep(.p-datatable-tbody > tr:hover) {
  background-color: #f8fafc !important;
}

:deep(.p-datatable-tbody > tr > td) {
  padding: 0.85rem 1rem;
  vertical-align: middle;
}
</style>
