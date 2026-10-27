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
    <!-- PRINTABLE VOUCHER SLIP CONTAINER -->
    <div id="printable-cashbook-slip" class="bg-white">
      <!-- HEADER BANNER -->
      <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white px-7 py-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex items-center justify-between relative z-10">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-emerald-400 border border-white/15 shadow-inner">
              <Icon icon="solar:book-bookmark-bold-duotone" width="28" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold tracking-widest uppercase text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                  Federal Treasury Entry
                </span>
                <span class="text-xs text-slate-300 font-mono">
                  #{{ cashbookDetails.ref_no }}
                </span>
              </div>
              <h2 class="text-xl font-bold text-white mt-1">Cash Book Transaction Slip</h2>
              <p class="text-xs text-slate-300">Office of the Accountant-General of the Federation • Cash Office</p>
            </div>
          </div>
          <button 
            @click="cancelModal" 
            class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer border border-white/10"
            title="Close"
          >
            <Icon icon="material-symbols:close" width="20" />
          </button>
        </div>

        <!-- AMOUNT HERO PANEL -->
        <div class="mt-6 bg-white/5 backdrop-blur-md border border-white/10 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <span class="text-xs text-slate-300 font-medium uppercase tracking-wider block">Transaction Amount</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-400 tracking-tight mt-0.5 flex items-baseline gap-1">
              <span class="text-lg text-emerald-500">₦</span>
              <span>{{ cashbookDetails.formattedAmount }}</span>
            </div>
          </div>
          <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
              :class="cashbookDetails.isReceipt ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'">
              <span class="w-2 h-2 rounded-full" :class="cashbookDetails.isReceipt ? 'bg-emerald-400' : 'bg-rose-400'"></span>
              {{ cashbookDetails.isReceipt ? 'Receipt (Inflow)' : 'Disbursement (Outflow)' }}
            </span>
            <span class="text-xs text-slate-300 font-mono">{{ cashbookDetails.time_stamp }}</span>
          </div>
        </div>
      </div>

      <!-- SLIP DETAILS BODY -->
      <div class="p-7 space-y-6">
        <!-- 2-COLUMN METADATA GRID -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 bg-gray-50/80 p-4 rounded-xl border border-gray-100 text-sm">
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Voucher No</span>
            <span class="font-bold text-gray-900 mt-1 font-mono block text-xs sm:text-sm">
              {{ cashbookDetails.ref_no }}
            </span>
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Value Date</span>
            <span class="font-semibold text-gray-800 mt-1 block text-xs sm:text-sm">
              {{ cashbookDetails.time_stamp }}
            </span>
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">NCOA Code</span>
            <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded text-xs inline-block mt-1">
              {{ cashbookDetails.NCOA }}
            </span>
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Platform</span>
            <span class="inline-flex items-center gap-1 font-semibold text-xs mt-1 px-2.5 py-0.5 rounded-full"
              :class="getPlatformBadge(cashbookDetails.platform)">
              <Icon :icon="getPlatformIcon(cashbookDetails.platform)" width="14" />
              {{ cashbookDetails.platform }}
            </span>
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Mandate / DVBN</span>
            <span class="font-mono text-gray-800 font-medium mt-1 block text-xs">
              {{ cashbookDetails.DVBN }}
            </span>
          </div>
          <div>
            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block">Treasury Receipt No</span>
            <span class="font-mono text-gray-800 font-medium mt-1 block text-xs">
              {{ cashbookDetails.receiptNo }}
            </span>
          </div>
        </div>

        <!-- PARTIES INVOLVED -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="flex items-center gap-2 text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">
              <Icon icon="solar:user-bold-duotone" width="16" class="text-blue-600" />
              <span>From (Payer Account)</span>
            </div>
            <p class="font-bold text-gray-900 text-sm leading-snug">
              {{ cashbookDetails.fromWhomReceived }}
            </p>
            <p class="text-xs text-gray-400 mt-1">TSA Sub-Account: {{ cashbookDetails.TSABank }}</p>
          </div>

          <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="flex items-center gap-2 text-gray-500 text-xs font-semibold uppercase tracking-wider mb-2">
              <Icon icon="solar:user-check-bold-duotone" width="16" class="text-emerald-600" />
              <span>To (Beneficiary / Payee)</span>
            </div>
            <p class="font-bold text-gray-900 text-sm leading-snug">
              {{ cashbookDetails.whomToPay }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Disbursed via {{ cashbookDetails.platform }}</p>
          </div>
        </div>

        <!-- DESCRIPTIONS -->
        <div class="space-y-3">
          <div v-if="cashbookDetails.descriptionOfPayments" class="bg-amber-50/50 border border-amber-200/60 rounded-xl p-4">
            <span class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5 mb-1.5">
              <Icon icon="solar:document-text-bold" width="15" />
              Payment Description
            </span>
            <p class="text-sm text-gray-800 leading-relaxed">
              {{ cashbookDetails.descriptionOfPayments }}
            </p>
          </div>

          <div v-if="cashbookDetails.descriptionOfReceipt" class="bg-emerald-50/50 border border-emerald-200/60 rounded-xl p-4">
            <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider flex items-center gap-1.5 mb-1.5">
              <Icon icon="solar:wallet-money-bold" width="15" />
              Receipt Description
            </span>
            <p class="text-sm text-gray-800 leading-relaxed">
              {{ cashbookDetails.descriptionOfReceipt }}
            </p>
          </div>
        </div>

        <!-- VOTE BOOK RECONCILIATION BADGE -->
        <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-200/80 text-xs text-gray-600">
          <div class="flex items-center gap-2">
            <Icon icon="solar:shield-check-bold" width="18" class="text-emerald-600" />
            <span>Audited & Liquidated through Cash Office Gateway</span>
          </div>
          <span class="font-mono text-gray-500">ID: #{{ props.entry?.id }}</span>
        </div>
      </div>

      <!-- FOOTER ACTIONS -->
      <div class="px-7 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3">
        <button 
          @click="printSlip"
          class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 font-semibold text-xs hover:bg-gray-50 hover:border-gray-400 transition-all shadow-xs cursor-pointer"
        >
          <Icon icon="solar:printer-bold-duotone" width="16" />
          <span>Print Slip</span>
        </button>

        <button 
          @click="cancelModal" 
          class="px-6 py-2 rounded-lg bg-[#2F68C5] hover:bg-blue-700 text-white font-semibold text-xs shadow-sm transition-all cursor-pointer"
        >
          Close
        </button>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed } from "vue";
