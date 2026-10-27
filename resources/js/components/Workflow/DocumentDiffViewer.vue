<template>
  <Dialog 
    :visible="modelValue" 
    @update:visible="$emit('update:modelValue', $event)"
    modal 
    maximizable
    :style="{ width: '95vw', maxWidth: '1600px' }" 
    :breakpoints="{ '1199px': '98vw', '575px': '100vw' }"
    :pt="{
      root: { class: 'bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 font-sans' },
      header: { class: 'bg-gradient-to-r from-slate-50 via-white to-blue-50/50 px-6 py-4 border-b border-gray-200' },
      content: { class: 'p-4 bg-slate-100/70 h-[80vh] flex flex-col' }
    }"
  >
    <!-- Modal Header -->
    <template #header>
      <div class="flex items-center justify-between w-full pr-6 gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-xs shrink-0">
            <Icon icon="lucide:columns-2" width="20" />
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h3 class="font-bold text-gray-900 text-base md:text-lg tracking-tight">Side-by-Side Version Comparison</h3>
              <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                Visual Diff
              </span>
            </div>
            <p class="text-xs text-gray-500 truncate">Compare document iterations, subordinate submissions, and official minute sheets</p>
          </div>
        </div>

        <!-- Sync Nav Pill & Close -->
        <div class="flex items-center gap-3">
          <button 
            type="button" 
            @click="syncNav = !syncNav"
            class="px-3 py-1.5 rounded-xl border text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer shadow-2xs"
            :class="syncNav ? 'bg-blue-600 text-white border-blue-600 shadow-blue-200' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
            title="When active, changing page in one viewer moves both"
          >
            <Icon :icon="syncNav ? 'lucide:link' : 'lucide:link-2-off'" width="14" />
            <span>{{ syncNav ? 'Sync Nav: ON' : 'Sync Nav: OFF' }}</span>
          </button>
        </div>
      </div>
    </template>

    <!-- Side-by-Side Dual Viewport Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 flex-1 min-h-0">
      <!-- LEFT PANE: Base Document -->
      <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm flex flex-col overflow-hidden">
        <!-- Left Pane Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-slate-50 to-white border-b border-gray-100 flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-200 text-slate-700">
              Base
            </span>
            <select 
              v-model="leftDocPath" 
              class="text-xs font-semibold text-gray-800 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 outline-none focus:border-blue-400 max-w-full truncate cursor-pointer"
            >
              <option v-for="(doc, idx) in availableDocuments" :key="'left-' + idx" :value="doc.path">
                {{ doc.label }}
              </option>
            </select>
          </div>

          <!-- Left Page Nav Controls -->
          <div class="flex items-center gap-1.5 shrink-0">
            <button 
              type="button"
              @click="changeLeftPage(-1)" 
              class="p-1 rounded-md text-gray-500 hover:bg-gray-100 disabled:opacity-30 cursor-pointer"
              :disabled="leftPage <= 1"
            >
              <Icon icon="lucide:chevron-left" width="16" />
            </button>
            <span class="text-xs font-mono font-medium text-gray-600">Page {{ leftPage }}</span>
            <button 
              type="button"
              @click="changeLeftPage(1)" 
              class="p-1 rounded-md text-gray-500 hover:bg-gray-100 cursor-pointer"
            >
              <Icon icon="lucide:chevron-right" width="16" />
            </button>
          </div>
        </div>

        <!-- Left Viewport (Embedded Iframe / PDF Viewer) -->
        <div class="flex-1 bg-slate-100 relative min-h-0">
          <iframe 
            v-if="leftDocUrl" 
            :src="leftDocUrl + '#page=' + leftPage" 
            class="w-full h-full border-0" 
            title="Base Document Viewer"
          ></iframe>
          <div v-else class="w-full h-full flex flex-col items-center justify-center p-8 text-center text-gray-400">
            <Icon icon="lucide:file-question" width="36" class="mb-2 opacity-50" />
            <p class="text-xs font-medium">Select a base document to preview</p>
          </div>
        </div>
      </div>

      <!-- RIGHT PANE: Comparison / Revised Document -->
      <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm flex flex-col overflow-hidden">
        <!-- Right Pane Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-blue-50/50 to-white border-b border-gray-100 flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-blue-100 text-blue-800">
              Revision
            </span>
            <select 
              v-model="rightDocPath" 
              class="text-xs font-semibold text-gray-800 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 outline-none focus:border-blue-400 max-w-full truncate cursor-pointer"
            >
              <option v-for="(doc, idx) in availableDocuments" :key="'right-' + idx" :value="doc.path">
                {{ doc.label }}
              </option>
            </select>
          </div>

          <!-- Right Page Nav Controls -->
          <div class="flex items-center gap-1.5 shrink-0">
            <button 
              type="button"
              @click="changeRightPage(-1)" 
              class="p-1 rounded-md text-gray-500 hover:bg-gray-100 disabled:opacity-30 cursor-pointer"
              :disabled="rightPage <= 1"
            >
              <Icon icon="lucide:chevron-left" width="16" />
            </button>
            <span class="text-xs font-mono font-medium text-gray-600">Page {{ rightPage }}</span>
            <button 
              type="button"
              @click="changeRightPage(1)" 
              class="p-1 rounded-md text-gray-500 hover:bg-gray-100 cursor-pointer"
            >
              <Icon icon="lucide:chevron-right" width="16" />
            </button>
          </div>
        </div>

        <!-- Right Viewport -->
        <div class="flex-1 bg-slate-100 relative min-h-0">
          <iframe 
            v-if="rightDocUrl" 
            :src="rightDocUrl + '#page=' + rightPage" 
            class="w-full h-full border-0" 
            title="Comparison Document Viewer"
          ></iframe>
          <div v-else class="w-full h-full flex flex-col items-center justify-center p-8 text-center text-gray-400">
            <Icon icon="lucide:file-question" width="36" class="mb-2 opacity-50" />
            <p class="text-xs font-medium">Select a comparison document to preview</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Footer -->
    <template #footer>
      <div class="flex items-center justify-between w-full pt-2">
        <span class="text-xs text-gray-500">
          Comparing <strong class="text-gray-800">{{ leftDocLabel }}</strong> with <strong class="text-gray-800">{{ rightDocLabel }}</strong>
        </span>
        <div class="flex items-center gap-2">
          <button 
            type="button"
            @click="$emit('update:modelValue', false)"
            class="px-4 py-2 rounded-xl text-gray-600 hover:bg-gray-100 text-xs font-semibold transition-colors cursor-pointer"
          >
            Close Diff View
          </button>
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  task: {
    type: Object,
    required: true
  }
});

