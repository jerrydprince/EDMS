<template>
  <Dialog 
    v-model:visible="isModalVisible" 
    :closable="false" 
    modal 
    :pt="{
      root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
      content: { class: 'p-0' },
      header: { class: 'hidden' },
      footer: { class: 'hidden' },
    }" 
    :style="{ width: '38rem' }" 
    :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
  >
    <!-- Custom Header -->
    <div class="px-7 py-5 border-b border-gray-100 bg-gray-50/70 flex items-center justify-between">
      <div class="flex items-center gap-3.5">
        <div class="p-2.5 bg-primary/10 text-primary rounded-xl border border-primary/20">
          <Icon icon="mdi:key-plus" width="24" />
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800 tracking-tight">Create Custom Permission</h2>
          <p class="text-xs text-gray-500 mt-0.5">Register new module capability for dynamic system access control</p>
        </div>
      </div>
      <button 
        type="button"
        @click="closeModal" 
        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
      >
        <Icon icon="mdi:close" width="20" />
      </button>
    </div>

    <!-- Form Content -->
    <form @submit.prevent="submit" class="p-7 space-y-5">
      
      <!-- 1. Module / Feature Category -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
            Module / Feature Category <span class="text-rose-500">*</span>
          </label>
          <button 
            type="button" 
            @click="toggleCustomModule" 
            class="text-xs font-semibold text-primary hover:text-primary/80 transition cursor-pointer"
          >
            {{ isCustomModule ? '← Select From Standard Modules' : '+ Type Custom Module Name' }}
          </button>
        </div>

        <!-- Dropdown for standard modules -->
        <div v-if="!isCustomModule">
          <select 
            v-model="selectedModuleOption" 
            @change="handleModuleSelectChange"
            class="w-full text-sm rounded-xl border-gray-300 bg-white focus:border-primary focus:ring-primary/20 px-3.5 py-2.5 transition"
          >
            <option value="" disabled>-- Select Module Category --</option>
            <option v-for="mod in allModuleOptions" :key="mod" :value="mod">
              {{ mod }}
            </option>
            <option value="__custom__">+ Enter Custom Module Name...</option>
          </select>
        </div>

        <!-- Custom module input field -->
        <div v-else>
          <input 
            type="text"
            v-model="customModuleName" 
            placeholder="e.g. Procurement, Fixed Assets, Legal, TSA Gateway"
            required
            class="w-full text-sm rounded-xl border border-gray-300 focus:border-primary focus:ring-primary/20 px-3.5 py-2.5 transition"
          />
        </div>

        <!-- Quick Module Pills -->
        <div class="flex flex-wrap gap-1.5 mt-2">
          <button
            type="button"
            v-for="quickMod in quickModules"
            :key="quickMod"
            @click="selectQuickModule(quickMod)"
            class="text-[11px] font-medium px-2.5 py-1 rounded-lg border transition-all cursor-pointer"
            :class="activeModuleValue.toLowerCase() === quickMod.toLowerCase() ? 'bg-primary text-white border-primary shadow-xs' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
          >
            {{ quickMod }}
          </button>
        </div>
      </div>

      <!-- 2. Action / Capability -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
            Action / Capability <span class="text-rose-500">*</span>
          </label>
          <button 
            type="button" 
            @click="toggleCustomAction" 
            class="text-xs font-semibold text-primary hover:text-primary/80 transition cursor-pointer"
          >
            {{ isCustomAction ? '← Select From Standard Actions' : '+ Type Custom Action' }}
          </button>
        </div>

        <!-- Dropdown for standard actions -->
        <div v-if="!isCustomAction">
          <select 
            v-model="selectedActionOption" 
            @change="handleActionSelectChange"
            class="w-full text-sm rounded-xl border-gray-300 bg-white focus:border-primary focus:ring-primary/20 px-3.5 py-2.5 transition"
          >
            <option value="" disabled>-- Select Capability Action --</option>
            <option v-for="act in standardActions" :key="act.value" :value="act.value">
              {{ act.label }} ({{ act.value }})
            </option>
            <option value="__custom__">+ Enter Custom Action...</option>
          </select>
        </div>

        <!-- Custom action input field -->
        <div v-else>
          <input 
            type="text"
            v-model="customActionName" 
            placeholder="e.g. reconcile, gazette, sign, seal, liquidate"
            required
            class="w-full text-sm rounded-xl border border-gray-300 focus:border-primary focus:ring-primary/20 px-3.5 py-2.5 transition"
          />
        </div>

        <!-- Quick Action Pills -->
        <div class="flex flex-wrap gap-1.5 mt-2">
          <button
            type="button"
            v-for="act in quickActions"
            :key="act"
            @click="selectQuickAction(act)"
            class="text-[11px] font-medium px-2.5 py-1 rounded-lg border transition-all cursor-pointer"
            :class="activeActionValue.toLowerCase() === act.toLowerCase() ? 'bg-primary text-white border-primary shadow-xs' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
          >
            {{ act }}
          </button>
        </div>
      </div>

      <!-- 3. Description (Optional) -->
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
          Description (Optional)
        </label>
        <textarea 
          v-model="formDescription" 
          rows="2" 
          placeholder="Detailed explanation of what privilege this grants the officer..."
          class="w-full text-sm rounded-xl border border-gray-300 focus:border-primary focus:ring-primary/20 px-3.5 py-2 transition"
        ></textarea>
      </div>

      <!-- Live Identifier Preview & Validation -->
      <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs text-slate-600 font-semibold">Computed System Identifier:</span>
            <p class="text-[11px] text-gray-400">Used in route gates, policy checks, and permission evaluation</p>
          </div>
          <code class="px-3 py-1 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold text-primary shadow-xs">
            {{ computedPermissionName || 'module action' }}
          </code>
        </div>

        <!-- Duplicate Warning / Ready Status -->
        <div v-if="isAlreadyRegistered" class="flex items-center gap-2 p-2 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-medium">
          <Icon icon="mdi:alert-circle-outline" width="16" class="text-amber-600 shrink-0" />
          <span>Warning: Permission <strong>"{{ computedPermissionName }}"</strong> is already registered in the system.</span>
        </div>
        <div v-else-if="computedPermissionName" class="flex items-center gap-2 p-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-xs font-medium">
          <Icon icon="mdi:check-circle-outline" width="16" class="text-emerald-600 shrink-0" />
          <span>Identifier is unique and ready for registration</span>
        </div>
      </div>

      <!-- Custom Footer -->
      <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
        <button 
          type="button" 
          @click="closeModal" 
          class="px-5 py-2.5 rounded-xl text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors cursor-pointer"
        >
          Cancel
        </button>
        <button 
          type="submit" 
          :disabled="!activeModuleValue || !activeActionValue || isAlreadyRegistered || isSubmitting"
          class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary/90 disabled:opacity-50 transition-all flex items-center gap-1.5 shadow-xs cursor-pointer disabled:cursor-not-allowed"
        >
          <Icon v-if="isSubmitting" icon="line-md:loading-loop" width="16" />
          <Icon v-else icon="lucide:check" width="16" />
          <span>{{ isSubmitting ? 'Registering...' : 'Register Permission' }}</span>
        </button>
      </div>
    </form>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import { useToast } from 'primevue/usetoast';

