<template>
  <div class="w-full">
    <!-- Directorate Switcher Header (When user has privileges or wants to inspect cross-directorate actions) -->
    <div class="flex items-center justify-between pb-2 mb-3 border-b border-gray-200">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Substantive Directorate Deck:</span>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold"
          :class="{
            'bg-emerald-100 text-emerald-800 border border-emerald-200': selectedDirectorate === 'procurement',
            'bg-blue-100 text-blue-800 border border-blue-200': selectedDirectorate === 'admin',
            'bg-indigo-100 text-indigo-800 border border-indigo-200': selectedDirectorate === 'finance',
          }"
        >
          {{ directorateName }}
        </span>
      </div>

      <!-- Quick Directorate Switch Tabs -->
      <div class="flex items-center gap-1 bg-gray-100 p-1 rounded-xl">
        <button
          type="button"
          @click="selectedDirectorate = 'finance'"
          :class="['px-2.5 py-1 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-1', selectedDirectorate === 'finance' ? 'bg-white text-indigo-700 shadow-xs' : 'text-gray-500 hover:text-gray-800']"
          title="Finance & Accounts (Dept 3)"
        >
          <Icon icon="lucide:calculator" width="13" />
          <span>F&A</span>
        </button>
        <button
          type="button"
          @click="selectedDirectorate = 'procurement'"
          :class="['px-2.5 py-1 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-1', selectedDirectorate === 'procurement' ? 'bg-white text-emerald-700 shadow-xs' : 'text-gray-500 hover:text-gray-800']"
          title="Procurement & BPP (Dept 2)"
        >
          <Icon icon="lucide:shopping-bag" width="13" />
          <span>Procurement</span>
        </button>
        <button
          type="button"
          @click="selectedDirectorate = 'admin'"
          :class="['px-2.5 py-1 text-xs font-semibold rounded-lg transition-all cursor-pointer flex items-center gap-1', selectedDirectorate === 'admin' ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-500 hover:text-gray-800']"
          title="Administration & HR (Dept 14)"
        >
          <Icon icon="lucide:users-round" width="13" />
          <span>Admin & HR</span>
        </button>
      </div>
    </div>

    <!-- 1. Finance & Accounts Desk -->
    <div v-if="selectedDirectorate === 'finance'">
      <ChiefAccountantActionDesk 
        :payment="payment" 
        :user="user" 
        :roles="roles" 
        :permissions="permissions"
        :activeQueries="activeQueries" 
        @refresh="$emit('refresh')" 
        @open-create-voucher="$emit('open-create-voucher')"
        @open-charge-account="$emit('open-charge-account')"
      />
    </div>

    <!-- 2. Procurement Directorate Desk -->
    <div v-else-if="selectedDirectorate === 'procurement'">
      <ProcurementActionDesk 
        :user="user" 
        :department="user?.department"
        @refresh="$emit('refresh')"
        @dispatched="handleProcurementDispatched"
      />
    </div>

    <!-- 3. Administration & HR Desk -->
    <div v-else-if="selectedDirectorate === 'admin'">
      <AdministrationActionDesk 
        :user="user" 
        :department="user?.department"
        @refresh="$emit('refresh')"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import ChiefAccountantActionDesk from './Payment/ChiefAccountantActionDesk.vue';
import ProcurementActionDesk from './Procurement/ProcurementActionDesk.vue';
import AdministrationActionDesk from './Administration/AdministrationActionDesk.vue';

const props = defineProps({
  payment: { type: Object, default: () => ({}) },
  user: { type: Object, default: () => ({}) },
  roles: { type: Array, default: () => [] },
  permissions: { type: Array, default: () => [] },
  activeQueries: { type: Array, default: () => [] }
});

const emit = defineEmits(['refresh', 'open-create-voucher', 'open-charge-account']);

// Detect default directorate based on user department
const defaultDir = computed(() => {
  const deptId = Number(props.user?.department_id);
  if (deptId === 2) return 'procurement';
  if (deptId === 14) return 'admin';
  return 'finance';
});

const selectedDirectorate = ref('finance');

onMounted(() => {
  selectedDirectorate.value = defaultDir.value;
});

const directorateName = computed(() => {
  if (selectedDirectorate.value === 'procurement') return 'Dept 2: Procurement (BPP 2007)';
  if (selectedDirectorate.value === 'admin') return 'Dept 14: Administration & HR (PSR 2021)';
  return 'Dept 3: Finance & Accounts (Financial Regs)';
});

const handleProcurementDispatched = (data) => {
  emit('refresh');
  selectedDirectorate.value = 'finance';
};
</script>
