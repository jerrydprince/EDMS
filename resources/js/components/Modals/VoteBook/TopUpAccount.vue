<template>
  <Dialog 
    v-model:visible="visible" 
    :closable="false" 
    modal 
    :pt="{
      root: { class: 'bg-white rounded-3xl shadow-2xl mx-2 md:mx-0 overflow-hidden border border-gray-100' },
      header: { class: 'hidden' },
      content: { class: 'p-0 bg-white w-full' },
      footer: { class: 'p-0 bg-gray-50 border-t border-gray-100' },
    }" 
    :style="{ width: '34rem' }" 
    :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
  >
    <section class="flex flex-col">
      <!-- HEADER -->
      <div class="px-6 py-5 bg-gradient-to-r from-slate-900 via-indigo-950 to-emerald-950 text-white flex items-center justify-between">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center shrink-0">
            <Icon icon="solar:round-transfer-vertical-bold-duotone" width="24" class="text-emerald-400" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                Warrant Credit / Augmentation
              </span>
            </div>
            <h2 class="text-lg font-bold text-white tracking-tight mt-0.5">
              Top-Up Vote Allocation
            </h2>
            <p class="text-xs text-slate-300">
              Incur Authority to Incur Expenditure (AIE) or Supplementary Warrant
            </p>
          </div>
        </div>
        <button 
          @click="cancelModal"
          class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-all cursor-pointer"
        >
          <Icon icon="solar:close-circle-bold" width="20" />
        </button>
      </div>

      <!-- BODY CONTENT -->
      <form @submit.prevent="submitTopUp" class="p-6 space-y-5">
        <!-- Beneficiary Account Card -->
        <div class="p-4 bg-gray-50/80 rounded-2xl border border-gray-200/80 flex items-center justify-between">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Crediting Vote Subhead</span>
            <h3 class="text-sm font-bold text-gray-900 mt-0.5">
              {{ accountSelected?.name }}
            </h3>
            <p class="text-xs text-gray-500 font-mono mt-0.5">
              {{ accountSelected?.account_number }} • {{ accountSelected?.ncoa_code ? ('NCOA ' + accountSelected.ncoa_code) : 'GIFMIS' }}
            </p>
          </div>
          <div class="text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Current Balance</span>
            <p class="text-base font-bold text-gray-900 font-mono mt-0.5">
              ₦{{ formatNumber(accountSelected?.balance || 0) }}
            </p>
          </div>
        </div>

        <!-- Amount Input -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
            Top-Up Allocation Amount (₦) <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-600 font-bold text-xl">
              ₦
            </div>
            <input 
              v-model="form.amount" 
              type="number" 
              step="0.01"
              min="0.01"
              required
              placeholder="0.00"
              class="w-full pl-10 pr-4 py-3.5 bg-emerald-50/20 hover:bg-white focus:bg-white border-2 border-emerald-500/40 focus:border-emerald-600 rounded-2xl text-2xl font-extrabold font-mono text-gray-900 outline-none transition-all shadow-inner" 
            />
          </div>
        </div>

        <!-- Quick Increment Buttons -->
        <div>
          <span class="block text-[11px] font-semibold text-gray-500 mb-2">Quick Augmentation Presets:</span>
          <div class="grid grid-cols-4 gap-2">
            <button
              type="button"
              v-for="preset in presets"
              :key="preset.val"
              @click="setPreset(preset.val)"
              class="py-1.5 px-2 bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all cursor-pointer text-center"
            >
              +{{ preset.label }}
            </button>
          </div>
        </div>

        <!-- Projected New Balance Card -->
        <div class="p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-200 flex items-center justify-between text-xs">
          <div class="flex items-center gap-2">
            <Icon icon="solar:verified-check-bold" width="18" class="text-emerald-600" />
            <span class="text-gray-700 font-medium">Projected New Available Balance:</span>
          </div>
          <span class="font-extrabold text-emerald-800 font-mono text-sm">
            ₦{{ formatNumber(projectedBalance) }}
          </span>
        </div>
      </form>

      <!-- FOOTER -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
        <button 
          type="button"
          @click="cancelModal" 
          class="px-5 py-2.5 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold rounded-xl border border-gray-200 transition-all cursor-pointer shadow-2xs"
        >
          Cancel
        </button>

        <button 
          type="button"
          @click="submitTopUp" 
          :disabled="!form.amount || Number(form.amount) <= 0 || form.processing"
          class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs shadow-emerald-600/30 transition-all cursor-pointer flex items-center gap-2"
        >
          <Icon v-if="form.processing" icon="solar:restart-bold" class="animate-spin" width="16" />
          <Icon v-else icon="solar:check-circle-bold" width="16" />
          <span>Confirm Warrant Credit</span>
        </button>
      </div>
    </section>
  </Dialog>
</template>

<script setup>
import { ref, computed, defineEmits, defineProps } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import Dialog from "primevue/dialog";

const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled", "refreshVoteBook", "account"]);
const props = defineProps({
  accountSelected: {
    type: Object,
    required: true
  }
});

const visible = ref(true);

const presets = [
  { label: "₦500k", val: 500000 },
  { label: "₦1.0M", val: 1000000 },
  { label: "₦5.0M", val: 5000000 },
  { label: "₦10.0M", val: 10000000 },
];

const form = useForm({
  amount: null,
  id: props.accountSelected?.id
});

const setPreset = (val) => {
  form.amount = (Number(form.amount) || 0) + val;
};

const projectedBalance = computed(() => {
  const current = Number(props.accountSelected?.balance || 0);
  const add = Number(form.amount || 0);
  return current + add;
});

const formatNumber = (num) => {
  return Number(num || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const submitTopUp = () => {
  if (!form.amount || Number(form.amount) <= 0) return;

  form.post('/finance-and-accounts/votebook-topup', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Allocation Credited",
        detail: `₦${formatNumber(form.amount)} added to ${props.accountSelected?.name}`,
        life: 3500,
      });
      emit("refreshVoteBook");
      emit("account", props.accountSelected);
      emit("cancelled");
    },
    onError: () => {
      toast.add({
        severity: "error",
        summary: "Top-up Failed",
        detail: "Could not apply supplementary top-up.",
        life: 4000,
      });
    }
  });
};

const cancelModal = () => {
  emit("cancelled");
};
</script>
