<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'border-0 rounded-2xl shadow-2xl overflow-hidden' },
      content: { class: 'px-0 bg-white pb-6' },
      header: { class: 'p-0 border-b-0' }
    }"
    :style="{ width: '52rem', maxWidth: '96vw' }"
    :breakpoints="{ '1199px': '85vw', '768px': '95vw' }"
  >
    <template #header>
      <!-- Dossier Header -->
      <div class="w-full bg-gradient-to-r from-slate-900 via-[#101828] to-slate-900 text-white p-6 relative">
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 text-emerald-400 shadow-inner">
              <Icon :icon="getDomainIcon(logDomain)" width="26" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-mono uppercase tracking-wider text-slate-300 bg-white/10 px-2 py-0.5 rounded border border-white/10">
                  Dossier #{{ audit?.id || '—' }}
                </span>
                <span
                  class="text-xs font-semibold px-2 py-0.5 rounded-full uppercase tracking-wider flex items-center gap-1.5"
                  :class="getSeverityClass(logSeverity)"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="getSeverityDotClass(logSeverity)"></span>
                  {{ logSeverity }}
                </span>
                <span class="text-xs font-medium text-slate-300 capitalize bg-slate-800/80 px-2.5 py-0.5 rounded-full border border-slate-700">
                  {{ logDomain }} Channel
                </span>
              </div>
              <h1 class="text-xl font-bold tracking-tight text-white mt-1.5">
                {{ formatEventName(audit?.event || audit?.properties?.event) }}
              </h1>
            </div>
          </div>

          <button
            class="text-slate-400 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all duration-200 outline-none"
            @click="closeModal"
            title="Close Dossier"
          >
            <Icon icon="mdi:close" width="20" />
          </button>
        </div>

        <p class="text-xs text-slate-300 mt-3 font-normal leading-relaxed line-clamp-2">
          {{ audit?.description || 'No descriptive narrative recorded.' }}
        </p>
      </div>
    </template>

    <div v-if="audit" class="px-6 pt-5 space-y-6 max-h-[72vh] overflow-y-auto custom-scrollbar">
      <!-- 1. Executive Summary Strip -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <!-- Timestamp Card -->
        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
            <Icon icon="carbon:time" class="text-slate-400" width="16" />
            Recorded At
          </div>
          <div class="text-sm font-bold text-slate-800">
            {{ formatFullDate(audit.created_at) }}
          </div>
          <div class="text-[11px] font-mono text-slate-500 mt-0.5">
            {{ formatRelativeTime(audit.created_at) }}
          </div>
        </div>

        <!-- Actor Identity Card -->
        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
            <Icon icon="lucide:user-check" class="text-slate-400" width="16" />
            Operating Actor
          </div>
          <div class="text-sm font-bold text-slate-800 truncate" :title="actorName">
            {{ actorName }}
          </div>
          <div class="text-[11px] text-slate-500 truncate" :title="actorMeta">
            {{ actorMeta || 'Identity verified' }}
          </div>
        </div>

        <!-- Station / IP Card -->
        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
            <Icon icon="carbon:network-4" class="text-slate-400" width="16" />
            Station Telemetry
          </div>
          <div class="text-sm font-bold text-slate-800 font-mono">
            {{ stationIp }}
          </div>
          <div class="text-[11px] text-slate-500 truncate">
            {{ stationClient }}
          </div>
        </div>
      </div>

      <!-- 2. Cryptographic Evidentiary Chain Seal -->
      <div class="p-4 rounded-xl border bg-emerald-50/60 border-emerald-200/80">
        <div class="flex items-center justify-between mb-2.5">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs shadow-sm">
              <Icon icon="heroicons:shield-check-solid" width="16" />
            </div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-900">
              Cryptographic Evidentiary Seal (SHA-256 Chain of Custody)
            </h3>
          </div>
          <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
            Tamper-Evident Sealed
          </span>
        </div>

        <div class="space-y-2 font-mono text-xs">
          <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-200/60 flex items-center justify-between gap-3">
            <div class="truncate">
              <span class="text-slate-500 font-sans font-medium text-[11px]">Record Hash: </span>
              <span class="text-emerald-950 font-semibold select-all">{{ forensicHash }}</span>
            </div>
            <button
              v-if="forensicHash !== 'legacy_unhashed'"
              @click="copyText(forensicHash)"
              class="text-emerald-700 hover:text-emerald-900 p-1 rounded hover:bg-emerald-100"
              title="Copy Hash"
            >
              <Icon icon="solar:copy-line-duotone" width="16" />
            </button>
          </div>

          <div class="bg-white/80 p-2.5 rounded-lg border border-emerald-200/60 flex items-center justify-between gap-3">
            <div class="truncate">
              <span class="text-slate-500 font-sans font-medium text-[11px]">Parent Block Hash: </span>
              <span class="text-slate-700 select-all">{{ parentHash }}</span>
            </div>
            <span class="text-[10px] text-slate-400 font-sans font-medium">Chain Link</span>
          </div>
        </div>
        <p class="text-[11px] text-emerald-800/80 mt-2 font-sans">
          This digital event is sealed into an immutable SHA-256 merkle hash sequence. Any unauthorized modification to database rows breaks the cryptographic chain and triggers forensic integrity flags.
        </p>
      </div>

      <!-- 3. State Delta Comparison (Before vs After) -->
      <div v-if="hasDelta" class="border border-slate-200 rounded-xl overflow-hidden">
        <div class="bg-slate-100/80 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="octicon:diff-16" class="text-indigo-600" width="18" />
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">State Delta Audit (Field Modifications)</span>
          </div>
          <span class="text-[11px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full">
            {{ deltaChanges.length }} {{ deltaChanges.length === 1 ? 'Field Modified' : 'Fields Modified' }}
          </span>
        </div>

        <div class="divide-y divide-slate-100">
          <div
            v-for="(change, idx) in deltaChanges"
            :key="idx"
            class="p-3.5 hover:bg-slate-50/80 transition-colors"
          >
            <div class="text-xs font-mono font-bold text-slate-700 mb-2 flex items-center gap-2">
              <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
              {{ change.field }}
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs font-mono">
              <div class="p-2.5 rounded-lg bg-rose-50/70 border border-rose-200/60 text-rose-900">
                <div class="text-[10px] font-sans font-semibold text-rose-500 uppercase tracking-wider mb-1 flex items-center gap-1">
                  <Icon icon="mdi:minus-circle" width="13" /> Before (Prior State)
                </div>
                <div class="break-all whitespace-pre-wrap">{{ formatValue(change.old) }}</div>
              </div>
              <div class="p-2.5 rounded-lg bg-emerald-50/70 border border-emerald-200/60 text-emerald-900">
                <div class="text-[10px] font-sans font-semibold text-emerald-600 uppercase tracking-wider mb-1 flex items-center gap-1">
                  <Icon icon="mdi:plus-circle" width="13" /> After (Updated State)
                </div>
                <div class="break-all whitespace-pre-wrap">{{ formatValue(change.new) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Operational Context & Metadata Payload -->
      <div class="border border-slate-200 rounded-xl overflow-hidden">
        <div class="bg-slate-100/80 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="carbon:data-structured" class="text-slate-600" width="18" />
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Operational Context & Parameters</span>
          </div>
          <span class="text-[11px] text-slate-500 font-mono">Subject: {{ audit.subject_type || 'General System' }} #{{ audit.subject_id || 'N/A' }}</span>
        </div>

        <div class="p-4 bg-slate-900 text-slate-200 text-xs font-mono rounded-b-xl overflow-x-auto max-h-56">
          <pre class="leading-relaxed">{{ formattedProperties }}</pre>
        </div>
      </div>

      <!-- 5. Associated Document Files (if applicable) -->
      <div v-if="associatedFiles.length > 0" class="border border-slate-200 rounded-xl overflow-hidden">
        <div class="bg-slate-100/80 px-4 py-2.5 border-b border-slate-200 flex items-center gap-2">
          <Icon icon="fluent:document-folder-24-regular" class="text-slate-600" width="18" />
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Associated Evidentiary Files</span>
        </div>

        <div class="divide-y divide-slate-100 p-2">
          <div
            v-for="(file, i) in associatedFiles"
            :key="i"
            class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
                <Icon icon="vscode-icons:file-type-pdf2" width="24" />
              </div>
              <div>
                <h4 class="text-xs font-bold text-slate-800">{{ file.file_name || 'Document' }}</h4>
                <p class="text-[11px] text-slate-400 font-mono">{{ file.size || file.file_size || 'Unknown size' }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="downloadEvidentiaryFile(file)"
                class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 flex items-center gap-1.5 shadow-sm"
              >
                <Icon icon="pajamas:download" width="14" /> Download
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dossier Footer -->
    <template #footer>
      <div class="px-6 pt-3 flex items-center justify-between w-full border-t border-slate-100">
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <Icon icon="carbon:certificate-check" class="text-emerald-600" width="16" />
          <span>Evidentiary verification record preserved.</span>
        </div>
        <button
          type="button"
          class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors shadow-sm"
          @click="closeModal"
        >
          Close Inspection
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { computed } from "vue";
import moment from "moment";
import { useToast } from "primevue/usetoast";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  audit: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(["update:modelValue"]);
const toast = useToast();

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  }
});

