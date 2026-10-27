<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-3xl shadow-2xl max-h-[92vh] flex flex-col w-[95vw] md:w-[700px] border border-gray-100 overflow-hidden' },
    content: { class: 'p-0 flex flex-col overflow-hidden h-full flex-1' },
    header: { class: 'hidden' },
    footer: { class: 'hidden' }
  }">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 px-6 py-5 flex items-center justify-between text-white shrink-0 relative overflow-hidden">
      <div class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
      <div class="flex items-center gap-3.5 relative z-10">
        <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30 shadow-inner">
          <Icon icon="lucide:send" width="24" class="text-white" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold tracking-tight text-white">Submit for Review</h2>
            <span class="px-2 py-0.5 text-[11px] font-semibold bg-white/20 backdrop-blur-md text-emerald-50 rounded-full border border-white/20">Staff Action</span>
          </div>
          <p class="text-emerald-100 text-xs mt-0.5 font-medium">Verify your PIN to stamp and forward this document</p>
        </div>
      </div>
      <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all hover:scale-105 active:scale-95 cursor-pointer">
        <Icon icon="lucide:x" width="18" />
      </button>
    </div>

    <!-- Body -->
    <div class="p-6 md:p-8 flex flex-col gap-6 overflow-y-auto max-h-[calc(92vh-160px)] custom-scrollbar bg-slate-50/50">
      
      <!-- Loaded Document Info Banner -->
      <div class="bg-white p-4 rounded-2xl border border-emerald-100 shadow-xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
            <Icon icon="lucide:file-text" width="20" />
          </div>
          <div class="min-w-0">
            <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Living Document Loaded</p>
            <h4 class="text-sm font-bold text-gray-800 truncate" :title="primaryDocTitle">{{ primaryDocTitle }}</h4>
          </div>
        </div>
        <div class="shrink-0 flex items-center gap-2">
          <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-100/70 text-emerald-800 rounded-lg">
            Version {{ (props.task?.latest_document_version?.version || 1) }}
          </span>
        </div>
      </div>

      <!-- Pre-Flight Readiness Checklist -->
      <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2.5 flex items-center gap-1.5">
          <Icon icon="lucide:check-square" width="14" class="text-emerald-600" />
          <span>Submission Pre-Flight Readiness</span>
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
          <div class="flex items-center gap-2 p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-xs font-semibold text-emerald-800">
            <Icon icon="lucide:check-circle-2" class="text-emerald-600 shrink-0" width="16" />
            <span>Living Docket Ready</span>
          </div>
          <div class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-semibold transition-all"
            :class="remark.trim() ? 'bg-emerald-50/70 border-emerald-100 text-emerald-800' : 'bg-gray-50 border-gray-200 text-gray-400'">
            <Icon :icon="remark.trim() ? 'lucide:check-circle-2' : 'lucide:circle'" :class="remark.trim() ? 'text-emerald-600' : 'text-gray-400'" class="shrink-0" width="16" />
            <span>Remark Provided</span>
          </div>
          <div class="flex items-center gap-2 p-2.5 rounded-xl border text-xs font-semibold transition-all"
            :class="pin.length >= 4 ? 'bg-emerald-50/70 border-emerald-100 text-emerald-800' : 'bg-gray-50 border-gray-200 text-gray-400'">
            <Icon :icon="pin.length >= 4 ? 'lucide:check-circle-2' : 'lucide:circle'" :class="pin.length >= 4 ? 'text-emerald-600' : 'text-gray-400'" class="shrink-0" width="16" />
            <span>PIN Entered</span>
          </div>
        </div>
      </div>

      <!-- Quick Remarks & Notes -->
      <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 flex items-center gap-1.5">
            <Icon icon="lucide:message-square" width="14" class="text-emerald-600" />
            Submission Remark
          </label>
          <span class="text-[11px] text-gray-400">Click quick chips or write your own</span>
        </div>

        <!-- Quick Pick Chips -->
        <div class="flex flex-wrap gap-2 pt-1">
          <button 
            type="button"
            v-for="(chip, i) in quickChips" 
            :key="i"
            @click="selectChip(chip)"
            class="text-xs px-3 py-1.5 rounded-xl border transition-all cursor-pointer select-none font-medium text-left"
            :class="remark === chip ? 'bg-emerald-50 border-emerald-300 text-emerald-700 shadow-2xs font-semibold' : 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 hover:border-gray-300'"
          >
            {{ chip }}
          </button>
        </div>

        <textarea 
          v-model="remark" 
          rows="3" 
          placeholder="Add comments or instructions for the reviewer..."
          class="w-full text-sm border border-gray-200 rounded-xl p-3.5 outline-none focus:border-emerald-500 focus:ring-3 focus:ring-emerald-500/15 transition-all text-gray-800 placeholder:text-gray-400 resize-none"
        ></textarea>
      </div>

      <!-- Optional Attachments Section -->
      <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold uppercase tracking-wider text-gray-600 flex items-center gap-1.5">
            <Icon icon="lucide:paperclip" width="14" class="text-emerald-600" />
            Supporting Attachments
          </label>
          <span class="text-[11px] text-gray-400">Optional</span>
        </div>

        <div class="relative border-2 border-dashed border-gray-200 hover:border-emerald-400 bg-gray-50/50 hover:bg-emerald-50/20 rounded-xl p-4 transition-all text-center cursor-pointer group">
          <input type="file" multiple @change="handleFiles" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" />
          <div class="flex flex-col items-center justify-center gap-1 pointer-events-none">
            <Icon icon="lucide:upload-cloud" width="24" class="text-gray-400 group-hover:text-emerald-600 transition-colors" />
            <p class="text-xs text-gray-600 font-medium"><span class="text-emerald-600 font-bold">Click to upload</span> or drag and drop files</p>
            <p class="text-[10px] text-gray-400">PDF, DOCX, PNG, JPG (max 20MB)</p>
          </div>
        </div>

        <!-- File Badges -->
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

      <!-- PIN Authorization Box -->
      <div class="bg-gradient-to-br from-amber-500/10 via-amber-50 to-orange-50 p-5 rounded-2xl border border-amber-200 shadow-inner flex flex-col items-center gap-3">
        <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
          <div class="w-7 h-7 rounded-lg bg-amber-200/80 text-amber-800 flex items-center justify-center">
            <Icon icon="lucide:shield-check" width="16" />
          </div>
          <span>Digital Signature PIN Authorization</span>
        </div>
        <p class="text-xs text-amber-700/90 text-center max-w-sm">Enter your secure PIN to digitally endorse this submission onto the minute sheet.</p>

        <div class="w-full max-w-xs relative mt-1">
          <input 
            type="password" 
            v-model="pin" 
            placeholder="••••••••" 
            autocomplete="off"
            @keyup.enter="submit"
            class="w-full text-center tracking-widest text-lg font-bold bg-white border-2 border-amber-300 focus:border-amber-500 focus:ring-4 focus:ring-amber-500/20 rounded-xl py-2 px-4 outline-none transition-all text-amber-950 placeholder:text-amber-200 shadow-sm"
          />
          <p v-if="pinError" class="text-red-500 text-xs font-medium mt-1.5 text-center flex items-center justify-center gap-1">
            <Icon icon="lucide:alert-circle" width="13" />
            {{ pinError }}
          </p>
        </div>
      </div>

    </div>

    <!-- Footer -->
    <div class="px-6 py-4 bg-white border-t border-gray-100 flex items-center justify-end gap-3 shrink-0">
      <button 
        type="button" 
        @click="closeModal" 
        class="px-5 py-2.5 rounded-xl font-medium text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer"
      >
        Cancel
      </button>
      <button 
        type="button" 
        @click="submit" 
        :disabled="isSubmitting || !pin"
        class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md hover:shadow-lg hover:shadow-emerald-600/20 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
      >
        <Icon v-if="isSubmitting" icon="lucide:loader-2" class="animate-spin" width="16" />
        <Icon v-else icon="lucide:check-circle-2" width="16" />
        <span>{{ isSubmitting ? 'Submitting...' : 'Sign & Submit Task' }}</span>
      </button>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  task: { type: Object, required: true },
});

