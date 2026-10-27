<template>
  <Dialog
    v-model:visible="modalOpen"
    modal
    :dismissableMask="true"
    :closable="true"
    :header="'Batch Assign Tasks (' + selectedTasks.length + ')'"
    :style="{ width: '92vw', maxWidth: '640px' }"
    :pt="{
      root: { class: 'bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden' },
      header: { class: 'bg-gradient-to-r from-slate-50 to-blue-50/50 p-6 border-b border-gray-100 font-bold text-gray-800' },
      content: { class: 'p-6 max-h-[80vh] overflow-y-auto' }
    }"
  >
    <form @submit.prevent="submitBatchAssign" class="space-y-6">
      <!-- Selected Tasks Summary Card -->
      <div class="bg-blue-50/60 border border-blue-200/80 rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider text-blue-800 flex items-center gap-1.5">
            <Icon icon="lucide:layers" width="14" />
            <span>Target Tasks Selected ({{ selectedTasks.length }})</span>
          </span>
          <span class="text-xs text-blue-600 font-medium">Batch Action</span>
        </div>
        <div class="max-h-28 overflow-y-auto space-y-1.5 pr-1 custom-scrollbar">
          <div 
            v-for="t in selectedTasks" 
            :key="t.id"
            class="text-xs bg-white/90 border border-blue-100 px-3 py-1.5 rounded-lg flex items-center justify-between text-gray-700 font-medium"
          >
            <span class="truncate max-w-[380px]">{{ t.title }}</span>
            <span v-if="t.tracking_id" class="text-[10px] font-mono text-gray-400 shrink-0">#{{ t.tracking_id }}</span>
          </div>
        </div>
      </div>

      <!-- Assignee / Recipient Selection -->
      <div class="space-y-2">
        <label class="block text-sm font-bold text-gray-700 flex items-center gap-1.5">
          <Icon icon="lucide:user-plus" class="text-primary" width="16" />
          <span>Assign To (Director / Department / Officer) <span class="text-red-500">*</span></span>
        </label>
        
        <MultiSelect
          v-model="selectedUsers"
          :options="usersList"
          optionLabel="display_name"
          placeholder="Search target Director or Department..."
          :filter="true"
          display="chip"
          class="w-full text-left bg-white border border-gray-200 rounded-xl hover:border-blue-400 focus:border-blue-400 transition-all outline-none"
          :pt="{
            root: { class: 'w-full text-left bg-white border border-gray-200 rounded-xl hover:border-blue-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all outline-none flex items-center justify-between' },
            label: { class: 'py-2.5 px-3 text-sm flex-1' },
            trigger: { class: 'w-10 flex items-center justify-center text-gray-400' },
            panel: { class: 'bg-white shadow-xl rounded-xl border border-gray-100 mt-2 p-1 overflow-hidden z-50' },
            header: { class: 'p-2 border-b border-gray-100 flex items-center justify-between gap-2' },
            filterInput: { class: 'w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:border-blue-400 transition-all outline-none' },
            wrapper: { class: 'max-h-[220px] overflow-auto py-1' },
            item: ({ context }) => ({ class: ['px-3 py-2 hover:bg-gray-50 transition-colors flex items-center gap-3 rounded-lg mx-1 cursor-pointer text-sm', context.selected ? 'bg-blue-50/60 text-primary font-semibold' : 'text-gray-700'] })
          }"
        >
          <template #option="slotProps">
            <div class="flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                {{ (slotProps.option.display_name || 'U')[0] }}
              </div>
              <p class="text-sm font-medium">{{ slotProps.option.display_name }}</p>
            </div>
          </template>
        </MultiSelect>
        <p v-if="formErrors.users" class="text-xs text-red-500 font-medium">{{ formErrors.users }}</p>
      </div>

      <!-- Priority & Due Date Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Priority -->
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 flex items-center gap-1.5">
            <Icon icon="lucide:flag" class="text-orange-500" width="16" />
            <span>Priority</span>
          </label>
          <div class="flex items-center gap-2">
            <button
              type="button"
              v-for="p in priorities"
              :key="p.value"
              @click="form.priority = p.value"
              class="flex-1 py-2 text-xs font-bold rounded-xl border transition-all text-center"
              :class="form.priority === p.value ? p.activeClass : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <!-- Deadline -->
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-700 flex items-center gap-1.5">
            <Icon icon="lucide:calendar" class="text-purple-600" width="16" />
            <span>Deadline (Optional)</span>
          </label>
          <input 
            type="date" 
            v-model="form.due_date"
            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all"
          />
        </div>
      </div>

      <!-- Written Directive / Minute Sheet Entry -->
      <div class="space-y-2">
        <label class="block text-sm font-bold text-gray-700 flex items-center gap-1.5">
          <Icon icon="lucide:message-square" class="text-emerald-600" width="16" />
          <span>Written Directive / Minute Note</span>
        </label>
        <textarea
          v-model="form.description"
          rows="3"
          placeholder="e.g. For your urgent review, advice and further action as appropriate."
          class="w-full bg-white border border-gray-200 rounded-xl p-3 text-sm text-gray-700 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-50 transition-all resize-none"
        ></textarea>
      </div>

      <!-- Digital Signature PIN Verification -->
      <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 space-y-2">
        <div class="flex items-center justify-between">
          <label class="block text-sm font-bold text-gray-800 flex items-center gap-1.5">
            <Icon icon="lucide:shield-check" class="text-emerald-600" width="18" />
            <span>Digital Signature PIN <span class="text-red-500">*</span></span>
          </label>
          <span class="text-[11px] text-gray-500">Sign all {{ selectedTasks.length }} tasks with 1 verification</span>
        </div>
        <div class="relative">
          <input 
            :type="showPin ? 'text' : 'password'"
            v-model="form.approval_pin"
            placeholder="Enter your 4-digit approval PIN"
            autocomplete="new-password"
            maxlength="10"
            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-2.5 text-sm tracking-widest text-gray-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-50 transition-all font-mono"
          />
          <button 
            type="button" 
            @click="showPin = !showPin"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
          >
            <Icon :icon="showPin ? 'lucide:eye-off' : 'lucide:eye'" width="16" />
          </button>
        </div>
        <p v-if="formErrors.approval_pin" class="text-xs text-red-500 font-medium">{{ formErrors.approval_pin }}</p>
      </div>

      <!-- Footer Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
        <button
          type="button"
          @click="modalOpen = false"
          class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-colors"
        >
          Cancel
        </button>

        <button
          type="submit"
          :disabled="isSubmitting || selectedTasks.length === 0"
          class="px-6 py-2.5 rounded-xl bg-primary hover:bg-blue-700 text-white text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
        >
          <Icon v-if="isSubmitting" icon="lucide:loader-2" class="animate-spin" width="18" />
          <Icon v-else icon="lucide:send" width="18" />
          <span>{{ isSubmitting ? 'Dispatching...' : 'Dispatch ' + selectedTasks.length + ' Tasks' }}</span>
        </button>
      </div>
    </form>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import Dialog from "primevue/dialog";
