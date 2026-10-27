<template>
  <Dialog v-if="visible" v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-3xl shadow-2xl max-h-[92vh] flex flex-col w-[95vw] md:w-[760px] border border-gray-100 overflow-hidden' },
    content: { class: 'p-0 flex flex-col overflow-hidden h-full flex-1' },
    header: { class: 'hidden' },
    footer: { class: 'hidden' }
  }">
    <!-- Modal Header -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-900 px-6 py-5 flex items-center justify-between text-white shrink-0 relative overflow-hidden">
      <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
      <div class="flex items-center gap-3.5 relative z-10">
        <div class="w-12 h-12 bg-white/15 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20 shadow-inner">
          <Icon icon="lucide:file-text" width="24" class="text-white" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-lg md:text-xl font-bold tracking-tight text-white">Generate Official Document</h2>
            <span class="px-2.5 py-0.5 text-[10px] font-bold bg-white/20 text-white rounded-full uppercase tracking-wider">Templates Studio</span>
          </div>
          <p class="text-blue-100 text-xs mt-0.5 font-medium">Select a standardized civil service template to generate and attach to this docket</p>
        </div>
      </div>
      <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all hover:scale-105 active:scale-95 cursor-pointer">
        <Icon icon="lucide:x" width="18" />
      </button>
    </div>

    <!-- Modal Body -->
    <div class="p-6 md:p-8 flex flex-col gap-5 overflow-y-auto max-h-[calc(92vh-150px)] custom-scrollbar bg-slate-50/50">

      <!-- Loading State -->
      <div v-if="isLoadingTemplates" class="py-12 flex flex-col items-center justify-center gap-3 text-slate-500">
        <Icon icon="lucide:loader-2" class="animate-spin text-blue-600" width="32" />
        <p class="text-xs font-semibold">Loading available document templates...</p>
      </div>

      <template v-else>
        <!-- Template Selection Section -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
            1. Select Document Template
          </label>
          
          <div class="relative">
            <select 
              v-model="selectedTemplateId" 
              @change="onTemplateChange" 
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-sm font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all cursor-pointer appearance-none"
            >
              <option :value="null" disabled>-- Choose an official template --</option>
              <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">
                {{ tpl.title }} {{ tpl.department_id ? '(Directorate Specific)' : '(Institutional)' }}
              </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
              <Icon icon="lucide:chevron-down" width="18" />
            </div>
          </div>

          <!-- Template Badges / Info -->
          <div v-if="selectedTemplate" class="mt-3 flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100">
            <span class="text-xs text-slate-500 font-medium">Scope:</span>
            <span v-if="selectedTemplate.department_id" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200">
              Departmental Scope
            </span>
            <span v-else class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
              Institutional / Standard OAGF
            </span>
            <span v-if="selectedTemplate.type" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 uppercase">
              {{ selectedTemplate.type }}
            </span>
            <span class="text-[11px] text-slate-400 font-mono ml-auto">v{{ selectedTemplate.version || 1 }}</span>
          </div>
        </div>

        <!-- Placeholder Inputs Section -->
        <div v-if="selectedTemplate && editablePlaceholders.length > 0" class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
          <div class="flex items-center justify-between mb-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                2. Customize Document Placeholders
              </label>
              <p class="text-xs text-slate-500">Standard metadata (officer name, docket ID, department) is auto-populated. Customize any specific fields below:</p>
            </div>
            <span class="text-[11px] font-semibold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-md border border-blue-100">
              {{ editablePlaceholders.length }} variables
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <div v-for="ph in editablePlaceholders" :key="ph" class="flex flex-col gap-1">
              <label :for="'ph-' + ph" class="text-xs font-semibold text-slate-700 flex items-center justify-between">
                <span>{{ formatPlaceholderLabel(ph) }}</span>
                <span class="text-[10px] font-mono text-slate-400 font-normal" v-text="'{{ ' + ph + ' }}'"></span>
              </label>
              <input 
                :id="'ph-' + ph"
                v-model="form.placeholders[ph]" 
                type="text"
                :placeholder="'Enter ' + formatPlaceholderLabel(ph).toLowerCase() + '...'"
                class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs md:text-sm font-medium text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
              />
            </div>
          </div>
        </div>

        <!-- Live Document Preview Section -->
        <div v-if="selectedTemplate" class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <Icon icon="lucide:eye" width="16" class="text-blue-600" />
              <label class="text-xs font-bold uppercase tracking-wider text-slate-700">
                Document Live Preview
              </label>
            </div>
            <span class="text-xs text-slate-500">Will be converted to official PDF</span>
          </div>

          <div class="p-5 border border-slate-200 rounded-xl bg-slate-50/70 max-h-[320px] overflow-y-auto custom-scrollbar shadow-inner text-sm font-normal">
            <div class="bg-white p-6 rounded-lg shadow-xs border border-slate-100" v-html="renderedPreviewHtml"></div>
          </div>
        </div>
      </template>

    </div>

    <!-- Modal Footer -->
    <div class="px-6 py-4 bg-white border-t border-slate-100 flex items-center justify-between shrink-0">
      <button 
        type="button" 
        @click="closeModal" 
        class="px-4 py-2.5 text-xs md:text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-all cursor-pointer"
        :disabled="loading"
      >
        Cancel
      </button>

      <button 
        type="button" 
        @click="generateDocument" 
        :disabled="loading || !selectedTemplate"
        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold text-white shadow-md transition-all cursor-pointer"
        :class="loading || !selectedTemplate ? 'bg-slate-300 cursor-not-allowed shadow-none' : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-blue-500/20 active:scale-95'"
      >
        <Icon v-if="loading" icon="lucide:loader-2" width="16" class="animate-spin text-white" />
        <Icon v-else icon="lucide:file-plus" width="16" class="text-white" />
        <span>{{ loading ? 'Generating PDF...' : 'Generate & Attach Document' }}</span>
      </button>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";