defineEmits(['update:modelValue']);

const page = usePage();
const syncNav = ref(true);
const leftPage = ref(1);
const rightPage = ref(1);

const leftDocPath = ref('');
const rightDocPath = ref('');

// Build available documents list for comparison
const availableDocuments = computed(() => {
  const docs = [];
  if (!props.task) return docs;

  // 1. Task Document Versions
  if (props.task.document_versions && props.task.document_versions.length > 0) {
    props.task.document_versions.forEach(v => {
      docs.push({
        label: `Main Document (v${v.version})${v.created_by_user ? ' - ' + v.created_by_user.first_name : ''}`,
        path: v.path
      });
    });
  }

  // 2. Base Task File
  if (props.task.file?.path && !docs.some(d => d.path === props.task.file.path)) {
    docs.push({
      label: 'Initial Task Document (v1)',
      path: props.task.file.path
    });
  }

  // 3. Submissions from subordinate assignments
  if (props.task.assignments && props.task.assignments.length > 0) {
    props.task.assignments.forEach(a => {
      if (a.task_submissions && a.task_submissions.length > 0) {
        a.task_submissions.forEach(sub => {
          if (sub.task_submission_files && sub.task_submission_files.length > 0) {
            sub.task_submission_files.forEach(sf => {
              const u = a.assigned_to;
              const name = u ? `${u.first_name || ''} ${u.last_name || ''}`.trim() : 'Staff';
              docs.push({
                label: `Submission: ${sf.file_name || sf.name || 'Output'} (${name})`,
                path: sf.path
              });
            });
          }
        });
      }
    });
  }

  // 4. Task Supporting Docs
  if (props.task.taskdocs && props.task.taskdocs.length > 0) {
    props.task.taskdocs.forEach(td => {
      if (!docs.some(d => d.path === td.path)) {
        docs.push({
          label: `Attachment: ${td.file_name || td.name || 'Document'}`,
          path: td.path
        });
      }
    });
  }

  return docs;
});

// Auto-select left (base) and right (latest revision) upon opening
watch(() => props.modelValue, (isOpen) => {
  if (isOpen && availableDocuments.value.length > 0) {
    leftDocPath.value = availableDocuments.value[0].path;
    if (availableDocuments.value.length > 1) {
      rightDocPath.value = availableDocuments.value[availableDocuments.value.length - 1].path;
    } else {
      rightDocPath.value = availableDocuments.value[0].path;
    }
  }
}, { immediate: true });

const getFullUrl = (path) => {
  if (!path) return '';
  const base = page.props.base || '';
  return path.startsWith('/') ? `${base}${path}` : `${base}/${path}`;
};

const leftDocUrl = computed(() => getFullUrl(leftDocPath.value));
const rightDocUrl = computed(() => getFullUrl(rightDocPath.value));

const leftDocLabel = computed(() => {
  const d = availableDocuments.value.find(x => x.path === leftDocPath.value);
  return d ? d.label : 'Base Document';
});

const rightDocLabel = computed(() => {
  const d = availableDocuments.value.find(x => x.path === rightDocPath.value);
  return d ? d.label : 'Comparison Document';
});

// Page Navigation
const changeLeftPage = (delta) => {
  leftPage.value = Math.max(1, leftPage.value + delta);
  if (syncNav.value) {
    rightPage.value = leftPage.value;
  }
};

const changeRightPage = (delta) => {
  rightPage.value = Math.max(1, rightPage.value + delta);
  if (syncNav.value) {
    leftPage.value = rightPage.value;
  }
};
</script>