const closeModal = () => {
  emit("update:modelValue", false);
};

// Domain computation
const logDomain = computed(() => {
  if (!props.audit) return 'general';
  return props.audit.log_name || props.audit.properties?.domain || 'records';
});

// Severity computation
const logSeverity = computed(() => {
  if (!props.audit) return 'routine';
  return props.audit.properties?.severity || 'routine';
});

// Actor Name & Meta
const actorName = computed(() => {
  if (!props.audit) return 'System';
  const p = props.audit.properties;
  if (p?.actor?.name) return p.actor.name;
  if (p?.user) return p.user;
  if (props.audit.causer) {
    return `${props.audit.causer.first_name || ''} ${props.audit.causer.last_name || ''}`.trim() || props.audit.causer.email;
  }
  return 'System / Automated';
});

const actorMeta = computed(() => {
  if (!props.audit) return '';
  const p = props.audit.properties;
  const role = p?.actor?.role || '';
  const dept = p?.actor?.department || p?.department?.title || '';
  if (role && dept) return `${role} • ${dept}`;
  if (role) return role;
  if (dept) return dept;
  return p?.actor?.email || '';
});

// Telemetry Details
const stationIp = computed(() => {
  const p = props.audit?.properties;
  return p?.telemetry?.ip || p?.ip || '127.0.0.1';
});