const props = defineProps({
  modelValue: Boolean,
  taskId: [Number, String]
});

const emit = defineEmits(['update:modelValue', 'generated']);
const toast = useToast();

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const templates = ref([]);
const selectedTemplateId = ref(null);
const selectedTemplate = computed(() => {
  return templates.value.find(t => t.id === selectedTemplateId.value) || null;
});

const isLoadingTemplates = ref(false);
const loading = ref(false);
const baseHydratedHtml = ref('');

const form = ref({
  placeholders: {}
});

// Auto-hydrated placeholders that are populated automatically by the system
const autoHydratedKeys = [
  'tracking_id', 'docket_number', 'docket_no', 'title', 'subject', 
  'description', 'officer_name', 'user_name', 'officer_role', 'user_role', 
  'department', 'department_name', 'agf_directive', 'date', 'today', 
  'year', 'external_ref', 'reference_no', 'sender_name', 'sender'
];

// Placeholders extracted from template
const editablePlaceholders = ref([]);

// Watch for modal visibility to load templates
watch(visible, async (isOpen) => {
  if (isOpen) {
    // Clean any orphaned dialog masks from prior HMR
    document.querySelectorAll('.p-dialog-mask:not(:has(.p-dialog))').forEach(el => el.remove());

    if (templates.value.length === 0) {
      isLoadingTemplates.value = true;
      try {
        const res = await axios.get('/document-templates/get').catch(() => axios.get('/dashboard/document-templates/get'));
        templates.value = Array.isArray(res.data) ? res.data : [];
      } catch (err) {
        console.error('Failed to load document templates:', err);
        if (toast) {
          toast.add({
            severity: 'error',
            summary: 'Templates Error',
            detail: 'Unable to load document templates.',
            life: 3000
          });
        }
      } finally {
        isLoadingTemplates.value = false;
      }
    }
  } else {
    selectedTemplateId.value = null;
    form.value.placeholders = {};
    editablePlaceholders.value = [];
    baseHydratedHtml.value = '';
  }
});