import Dialog from "primevue/dialog";
import { Icon } from "@iconify/vue";

const props = defineProps({
  entry: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(["cancelled"]);
const visible = ref(true);

const cashbookDetails = computed(() => {
  const e = props.entry || {};
  const amt = parseFloat(e.amount || 0);
  const isReceipt = Boolean(e.receipt_description && !e.payment_description);

  return {
    ref_no: e.voucher_no || e.ref_no || "---",
    time_stamp: e.date ? new Date(e.date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : "---",
    NCOA: e.ncoa_code || "---",
    DVBN: e.dvbn || "---",
    formattedAmount: amt.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
    TSABank: e.tsa_bank || "CBN TSA Operating Account",
    fromWhomReceived: e.from || "OAGF TSA Operating Account",
    whomToPay: e.to || "Beneficiary",
    receiptNo: e.treasury_receipt || "---",
    platform: e.disbursement_platform || "GIFMIS",
    isReceipt: isReceipt,
    descriptionOfReceipt: e.receipt_description || "",
    descriptionOfPayments: e.payment_description || ""
  };
});

const getPlatformBadge = (platform) => {
  const p = (platform || '').toLowerCase();
  if (p.includes('gifmis')) return 'bg-indigo-100 text-indigo-700 border border-indigo-200';
  if (p.includes('remita')) return 'bg-amber-100 text-amber-800 border border-amber-200';
  return 'bg-blue-100 text-blue-700 border border-blue-200';
};

const getPlatformIcon = (platform) => {
  const p = (platform || '').toLowerCase();
  if (p.includes('gifmis')) return 'solar:server-bold-duotone';
  if (p.includes('remita')) return 'solar:card-2-bold-duotone';
  return 'solar:bank-bold-duotone';
};

const cancelModal = () => {
  emit("cancelled");
};

const printSlip = () => {
  window.print();
};
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printable-cashbook-slip, #printable-cashbook-slip * {
    visibility: visible;
  }
  #printable-cashbook-slip {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
