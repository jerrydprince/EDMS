<template>
  <Dialog 
    v-model:visible="visible" 
    modal 
    :closable="false"
    :pt="{
      root: { class: 'bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden mx-3' },
      header: { class: 'hidden' },
      content: { class: 'p-0 overflow-y-auto max-h-[90vh]' },
      footer: { class: 'hidden' }
    }" 
    :style="{ width: '42rem' }" 
    :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
  >
    <!-- MODAL HEADER -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white px-7 py-5 flex items-center justify-between border-b border-white/10">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-inner">
          <Icon icon="solar:card-recive-bold-duotone" width="24" />
        </div>
        <div>
          <h2 class="text-lg font-bold text-white">Record Cash Book Entry</h2>
          <p class="text-xs text-slate-300">
            Posting to: <span class="text-emerald-400 font-semibold">{{ activeAccountName }}</span>
          </p>
        </div>
      </div>
      <button 
        @click="cancelModal" 
        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer"
      >
        <Icon icon="material-symbols:close" width="18" />
      </button>
    </div>

    <!-- TRANSACTION TYPE SELECTOR -->
    <div class="px-7 pt-5 pb-2 bg-gray-50/70 border-b border-gray-100">
      <div class="grid grid-cols-2 gap-2 bg-gray-200/70 p-1 rounded-xl">
        <button 
          type="button"
          @click="entryType = 'disbursement'"
          class="flex items-center justify-center gap-2 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer"
          :class="entryType === 'disbursement' ? 'bg-white text-rose-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
        >
          <Icon icon="solar:arrow-right-down-bold" width="16" :class="entryType === 'disbursement' ? 'text-rose-600' : ''" />
          <span>Disbursement (Outflow)</span>
        </button>

        <button 
          type="button"
          @click="entryType = 'receipt'"
          class="flex items-center justify-center gap-2 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer"
          :class="entryType === 'receipt' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
        >
          <Icon icon="solar:arrow-left-down-bold" width="16" :class="entryType === 'receipt' ? 'text-emerald-600' : ''" />
          <span>Receipt (Inflow)</span>
        </button>
      </div>
    </div>

    <!-- FORM FIELDS -->
    <form @submit.prevent="submitEntry" class="p-7 space-y-4 text-sm">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Date -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            Value Date <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="form.date" 
            type="date" 
            required
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>

        <!-- Voucher No -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
            <span>Voucher No <span class="text-red-500">*</span></span>
            <button type="button" @click="generateVoucherNo" class="text-[11px] text-blue-600 hover:underline lowercase font-normal">generate</button>
          </label>
          <input 
            v-model="form.voucher_no" 
            type="text" 
            placeholder="e.g. VOU-000000018"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>

        <!-- Amount -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            Amount (₦) <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-500 font-bold text-xs">₦</span>
            <input 
              v-model="form.amount" 
              type="number" 
              step="0.01" 
              placeholder="0.00"
              required
              class="w-full pl-8 pr-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-900 font-bold text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
            />
          </div>
        </div>

        <!-- NCOA Code -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            NCOA Classification <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="form.ncoa_code" 
            type="text" 
            placeholder="e.g. 22020501"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>

        <!-- From (Payer) -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            From (Payer) <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="form.from" 
            type="text" 
            placeholder="e.g. OAGF TSA Operating Account"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>

        <!-- To (Beneficiary) -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            To (Beneficiary / Payee) <span class="text-red-500">*</span>
          </label>
          <input 
            v-model="form.to" 
            type="text" 
            placeholder="e.g. Contractor Name / Staff Name"
            required
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>

        <!-- Platform -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            Disbursement Platform
          </label>
          <select 
            v-model="form.disbursement_platform" 
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          >
            <option value="GIFMIS">GIFMIS</option>
            <option value="Remita">Remita</option>
            <option value="TSA-Direct">TSA-Direct</option>
          </select>
        </div>

        <!-- DVBN / Mandate Ref -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            DVBN / Mandate Ref
          </label>
          <input 
            v-model="form.dvbn" 
            type="text" 
            placeholder="e.g. FGN/2026/00492"
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>

        <!-- TSA Sub-Account -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            TSA / Bank Account
          </label>
          <input 
            v-model="form.tsa_bank" 
            type="text" 
            placeholder="e.g. CBN TSA Operating Account"
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>

        <!-- Treasury Receipt No -->
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
            Treasury Receipt No
          </label>
          <input 
            v-model="form.treasury_receipt" 
            type="text" 
            placeholder="e.g. TR-849204"
            class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
          />
        </div>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
          {{ entryType === 'disbursement' ? 'Payment Description' : 'Receipt Description' }}
        </label>
        <textarea 
          v-if="entryType === 'disbursement'"
          v-model="form.payment_description" 
          rows="3" 
          placeholder="Details of disbursement mandate, goods supplied, or services rendered..."
          class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none"
        ></textarea>
        <textarea 
          v-else
          v-model="form.receipt_description" 
          rows="3" 
          placeholder="Source of funds, allocation subhead, or refund deposit description..."
          class="w-full px-3.5 py-2.5 rounded-lg border border-gray-300 text-gray-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none"
        ></textarea>
      </div>

      <!-- FOOTER BUTTONS -->
      <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
        <button 
          type="button" 
          @click="cancelModal" 
          class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 font-semibold text-xs hover:bg-gray-50 transition-all cursor-pointer"
        >
          Cancel
        </button>

        <button 
          type="submit" 
          :disabled="form.processing"
          class="px-6 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all disabled:opacity-50 flex items-center gap-2 cursor-pointer"
        >
          <Icon v-if="form.processing" icon="line-md:loading-loop" width="16" />
          <Icon v-else icon="solar:check-circle-bold" width="16" />
          <span>{{ form.processing ? 'Saving...' : 'Record Entry' }}</span>
        </button>
      </div>
    </form>
  </Dialog>
