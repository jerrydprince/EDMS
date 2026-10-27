<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-in zoom-in duration-200">
      
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-50">
        <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Affix Digital Stamp
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Stamp Preview Area -->
      <div class="p-8 flex flex-col items-center justify-center bg-stripes bg-slate-50/50">
        <p class="text-sm font-semibold text-slate-500 mb-6 uppercase tracking-widest">Stamp Preview</p>
        
        <!-- The Visual Stamp -->
        <div :class="[
          'relative flex items-center justify-center p-6 border-4 rounded-xl rotate-[-2deg] opacity-90 shadow-sm transition-all',
          stampStyle.border,
          stampStyle.text
        ]">
          <!-- Inner Border -->
          <div :class="['absolute inset-1 border-2 border-dashed rounded-lg', stampStyle.border]"></div>
          
          <div class="text-center relative z-10 bg-white/80 px-2 py-1 rounded backdrop-blur-sm">
            <h2 class="text-2xl font-black uppercase tracking-widest mb-1">{{ stampLabel }}</h2>
            <p class="text-xs font-bold font-mono">ID: {{ user.id }}-{{ Date.now().toString().slice(-6) }}</p>
            <p class="text-[10px] font-mono mt-1">{{ new Date().toLocaleString() }}</p>
            <p class="text-[10px] font-bold mt-1 uppercase">{{ sectionName }}</p>
          </div>
        </div>
      </div>

      <!-- Form & Controls -->
      <div class="p-6 border-t border-gray-100">
        <div class="mb-5">
          <label class="block text-sm font-semibold text-slate-700 mb-2">Final Comments (Optional)</label>
          <textarea 
            v-model="comments"
            rows="3" 
            placeholder="Any remarks before stamping..."
            class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
          ></textarea>
        </div>

        <div class="flex items-center justify-end gap-3">
          <button @click="$emit('close')" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition-colors">
            Cancel
          </button>
          <button 
            @click="submitStamp"
            :disabled="isSubmitting"
            :class="[
              'flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-white transition-all shadow-md',
              stampStyle.bg,
              isSubmitting ? 'opacity-75 cursor-not-allowed' : 'hover:opacity-90'
            ]"
          >
            <span v-if="isSubmitting">Affixing...</span>
            <span v-else>Confirm & Stamp</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  paymentId: Number,
  user: Object,
  roles: Array
});

const emit = defineEmits(['close', 'stamped']);

const comments = ref('');
const isSubmitting = ref(false);

// Determine Section Name
const sectionName = computed(() => {
  if (props.roles.includes('checking') || props.roles.includes('checking(HOS)')) return 'Checking Section';
  if (props.roles.includes('internal audit') || props.roles.includes('internal audit(HOS)')) return 'Internal Audit';
  if (props.roles.includes('final account') || props.roles.includes('final account(HOS)')) return 'Final Accounts';
  return 'Verification';
});

// Determine Stamp Label
const stampLabel = computed(() => {
  if (sectionName.value === 'Checking Section') return 'PASSED FOR PAYMENT';
  if (sectionName.value === 'Internal Audit') return 'AUDITED & FOUND CORRECT';
  if (sectionName.value === 'Final Accounts') return 'POSTED TO LEDGER';
  return 'VERIFIED';
});

// Determine Stamp Visual Style (Colors)
const stampStyle = computed(() => {
  if (sectionName.value === 'Checking Section') {
    return { border: 'border-blue-600', text: 'text-blue-700', bg: 'bg-blue-600' };
  }
  if (sectionName.value === 'Internal Audit') {
    return { border: 'border-rose-600', text: 'text-rose-700', bg: 'bg-rose-600' };
  }
  if (sectionName.value === 'Final Accounts') {
    return { border: 'border-emerald-600', text: 'text-emerald-700', bg: 'bg-emerald-600' };
  }
  return { border: 'border-indigo-600', text: 'text-indigo-700', bg: 'bg-indigo-600' };
});

const submitStamp = () => {
  isSubmitting.value = true;
  
  // Submit via Inertia to Laravel Endpoint
  const form = useForm({
    payment_id: props.paymentId,
    stamp_type: sectionName.value,
    label: stampLabel.value,
    color: stampStyle.value.border, // Extract just the color tailwind class
    comments: comments.value
  });

  form.post('/ebewe/affix-stamp', {
    preserveScroll: true,
    onSuccess: () => {
      isSubmitting.value = false;
      emit('stamped');
      emit('close');
    },
    onError: () => {
      isSubmitting.value = false;
      alert('Failed to affix stamp. Please try again.');
    }
  });
};
</script>

<style scoped>
.bg-stripes {
  background-image: linear-gradient(45deg, #f8fafc 25%, transparent 25%, transparent 50%, #f8fafc 50%, #f8fafc 75%, transparent 75%, transparent);
  background-size: 20px 20px;
}
</style>