const emit = defineEmits(['update:modelValue', 'submitted']);

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const quickChips = [
  'Draft completed as requested.',
  'Task deliverables completed and attached.',
  'Action item resolved for supervisor review.',
  'Forwarded for clearance and directive.'
];

const remark = ref('Draft completed as requested.');
const pin = ref('');
const pinError = ref('');
const attachments = ref([]);
const isSubmitting = ref(false);

const primaryDocTitle = computed(() => {
  if (props.task?.file?.file_name) return props.task.file.file_name;
  if (props.task?.taskdocs && props.task.taskdocs.length > 0) {
    const main = props.task.taskdocs.find(d => !d.file_name?.includes('Minute Sheet'));
    if (main) return main.file_name;
  }
  return props.task?.title || 'Main Task Document';
});

const selectChip = (chip) => {
  remark.value = chip;
};

const handleFiles = (e) => {
  const selected = Array.from(e.target.files || []);
  attachments.value = [...attachments.value, ...selected];
};

const removeFile = (index) => {
  attachments.value.splice(index, 1);
};

const closeModal = () => {
  pin.value = '';
  pinError.value = '';
  emit('update:modelValue', false);
};

const submit = async () => {
  if (!pin.value) {
    pinError.value = 'Please enter your digital signature PIN.';
    return;
  }

  isSubmitting.value = true;
  pinError.value = '';

  const formData = new FormData();
  formData.append('pin', pin.value);
  formData.append('remark', remark.value);
  attachments.value.forEach((file) => {
    formData.append('attachments[]', file);
  });

  try {
    const res = await axios.post(`/dashboard/tasks/${props.task.id}/submit-for-review`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'Task Submitted',
        text: res.data.message || 'Task submitted successfully for review.',
        timer: 2000,
        showConfirmButton: false
      });
      closeModal();
      emit('submitted', res.data.version);
    } else {
      pinError.value = res.data?.message || 'Submission failed.';
    }
  } catch (err) {
    pinError.value = err.response?.data?.message || 'An error occurred during submission.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>