</template>

<script setup>
import { ref, computed } from "vue";
import { useToast } from "primevue/usetoast";
import Dialog from "primevue/dialog";
import { Icon } from "@iconify/vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
  selectedAccount: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(["cancelled", "created"]);
const toast = useToast();
const visible = ref(true);
const entryType = ref('disbursement');

const activeAccountName = computed(() => {
  return props.selectedAccount?.name || 'Treasury Vote Book Account';
});

const form = useForm({
  vote_book_id: props.selectedAccount?.id || 1,
  date: new Date().toISOString().slice(0, 10),
  voucher_no: '',
  ncoa_code: props.selectedAccount?.ncoa_code || '22020501',
  dvbn: '',
  from: 'OAGF TSA Operating Account',
  to: '',
  amount: '',
  disbursement_platform: 'GIFMIS',
  tsa_bank: 'CBN TSA Operating Account',
  treasury_receipt: '',
  payment_description: '',
  receipt_description: ''
});

const generateVoucherNo = () => {
  const rand = Math.floor(100000 + Math.random() * 900000);
  form.voucher_no = `VOU-${rand}`;
};

const cancelModal = () => {
  emit("cancelled");
};

const submitEntry = () => {
  form.vote_book_id = props.selectedAccount?.id || 1;
  
  if (entryType.value === 'disbursement' && !form.payment_description) {
    form.payment_description = `Disbursement of ₦${Number(form.amount || 0).toLocaleString()} via ${form.disbursement_platform} for ${form.to}`;
  } else if (entryType.value === 'receipt' && !form.receipt_description) {
    form.receipt_description = `Receipt of ₦${Number(form.amount || 0).toLocaleString()} into ${form.from}`;
  }

  form.post('/finance-and-accounts/create-cashbook', {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Entry Recorded",
        detail: "The transaction has been successfully posted to the Cash Book.",
        life: 3500,
      });
      emit("created");
      emit("cancelled");
      form.reset();
    },
    onError: (errors) => {
      toast.add({
        severity: "error",
        summary: "Validation Failed",
        detail: Object.values(errors)[0] || "Please verify the form inputs.",
        life: 4000,
      });
    }
  });
};
</script>