import MultiSelect from "primevue/multiselect";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  selectedTasks: {
    type: Array,
    default: () => [],
  },
  prefillUser: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["update:modelValue", "assigned"]);

const modalOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val),
});

const page = usePage();
const usersList = ref([]);
const selectedUsers = ref([]);
const showPin = ref(false);
const isSubmitting = ref(false);
const formErrors = ref({});

const form = ref({
  users: [],
  priority: 2, // Standard medium
  due_date: "",
  description: "For your urgent review, advice and further action as appropriate.",
  approval_pin: "",
});

const priorities = [
  { value: 3, label: "Low", activeClass: "bg-blue-50 text-blue-700 border-blue-300 shadow-xs" },
  { value: 2, label: "Medium", activeClass: "bg-orange-50 text-orange-700 border-orange-300 shadow-xs" },
  { value: 1, label: "High", activeClass: "bg-red-50 text-red-700 border-red-300 shadow-xs" },
];

const fetchRecipients = async () => {
  try {
    const res = await axios.get("/dashboard/search-users", { params: { search: "" } });
    if (res.data && res.data.data) {
      usersList.value = res.data.data.map((item) => {
        item.display_name = item.display_title || item.title || (item.first_name + " " + item.last_name);
        return item;
      });
    }
  } catch (e) {
    console.error("Failed to load recipients for batch assign:", e);
  }
};

onMounted(() => {
  fetchRecipients();
});

// Prefill recipient if given (e.g. from 1-click smart suggest)
watch(
  () => props.prefillUser,
  (prefill) => {
    if (prefill && usersList.value.length > 0) {
      const match = usersList.value.find((u) => u.id === prefill.user_id || u.id === prefill.id);
      if (match) {
        selectedUsers.value = [match];
      }
    }
  },
  { immediate: true }
);

const submitBatchAssign = () => {
  formErrors.value = {};

  if (!selectedUsers.value || selectedUsers.value.length === 0) {
    formErrors.value.users = "Please select at least one recipient.";
    return;
  }

  if (!form.value.approval_pin) {
    formErrors.value.approval_pin = "Digital signature PIN is required to authorize dispatch.";
    return;
  }

  isSubmitting.value = true;

  const payload = {
    task_ids: props.selectedTasks.map((t) => t.id),
    users: selectedUsers.value.map((u) => {
      if (u.user && u.user.length >= 0) {
        u.users = u.user;
      }
      return u;
    }),
    priority: form.value.priority,
    due_date: form.value.due_date || null,
    description: form.value.description,
    approval_pin: form.value.approval_pin,
  };

  router.post("/dashboard/batch-assign-tasks", payload, {
    preserveScroll: true,
    onSuccess: (pageResp) => {
      isSubmitting.value = false;
      const flashErr = pageResp.props?.errors;
      if (flashErr && Object.keys(flashErr).length > 0) {
        formErrors.value = flashErr;
        return;
      }
      modalOpen.value = false;
      selectedUsers.value = [];
      form.value.approval_pin = "";
      emit("assigned");
    },
    onError: (errors) => {
      isSubmitting.value = false;
      formErrors.value = errors;
    },
  });
};
</script>