const formatPlaceholderLabel = (key) => {
  if (!key) return '';
  return key.replace(/[_-]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const onTemplateChange = async () => {
  editablePlaceholders.value = [];
  form.value.placeholders = {};
  baseHydratedHtml.value = '';

  if (!selectedTemplate.value) return;

  const rawHtml = selectedTemplate.value.content_html || '';

  // Extract all placeholders {{ placeholder }}
  const regex = /\{\{\s*([\w]+)\s*\}\}/g;
  let match;
  const found = [];
  while ((match = regex.exec(rawHtml)) !== null) {
    found.push(match[1]);
  }
  const uniqueKeys = [...new Set(found)];

  // Separate non-auto placeholders or all placeholders for user edit
  editablePlaceholders.value = uniqueKeys.filter(k => !autoHydratedKeys.includes(k.toLowerCase()));
  // If no custom placeholders, expose general ones if helpful (amount, deadline)
  if (editablePlaceholders.value.length === 0) {
    editablePlaceholders.value = uniqueKeys.filter(k => ['amount', 'deadline', 'recipient', 'remark'].includes(k.toLowerCase()));
  }

  editablePlaceholders.value.forEach(k => {
    form.value.placeholders[k] = '';
  });

  // Try hydrating base template with current task data for live preview
  if (props.taskId && selectedTemplate.value.id) {
    try {
      const res = await axios.get(`/templates/${selectedTemplate.value.id}/hydrate?task_id=${props.taskId}`)
        .catch(() => axios.get(`/dashboard/templates/${selectedTemplate.value.id}/hydrate?task_id=${props.taskId}`));
      if (res.data && res.data.hydrated_html) {
        baseHydratedHtml.value = res.data.hydrated_html;
      }
    } catch (err) {
      console.warn('Hydration preview fallback:', err);
      baseHydratedHtml.value = rawHtml;
    }
  } else {
    baseHydratedHtml.value = rawHtml;
  }
};

const renderedPreviewHtml = computed(() => {
  if (!selectedTemplate.value) return '';
  let html = baseHydratedHtml.value || selectedTemplate.value.content_html || '';

  // Replace user-entered placeholders
  Object.keys(form.value.placeholders).forEach(key => {
    const val = form.value.placeholders[key];
    const displayVal = val 
      ? `<span class="bg-amber-100 text-amber-900 font-semibold px-1 rounded">${val}</span>` 
      : `<span class="bg-blue-100 text-blue-800 font-mono text-xs px-1 rounded">[${formatPlaceholderLabel(key)}]</span>`;
    
    html = html.replace(new RegExp(`\\{\\{\\s*${key}\\s*\\}\\}`, 'gi'), displayVal);
  });

  return html;
});

const closeModal = () => {
  selectedTemplateId.value = null;
  form.value.placeholders = {};
  editablePlaceholders.value = [];
  baseHydratedHtml.value = '';
  visible.value = false;
};

const generateDocument = async () => {
  if (!selectedTemplate.value || !props.taskId) {
    if (toast) {
      toast.add({ severity: 'warn', summary: 'Missing Information', detail: 'Please select a template.', life: 3000 });
    }
    return;
  }

  loading.value = true;
  try {
    const postData = {
      template_id: selectedTemplate.value.id,
      placeholders: form.value.placeholders
    };
    const res = await axios.post(`/task/${props.taskId}/document/generate`, postData)
      .catch(() => axios.post(`/dashboard/task/${props.taskId}/document/generate`, postData));

    if (toast) {
      toast.add({
        severity: 'success',
        summary: 'Document Generated',
        detail: res.data.message || 'Official document generated and attached successfully.',
        life: 3500
      });
    }

    emit('generated', res.data.taskDoc);
    closeModal();
  } catch (err) {
    console.error('Error generating document:', err);
    const msg = err.response?.data?.error || err.response?.data?.message || 'Failed to generate document';
    if (toast) {
      toast.add({
        severity: 'error',
        summary: 'Generation Failed',
        detail: msg,
        life: 4000
      });
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
