<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'p-6 max-h-[80vh] overflow-y-auto' },
    header: { class: 'bg-white px-6 py-5 border-b' },
    footer: { class: 'bg-gray-50 px-6 py-4 border-t rounded-b-2xl' },
  }" :style="{ width: '38rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <Icon icon="mdi:check-decagram" width="24" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-800">{{ approveModalTitle }}</h2>
            <p class="text-xs text-gray-500">Official hierarchical review & endorsement</p>
          </div>
        </div>
        <Icon icon="mingcute:close-line" width="22" @click="closeModal" class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors" />
      </div>
    </template>

    <div class="flex flex-col gap-5">
      <!-- Routing Flow Banner -->
      <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-blue-50 border border-emerald-200/80 rounded-2xl p-4 shadow-2xs">
        <div class="flex items-center justify-between gap-2 mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Review Routing Chain</span>
          <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-600 text-white">Next Step</span>
        </div>
        <div class="flex items-center gap-3 mt-2">
          <div class="flex flex-col">
            <span class="text-xs text-gray-500">From Reviewer</span>
            <span class="text-sm font-bold text-gray-800">{{ currentUser?.first_name }} {{ currentUser?.last_name }}</span>
            <span class="text-[11px] text-gray-600">{{ currentRoleTitle }}</span>
          </div>
          <Icon icon="lucide:arrow-right" class="text-emerald-600 mx-2" width="20" />
          <div class="flex flex-col">
            <span class="text-xs text-gray-500">To Authority</span>
            <span class="text-sm font-bold text-emerald-900">{{ destinationAuthority }}</span>
            <span class="text-[11px] text-emerald-700">{{ destinationSubtext }}</span>
          </div>
        </div>
      </div>

      <!-- Endorsement / Minute Directive -->
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <Icon icon="lucide:file-edit" width="16" class="text-emerald-600" />
            <span>Executive Minute Directive / Remarks</span>
          </label>
          <span class="text-[11px] text-gray-400">Click preset to populate</span>
        </div>

        <!-- Quick Minute Chips -->
        <div class="flex flex-wrap gap-2 pt-1">
          <button 
            type="button"
            v-for="(chip, i) in approvalPresets" 
            :key="i"
            @click="selectPreset(chip)"
            class="text-xs px-2.5 py-1.5 rounded-xl border transition-all cursor-pointer select-none font-medium text-left"
            :class="directive.includes(chip) ? 'bg-emerald-50 border-emerald-300 text-emerald-700 shadow-2xs font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 hover:border-gray-300'"
          >
            {{ chip }}
          </button>
        </div>

        <textarea 
          v-model="directive" 
          rows="3" 
          placeholder="e.g., Reviewed and verified. Recommended for further consideration and approval."
          class="w-full border border-gray-200 rounded-xl p-3.5 text-sm text-gray-700 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all resize-none shadow-2xs bg-gray-50/50 mt-1"
        ></textarea>
      </div>

      <!-- Optional Attachments Section -->
      <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
          <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <Icon icon="lucide:paperclip" width="16" class="text-emerald-600" />
            <span>Supporting Attachments (Optional)</span>
          </label>
          <span class="text-[11px] text-gray-400">PDF, DOCX, PNG, JPG (max 20MB)</span>
        </div>
        <div class="relative border-2 border-dashed border-gray-200 hover:border-emerald-400 bg-gray-50/50 hover:bg-emerald-50/20 rounded-xl p-3 transition-all text-center cursor-pointer group">
          <input type="file" multiple @change="handleFiles" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" />
          <div class="flex flex-col items-center justify-center gap-1 pointer-events-none">
            <Icon icon="lucide:upload-cloud" width="20" class="text-gray-400 group-hover:text-emerald-600 transition-colors" />
            <p class="text-xs text-gray-600 font-medium"><span class="text-emerald-600 font-bold">Click to upload</span> or drag and drop files</p>
          </div>
        </div>

        <div v-if="attachments.length > 0" class="flex flex-wrap gap-2 pt-1">
          <div v-for="(file, idx) in attachments" :key="idx" class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-1.5 rounded-lg text-xs font-medium">
            <Icon icon="lucide:file" width="14" />
            <span class="truncate max-w-[180px]">{{ file.name }}</span>
            <button @click="removeFile(idx)" type="button" class="text-emerald-600 hover:text-red-600 transition-colors cursor-pointer ml-1">
              <Icon icon="lucide:x" width="14" />
            </button>
          </div>
        </div>
      </div>

      <!-- Digital Signature PIN -->
      <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-4 rounded-xl border border-amber-200/80 shadow-2xs">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:key-round" width="16" class="text-amber-600" />
            <span class="text-xs font-bold uppercase tracking-wider text-amber-900">Digital Signature Authorization</span>
          </div>
          <span class="text-[11px] text-amber-700">Enter Security PIN</span>
        </div>
        <input 
          v-model="pin" 
          type="password" 
          placeholder="••••" 
          maxlength="8" 
          class="w-full text-center tracking-widest text-lg font-bold bg-white border border-amber-200 rounded-xl py-2 px-3 outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 text-amber-900" 
        />
        <p v-if="pinError" class="text-red-500 text-xs mt-1.5 font-medium text-center">{{ pinError }}</p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <button @click="closeModal" class="px-5 py-2 text-sm text-gray-600 font-medium rounded-xl hover:bg-gray-200 transition-colors">
          Cancel
        </button>
        <button 
          @click="submitApproval" 
          :disabled="isSubmitting" 
          class="px-6 py-2 text-sm bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all shadow-md shadow-emerald-500/20 flex items-center gap-2 disabled:opacity-50"
        >
          <Icon v-if="isSubmitting" icon="lucide:loader-2" class="animate-spin" width="16" />
          <Icon v-else icon="mdi:check-decagram" width="16" />
          <span>{{ isAgf ? 'Final Approval' : approveModalTitle }}</span>
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  task: { type: Object, required: true },
  assignment: { type: Object, required: true },
  modifiedFile: { type: Object, default: null },
  modifiedFilePath: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue', 'confirmed']);

const page = usePage();
const currentUser = computed(() => page.props.user);
const roles = computed(() => page.props.roles || []);

const directive = ref('');

const approvalPresets = [
  'Reviewed and verified. Recommended for approval.',
  'Content audited and cleared. Passed for necessary action.',
  'Subordinate submission verified against SOP. Approved.',
  'Cleared for final endorsement and dispatch.'
];

const selectPreset = (preset) => {
  if (!directive.value) {
    directive.value = preset;
  } else if (!directive.value.includes(preset)) {
    directive.value = directive.value + ' ' + preset;
  }
};
const pin = ref('');
const pinError = ref('');
const isSubmitting = ref(false);
const attachments = ref([]);

const handleFiles = (e) => {
  const selected = Array.from(e.target.files || []);
  attachments.value.push(...selected);
};

const removeFile = (idx) => {
  attachments.value.splice(idx, 1);
};

const isAgf = computed(() => roles.value.includes('super user') || roles.value.includes('Accountant-General'));
const isDirector = computed(() => roles.value.includes('director'));
const isDeputyDirector = computed(() => roles.value.includes('deputy director'));
const isAssistantDirector = computed(() => roles.value.includes('assistant director'));

const approveModalTitle = computed(() => {
  if (isAgf.value) return 'Final Executive Approval';
  if (isDirector.value) return 'Submit to AGF';
  if (isDeputyDirector.value) return 'Submit to Director';
  if (isAssistantDirector.value) return 'Submit to Deputy Director';
  return 'Submit to Assigner';
});

const currentRoleTitle = computed(() => {
  if (isAgf.value) return 'Accountant-General (AGF)';
  if (isDirector.value) return 'Director (Department Head)';
  if (isDeputyDirector.value) return 'Deputy Director (Division Head)';
  if (isAssistantDirector.value) return 'Assistant Director (Unit Head)';
  return 'Reviewing Officer';
});

const destinationAuthority = computed(() => {
  if (isAgf.value) return 'System Executive Archive';
  if (isDirector.value) return 'Accountant-General of the Federation (AGF)';
  if (isDeputyDirector.value) return 'Director (Head of Department)';
  if (isAssistantDirector.value) return 'Deputy Director (Head of Division)';
  return 'Immediate Supervisor';
});

const destinationSubtext = computed(() => {
  if (isAgf.value) return 'Final closure and official executive completion';
  if (isDirector.value) return 'Executive Review & Final Approval';
  if (isDeputyDirector.value) return 'Departmental Verification & Forwarding';
  if (isAssistantDirector.value) return 'Divisional Supervision & Review';
  return 'Hierarchy Upward Escalation';
});

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const closeModal = () => {
  pin.value = '';
  pinError.value = '';
  attachments.value = [];
  emit('update:modelValue', false);
};

const submitApproval = () => {
  pinError.value = '';
  isSubmitting.value = true;
  emit('confirmed', {
    directive: directive.value || (isAgf.value ? 'Final Executive Approval granted.' : 'Task submission reviewed and approved.'),
    pin: pin.value,
    attachments: attachments.value,
    onDone: (error) => {
      isSubmitting.value = false;
      if (error) {
        pinError.value = error;
      } else {
        closeModal();
      }
    }
  });
};
</script>
