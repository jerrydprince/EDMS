<template>
  <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between sticky top-0 z-40 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center border border-indigo-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      </div>
      <div>
        <h2 class="text-lg font-bold text-gray-900 leading-tight">EBEWE Action Center</h2>
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
          <span v-if="payment.ebewe_instance">Stage: {{ payment.ebewe_instance.current_stage?.name || 'Awaiting Stage' }}</span>
          <span v-else>Legacy Routing Active</span>
        </p>
      </div>
    </div>

    <!-- Dynamic Action Buttons based on Routing Engine -->
    <div class="flex items-center gap-3">
      
      <!-- Parallel Verification (E.g. Advances pinging Other Charges) -->
      <button 
        v-if="canPingForVerification"
        @click="$emit('openParallelChecklist')"
        class="flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-sm font-bold hover:bg-amber-100 transition-colors shadow-sm"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        Request Vote Verification
      </button>

      <!-- Digital Stamping Button (For Audit/Checking) -->
      <button 
        v-if="canStamp"
        @click="$emit('openDigitalStamp')"
        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white border border-indigo-700 rounded-lg text-sm font-bold hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Affix Digital Stamp
      </button>

      <!-- Fallback Legacy Buttons mapped via EBEWE -->
      <div class="flex items-center gap-2 border-l border-gray-200 pl-3 ml-1" v-if="hasLegacyActions">
        <button 
          v-if="showReject"
          @click="$emit('reject')"
          class="px-4 py-2 bg-white text-rose-600 border border-rose-200 rounded-lg text-sm font-bold hover:bg-rose-50 transition-colors"
        >
          Return / Reject
        </button>
        
        <button 
          v-if="showAssign"
          @click="$emit('assign')"
          class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors"
        >
          Re-Assign Route
        </button>

        <button 
          v-if="showAccept"
          @click="$emit('accept')"
          class="px-6 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors shadow-md shadow-emerald-200"
        >
          Accept & Proceed
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  payment: Object,
  roles: Array,
  user: Object
});

defineEmits(['openParallelChecklist', 'openDigitalStamp', 'reject', 'assign', 'accept']);

// Logic to determine if user can digitally stamp
const canStamp = computed(() => {
  return props.roles.includes('checking') || 
         props.roles.includes('checking(HOS)') || 
         props.roles.includes('internal audit') || 
         props.roles.includes('internal audit(HOS)') ||
         props.roles.includes('final account') ||
         props.roles.includes('final account(HOS)');
});

// Logic for Parallel Checklists
const canPingForVerification = computed(() => {
  return props.roles.includes('advance section') || props.roles.includes('advance section(HOS)');
});

// Legacy backward compatibility state
const hasLegacyActions = computed(() => true);
const showReject = computed(() => props.payment?.assigned_by && props.payment?.assigned_by?.status === 'pending');
const showAssign = computed(() => props.payment?.assigned_to?.length === 0);
const showAccept = computed(() => props.payment?.assigned_by?.status === 'pending');

</script>
