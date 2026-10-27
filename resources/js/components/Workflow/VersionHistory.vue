<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
      <div>
        <div class="flex items-center gap-2">
          <h3 class="text-lg font-bold text-gray-800">Living Document Versions</h3>
          <span class="px-2.5 py-0.5 text-xs font-bold bg-blue-50 text-blue-700 rounded-full border border-blue-100">
            {{ versions.length }} Version{{ versions.length === 1 ? '' : 's' }}
          </span>
        </div>
        <p class="text-xs text-gray-500 mt-0.5">Chronological audit trail of living document revisions and endorsements</p>
      </div>

      <!-- Mode Toggles -->
      <div class="flex items-center gap-2">
        <button 
          @click="activeSubTab = 'versions'"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer"
          :class="activeSubTab === 'versions' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
        >
          <div class="flex items-center gap-1.5">
            <Icon icon="lucide:layers" width="14" />
            <span>Version List</span>
          </div>
        </button>
        <button 
          @click="activeSubTab = 'annotations'"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer relative"
          :class="activeSubTab === 'annotations' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
        >
          <div class="flex items-center gap-1.5">
            <Icon icon="lucide:message-square" width="14" />
            <span>Review Notes</span>
            <span v-if="unresolvedNotesCount > 0" class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
          </div>
        </button>
      </div>
    </div>

    <!-- 1. VERSIONS LIST -->
    <div v-show="activeSubTab === 'versions'" class="space-y-4">
      <div v-if="versions.length === 0" class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200 text-gray-400">
        <Icon icon="lucide:history" width="40" class="mx-auto mb-2 opacity-40" />
        <p class="text-sm font-medium">No document versions recorded yet.</p>
        <p class="text-xs text-gray-400">Versions will be created as the document is submitted and signed.</p>
      </div>

      <div 
        v-for="(ver, index) in versions" 
        :key="ver.id"
        class="bg-white rounded-2xl border transition-all p-5 hover:shadow-md relative overflow-hidden group cursor-pointer"
        :class="activeVersionId === ver.id ? 'border-blue-500 ring-2 ring-blue-500/10 bg-blue-50/20 shadow-xs' : 'border-gray-200/80 hover:border-blue-200'"
        @click="loadVersion(ver)"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div 
              class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm shrink-0 shadow-xs"
              :class="getActionBadgeClass(ver.action)"
            >
              v{{ ver.version }}
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h4 class="font-bold text-sm text-gray-800">
                  Version {{ ver.version }} - {{ getActionTitle(ver.action) }}
                </h4>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full uppercase" :class="getActionPillClass(ver.action)">
                  {{ ver.action }}
                </span>
                <span v-if="activeVersionId === ver.id" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 flex items-center gap-1">
                  <Icon icon="lucide:check" width="12" /> Active in PDF Viewer
                </span>
              </div>
              <p class="text-xs text-gray-500 mt-0.5">
                By <span class="font-medium text-gray-700">{{ ver.created_by_user?.first_name || ver.user?.first_name || 'Officer' }} {{ ver.created_by_user?.last_name || ver.user?.last_name || '' }}</span>
                <span v-if="ver.created_by_user?.department?.title" class="text-gray-400"> ({{ ver.created_by_user.department.title }})</span>
                • {{ formatDate(ver.created_at) }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 shrink-0">
            <button 
              type="button"
              @click.stop="loadVersion(ver)"
              class="px-3.5 py-1.5 rounded-xl border font-semibold text-xs transition-all flex items-center gap-1.5 cursor-pointer shadow-2xs"
              :class="activeVersionId === ver.id 
                ? 'bg-blue-600 text-white border-blue-600 shadow-xs' 
                : 'border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-blue-700'"
            >
              <Icon :icon="activeVersionId === ver.id ? 'lucide:eye' : 'lucide:eye'" width="14" />
              <span>{{ activeVersionId === ver.id ? 'Viewing v' + ver.version : 'Preview v' + ver.version }}</span>
            </button>
            <a 
              :href="ver.path" 
              target="_blank" 
              download
              @click.stop
              class="p-1.5 rounded-xl border border-gray-200 text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition-colors cursor-pointer"
              title="Download this version"
            >
              <Icon icon="lucide:download" width="14" />
            </a>
          </div>
        </div>

        <!-- Note / Remarks -->
        <div class="mt-3 text-xs text-gray-600 bg-gray-50/70 rounded-xl p-3 border border-gray-100" v-if="ver.note">
          <p class="font-semibold text-gray-400 uppercase text-[10px] tracking-wider mb-1">Officer Note / Directive</p>
          <p class="text-gray-700 whitespace-pre-wrap">{{ ver.note }}</p>
        </div>

        <!-- Annotations count badge if present -->
        <div v-if="ver.annotations && ver.annotations.length > 0" class="mt-2.5 flex items-center gap-2">
          <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/80 flex items-center gap-1">
            <Icon icon="lucide:pin" width="12" />
            {{ ver.annotations.length }} review note(s) attached
          </span>
        </div>
      </div>
    </div>

    <!-- 2. ANNOTATIONS / REVIEW NOTES TAB -->
    <div v-show="activeSubTab === 'annotations'" class="space-y-4">
      <div v-if="allAnnotations.length === 0" class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200 text-gray-400">
        <Icon icon="lucide:check-circle" width="40" class="mx-auto mb-2 text-emerald-500/40" />
        <p class="text-sm font-medium">No return review notes currently pending.</p>
        <p class="text-xs text-gray-400">All reviewer annotations have either been resolved or cleared.</p>
      </div>

      <div 
        v-for="note in allAnnotations" 
        :key="note.id"
        class="bg-white rounded-2xl border p-4 transition-all"
        :class="note.resolved_at ? 'border-gray-200 opacity-70 bg-gray-50/40' : 'border-amber-200 shadow-xs'"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-2.5">
            <div 
              class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold"
              :class="note.resolved_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
            >
              <Icon :icon="note.resolved_at ? 'lucide:check' : 'lucide:message-circle'" width="14" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-800">Page {{ note.page }}</span>
                <span v-if="note.resolved_at" class="text-[10px] font-semibold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full">
                  Resolved
                </span>
                <span v-else class="text-[10px] font-bold px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full">
                  Pending Action
                </span>
              </div>
              <p class="text-xs text-gray-700 mt-1.5 whitespace-pre-wrap">{{ note.text }}</p>
              <p class="text-[10px] text-gray-400 mt-2 font-mono">
                Created on Version {{ note.version_id }} • {{ formatDate(note.created_at) }}
              </p>
            </div>
          </div>

          <button 
            v-if="!note.resolved_at"
            type="button"
            @click="resolveNote(note)"
            class="px-3 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition-all shrink-0 cursor-pointer flex items-center gap-1"
          >
            <Icon icon="lucide:check" width="13" />
            <span>Mark Resolved</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  task: { type: Object, required: true },
  activePath: { type: String, default: '' },
});