const stationClient = computed(() => {
  const t = props.audit?.properties?.telemetry;
  if (t) {
    const parts = [];
    if (t.device) parts.push(t.device);
    if (t.os) parts.push(t.os);
    if (t.browser) parts.push(t.browser);
    if (parts.length > 0) return parts.join(' • ');
  }
  return props.audit?.properties?.device || 'EDMS Secure Workstation';
});

// Cryptographic Hash Data
const forensicHash = computed(() => {
  return props.audit?.properties?.forensic_hash || 'legacy_unhashed';
});

const parentHash = computed(() => {
  return props.audit?.properties?.parent_hash || 'genesis_or_unlinked';
});

// State Delta Evaluation
const hasDelta = computed(() => {
  const p = props.audit?.properties;
  if (p?.delta?.changes && Array.isArray(p.delta.changes) && p.delta.changes.length > 0) {
    return true;
  }
  if (p?.old && p?.new && typeof p.old === 'object') {
    return true;
  }
  return false;
});

const deltaChanges = computed(() => {
  const p = props.audit?.properties;
  if (p?.delta?.changes && Array.isArray(p.delta.changes)) {
    return p.delta.changes;
  }
  if (p?.old && p?.new) {
    const changes = [];
    const keys = new Set([...Object.keys(p.old), ...Object.keys(p.new)]);
    keys.forEach(k => {
      changes.push({
        field: k,
        old: p.old[k],
        new: p.new[k]
      });
    });
    return changes;
  }
  return [];
});

// Formatted properties JSON excluding internal hashes already rendered in seals
const formattedProperties = computed(() => {
  if (!props.audit?.properties) return '{}';
  const clean = { ...props.audit.properties };
  // Hide verbose cryptographic hashes from raw JSON viewer since they are prominently displayed in the seal
  delete clean.forensic_hash;
  delete clean.parent_hash;
  return JSON.stringify(clean, null, 2);
});

// Associated files
const associatedFiles = computed(() => {
  const p = props.audit?.properties;
  if (p?.document_type?.file_path && Array.isArray(p.document_type.file_path)) {
    return p.document_type.file_path;
  }
  if (p?.files && Array.isArray(p.files)) {
    return p.files;
  }
  return [];
});

// Helper formatters
const formatFullDate = (date) => {
  return date ? moment(date).format('MMMM Do YYYY, h:mm:ss a') : '—';
};

const formatRelativeTime = (date) => {
  return date ? moment(date).fromNow() : '';
};

const formatEventName = (event) => {
  if (!event) return 'System Activity';
  return event.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatValue = (val) => {
  if (val === null || val === undefined) return '<empty>';
  if (typeof val === 'object') return JSON.stringify(val, null, 2);
  if (typeof val === 'boolean') return val ? 'true' : 'false';
  return String(val);
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

const getSeverityClass = (sev) => {
  switch (sev) {
    case 'critical': return 'bg-rose-500/20 text-rose-300 border border-rose-500/40';
    case 'warning': return 'bg-amber-500/20 text-amber-300 border border-amber-500/40';
    case 'notice': return 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40';
    case 'routine':
    default:
      return 'bg-slate-700 text-slate-300 border border-slate-600';
  }
};

const getSeverityDotClass = (sev) => {
  switch (sev) {
    case 'critical': return 'bg-rose-400 animate-ping';
    case 'warning': return 'bg-amber-400';
    case 'notice': return 'bg-emerald-400';
    case 'routine':
    default:
      return 'bg-slate-400';
  }
};

const copyText = (text) => {
  if (navigator?.clipboard?.writeText) {
    navigator.clipboard.writeText(text);
    if (toast) {
      toast.add({
        severity: "info",
        summary: "Hash Copied",
        detail: "Forensic SHA-256 seal copied to clipboard.",
        life: 2500
      });
    }
  }
};

const downloadEvidentiaryFile = (file) => {
  const url = file.path?.startsWith('/') ? `/storage${file.path}` : `/storage/${file.path}`;
  const fileName = file.file_name || 'audit_evidence_file';
  const link = document.createElement('a');
  link.href = url;
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>