const defaultModules = [
  'Payment',
  'Voucher',
  'Checking',
  'Internal Audit',
  'Cashbook',
  'Votebook',
  'Ledger',
  'Advances',
  'Registry',
  'Admin Registry',
  'Mails',
  'Workflow',
  'Edms',
  'Pms',
  'Servicom',
  'Queries',
  'Working Calendar',
  'Chat',
  'Branch Setup',
  'Manage Users',
  'Access Config',
  'Metadata Setup',
  'Document Generation',
  'Analytics',
  'Support',
  'Settings',
  'Notifications',
  'Audit',
  'Recycle Bin',
  'System Configurations'
];

const standardActions = [
  { value: 'view', label: 'View / Read Access' },
  { value: 'create', label: 'Create / Draft' },
  { value: 'update', label: 'Update / Edit' },
  { value: 'delete', label: 'Delete / Remove' },
  { value: 'approve', label: 'Executive Approval' },
  { value: 'reject', label: 'Reject / Return' },
  { value: 'verify', label: 'Cryptographic / Compliance Verification' },
  { value: 'stamp', label: 'Affix Seal / Stamp' },
  { value: 'disburse', label: 'TSA Cash Disbursement' },
  { value: 'post', label: 'Post to General Ledger' },
  { value: 'minute', label: 'Minute & Forward' },
  { value: 'dispatch', label: 'Outward Registry / Ticket Dispatch' },
  { value: 'respond', label: 'Submit Response / Representation' },
  { value: 'verdict', label: 'Issue Ruling / Verdict' },
  { value: 'retire', label: 'Retire Advance / Imprest' },
  { value: 'initiate', label: 'Initiate Workflow / Payment' },
  { value: 'classify', label: 'Classify Expenditure Track' },
  { value: 'encumber', label: 'Commit & Encumber Funds' },
  { value: 'export', label: 'Export Data / Evidence' },
  { value: 'download', label: 'Download Document' },
  { value: 'manage', label: 'Full Management' },
];

const quickModules = [
  'Payment', 'Voucher', 'Registry', 'Workflow', 'Edms', 
  'Support', 'Settings', 'Notifications', 'Audit', 'Recycle Bin', 'Queries'
];

const quickActions = [
  'view', 'create', 'update', 'delete', 'approve', 'dispatch', 'export', 'verify'
];

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: undefined
  },
  visible: {
    type: Boolean,
    default: undefined
  },
  existingModules: {
    type: Array,
    default: () => []
  },
  allPermissions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'update:visible', 'close', 'created']);
const toast = useToast();
const isSubmitting = ref(false);

const isCustomModule = ref(false);
const selectedModuleOption = ref('Payment');
const customModuleName = ref('');

const isCustomAction = ref(false);
const selectedActionOption = ref('view');
const customActionName = ref('');