const emit = defineEmits(['selectVersion', 'refresh']);

const activeSubTab = ref('versions');
const selectedVersionId = ref(null);
const versions = ref([]);

const activeVersionId = computed(() => {
  if (selectedVersionId.value) {
    return selectedVersionId.value;
  }
  if (props.activePath && versions.value.length > 0) {
    const cleanActive = props.activePath.split('?')[0].replace(/^\//, '');
    const found = versions.value.find(v => {
      const cleanVer = (v.path || '').split('?')[0].replace(/^\//, '');
      return cleanVer === cleanActive;
    });
    if (found) return found.id;
  }
  return versions.value[0]?.id ?? null;
});

const fetchVersions = async () => {
  try {
    const res = await axios.get(`/dashboard/tasks/${props.task.id}/versions`);
    versions.value = res.data?.versions || [];
  } catch (err) {
    console.error('Failed to load version history:', err);
  }
};

onMounted(() => {
  if (props.task?.document_versions && props.task.document_versions.length > 0) {
    versions.value = props.task.document_versions;
  } else {
    fetchVersions();
  }
});

const allAnnotations = computed(() => {
  const notes = [];
  versions.value.forEach(v => {
    if (v.annotations) {
      v.annotations.forEach(a => notes.push(a));
    }
  });
  return notes;
});

const unresolvedNotesCount = computed(() => {
  return allAnnotations.value.filter(n => !n.resolved_at).length;
});

const getActionBadgeClass = (action) => {
  switch (action) {
    case 'approve': return 'bg-purple-100 text-purple-800 border border-purple-200';
    case 'return': return 'bg-rose-100 text-rose-800 border border-rose-200';
    case 'sign': return 'bg-blue-100 text-blue-800 border border-blue-200';
    default: return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
  }
};

const getActionPillClass = (action) => {
  switch (action) {
    case 'approve': return 'bg-purple-50 text-purple-700 border border-purple-200';
    case 'return': return 'bg-rose-50 text-rose-700 border border-rose-200';
    case 'sign': return 'bg-blue-50 text-blue-700 border border-blue-200';
    default: return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
  }
};

const getActionTitle = (action) => {
  switch (action) {
    case 'approve': return 'Executive Final Approval';
    case 'return': return 'Returned for Revision';
    case 'sign': return 'Signed & Endorsed';
    case 'submit': return 'Submitted for Review';
    default: return 'Document Version';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const loadVersion = (ver) => {
  if (!ver) return;
  selectedVersionId.value = ver.id;
  emit('selectVersion', ver);
};

const resolveNote = async (note) => {
  try {
    const res = await axios.post(`/dashboard/task-annotations/${note.id}/resolve`);
    if (res.data?.success) {
      note.resolved_at = new Date().toISOString();
      Swal.fire({
        icon: 'success',
        title: 'Note Resolved',
        timer: 1500,
        showConfirmButton: false
      });
      emit('refresh');
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Action Failed',
      text: err.response?.data?.message || 'Could not resolve note.'
    });
  }
};
</script>
