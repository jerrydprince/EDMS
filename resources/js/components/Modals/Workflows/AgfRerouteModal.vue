<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'p-6 max-h-[80vh] overflow-y-auto' },
    header: { class: 'bg-white px-6 py-5 border-b' },
    footer: { class: 'bg-gray-50 px-6 py-4 border-t rounded-b-2xl' },
  }" :style="{ width: '36rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }">
    <template #header>
      <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <Icon icon="lucide:git-fork" width="22" />
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-800">AGF Departmental Reroute</h2>
            <p class="text-xs text-gray-500">Reassign task to another department for a different function</p>
          </div>
        </div>
        <Icon icon="mingcute:close-line" width="22" @click="closeModal" class="cursor-pointer text-gray-400 hover:text-red-500 transition-colors" />
      </div>
    </template>

    <div class="flex flex-col gap-5">
      <!-- Target Department -->
      <div class="flex flex-col gap-2">
        <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
          <Icon icon="lucide:building-2" width="16" class="text-purple-600" />
          <span>Destination Department <span class="text-red-500">*</span></span>
        </label>
        <select 
          v-model="selectedDepartmentId" 
          class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all shadow-2xs"
        >
          <option value="" disabled>Select target department</option>
          <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.title }}</option>
        </select>
      </div>

      <!-- Directive note -->
      <div class="flex flex-col gap-2">
        <label class="text-sm font-semibold text-gray-700 flex items-center gap-2">
          <Icon icon="lucide:file-text" width="16" class="text-purple-600" />
          <span>AGF Directive / Instructions</span>
        </label>
        <textarea 
          v-model="directive" 
          rows="3" 
          placeholder="Specify the action and instructions for the department..."
          class="w-full border border-gray-200 rounded-xl p-3.5 text-sm text-gray-700 outline-none focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 transition-all resize-none shadow-2xs bg-gray-50/50"
        ></textarea>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 w-full">
        <button @click="closeModal" class="px-5 py-2 text-sm text-gray-600 font-medium rounded-xl hover:bg-gray-200 transition-colors">
          Cancel
        </button>
        <button 
          @click="submitReroute" 
          :disabled="!selectedDepartmentId || isSubmitting" 
          class="px-6 py-2 text-sm bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-xl transition-all shadow-md shadow-purple-500/20 flex items-center gap-2 disabled:opacity-50"
        >
          <Icon v-if="isSubmitting" icon="lucide:loader-2" class="animate-spin" width="16" />
          <Icon v-else icon="lucide:send" width="16" />
          <span>Reroute Task</span>
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import axios from 'axios';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  task: { type: Object, required: true },
  assignment: { type: Object, required: true },
});

const emit = defineEmits(['update:modelValue', 'confirmed']);

const departments = ref([]);
const selectedDepartmentId = ref('');
const directive = ref('');
const isSubmitting = ref(false);

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const closeModal = () => {
  selectedDepartmentId.value = '';
  directive.value = '';
  emit('update:modelValue', false);
};

const fetchDepartments = () => {
  axios.get('/dashboard/get-branches-by-role').then(res => {
    if (Array.isArray(res.data)) {
      departments.value = res.data;
    } else if (res.data && res.data.departments) {
      departments.value = res.data.departments;
    }
  }).catch(() => {
    axios.get('/dashboard/search-users', { params: { search: '' } }).then(res => {
      if (res.data && res.data.type === 'department') {
        departments.value = res.data.data;
      }
    });
  });
};

onMounted(() => {
  fetchDepartments();
});

const submitReroute = () => {
  if (!selectedDepartmentId.value) return;
  isSubmitting.value = true;
  emit('confirmed', {
    reroute_department_id: selectedDepartmentId.value,
    directive: directive.value || 'Rerouted by AGF for cross-departmental execution.',
    onDone: () => {
      isSubmitting.value = false;
      closeModal();
    }
  });
};
</script>