const formDescription = ref('');

const isModalVisible = computed({
  get: () => (props.modelValue !== undefined ? props.modelValue : (props.visible || false)),
  set: (val) => {
    emit('update:modelValue', val);
    emit('update:visible', val);
    if (!val) {
      emit('close');
    }
  }
});

// Merged Unique Module Options
const allModuleOptions = computed(() => {
  const merged = new Set([...defaultModules, ...(props.existingModules || [])]);
  return Array.from(merged).filter(Boolean).sort();
});

// Current Active Values (sanitized)
const activeModuleValue = computed(() => {
  const raw = isCustomModule.value ? customModuleName.value : selectedModuleOption.value;
  return (raw || '').replace(/\s+/g, ' ').trim();
});

const activeActionValue = computed(() => {
  const raw = isCustomAction.value ? customActionName.value : selectedActionOption.value;
  return (raw || '').replace(/\s+/g, ' ').trim();
});

const computedPermissionName = computed(() => {
  if (!activeModuleValue.value && !activeActionValue.value) return '';
  return `${activeModuleValue.value.toLowerCase()} ${activeActionValue.value.toLowerCase()}`.trim();
});

// Check if computed permission name already exists
const isAlreadyRegistered = computed(() => {
  if (!computedPermissionName.value) return false;
  const target = computedPermissionName.value.toLowerCase();
  
  if (Array.isArray(props.allPermissions)) {
    for (const item of props.allPermissions) {
      if (item?.permissions && Array.isArray(item.permissions)) {
        if (item.permissions.some(p => (typeof p === 'string' ? p : p?.name)?.toLowerCase() === target)) {
          return true;
        }
      } else if (typeof item === 'string' && item.toLowerCase() === target) {
        return true;
      } else if (item?.name && item.name.toLowerCase() === target) {
        return true;
      }
    }
  }
  return false;
});

const toggleCustomModule = () => {
  isCustomModule.value = !isCustomModule.value;
  if (isCustomModule.value) {
    customModuleName.value = '';
  } else {
    if (selectedModuleOption.value === '__custom__') {
      selectedModuleOption.value = allModuleOptions.value[0] || 'Payment';
    }
  }
};

const toggleCustomAction = () => {
  isCustomAction.value = !isCustomAction.value;
  if (isCustomAction.value) {
    customActionName.value = '';
  } else {
    if (selectedActionOption.value === '__custom__') {
      selectedActionOption.value = 'view';
    }
  }
};

const handleModuleSelectChange = () => {
  if (selectedModuleOption.value === '__custom__') {
    isCustomModule.value = true;
    customModuleName.value = '';
  }
};

const handleActionSelectChange = () => {
  if (selectedActionOption.value === '__custom__') {
    isCustomAction.value = true;
    customActionName.value = '';
  }
};

const selectQuickModule = (mod) => {
  isCustomModule.value = false;
  selectedModuleOption.value = mod;
};

const selectQuickAction = (act) => {
  isCustomAction.value = false;
  selectedActionOption.value = act;
};

const closeModal = () => {
  isModalVisible.value = false;
  emit('update:modelValue', false);
  emit('update:visible', false);
  emit('close');
  formDescription.value = '';
  isCustomModule.value = false;
  selectedModuleOption.value = allModuleOptions.value[0] || 'Payment';
  customModuleName.value = '';
  isCustomAction.value = false;
  selectedActionOption.value = 'view';
  customActionName.value = '';
};

watch(isModalVisible, (isOpen) => {
  if (isOpen) {
    isSubmitting.value = false;
    if (selectedModuleOption.value === '__custom__') {
      selectedModuleOption.value = allModuleOptions.value[0] || 'Payment';
    }
    if (selectedActionOption.value === '__custom__') {
      selectedActionOption.value = 'view';
    }
  }
});

const submit = () => {
  const mod = activeModuleValue.value;
  const act = activeActionValue.value;

  if (!mod || !act) {
    toast.add({
      severity: 'warn',
      summary: 'Required Fields',
      detail: 'Please specify both Module and Action.',
      life: 3000
    });
    return;
  }

  if (isAlreadyRegistered.value) {
    toast.add({
      severity: 'warn',
      summary: 'Duplicate Permission',
      detail: `Permission "${computedPermissionName.value}" is already registered.`,
      life: 4000
    });
    return;
  }

  isSubmitting.value = true;

  router.post('/dashboard/create-permission', {
    module: mod,
    action: act,
    description: formDescription.value
  }, {
    onSuccess: () => {
      toast.add({
        severity: 'success',
        summary: 'Permission Created',
        detail: `Successfully registered: ${computedPermissionName.value}`,
        life: 4000
      });
      emit('created', { module: mod, action: act, name: computedPermissionName.value });
      closeModal();
      router.reload();
    },
    onError: (err) => {
      toast.add({
        severity: 'error',
        summary: 'Registration Failed',
        detail: err?.action || err?.module || err?.description || 'Could not register permission.',
        life: 4000
      });
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};
</script>
