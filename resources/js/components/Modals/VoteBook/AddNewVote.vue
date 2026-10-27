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
    :style="{ width: '36rem' }"
    :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
  >
    <section class="flex flex-col">
      <!-- MODAL HEADER -->
      <div class="px-6 py-5 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white flex items-center justify-between">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center shrink-0">
            <Icon icon="solar:wallet-money-bold-duotone" width="24" class="text-emerald-400" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                GIFMIS & Budget Office
              </span>
            </div>
            <h2 class="text-lg font-bold text-white tracking-tight mt-0.5">
              Create Vote Book Account
            </h2>
            <p class="text-xs text-slate-300">
              Register a new statutory commitment & expenditure budget subhead
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

      <!-- FORM CONTENT -->
      <form @submit.prevent="addNewVote" class="p-6 space-y-4 max-h-[72vh] overflow-y-auto">
        <!-- Vote Subhead Name -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
            Vote Subhead Title <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
              <Icon icon="solar:document-text-bold-duotone" width="18" />
            </div>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. IT & Software Development Vote"
              class="w-full pl-10 pr-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-sm text-gray-900 outline-none transition-all shadow-2xs"
            />
          </div>
        </div>

        <!-- Account Number & GIFMIS Code -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
              Account Number / Ref <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <Icon icon="solar:card-2-bold-duotone" width="18" />
              </div>
              <input
                v-model="form.account_number"
                type="text"
                required
                placeholder="e.g. 0220001001-CAP"
                class="w-full pl-10 pr-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-sm font-mono text-gray-900 outline-none transition-all shadow-2xs"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
              GIFMIS Code <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <Icon icon="solar:hashtag-bold-duotone" width="18" />
              </div>
              <input
                v-model="form.gifmis_code"
                type="text"
                required
                placeholder="e.g. 0220001001-GIFMIS"
                class="w-full pl-10 pr-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-sm font-mono text-gray-900 outline-none transition-all shadow-2xs"
              />
            </div>
          </div>
        </div>

        <!-- NCOA Code & Fund Code -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
              NCOA Economic Code
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <Icon icon="solar:tag-bold-duotone" width="18" />
              </div>
              <input
                v-model="form.ncoa_code"
                type="text"
                placeholder="e.g. 22020501"
                class="w-full pl-10 pr-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-sm font-mono text-gray-900 outline-none transition-all shadow-2xs"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
              Fund Code
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <Icon icon="solar:archive-minimalistic-bold-duotone" width="18" />
              </div>
              <input
                v-model="form.fund_code"
                type="text"
                placeholder="e.g. 01101 (CRF)"
                class="w-full pl-10 pr-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-sm font-mono text-gray-900 outline-none transition-all shadow-2xs"
              />
            </div>
          </div>
        </div>

        <!-- Approved Allocation / Initial Balance -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
            Approved Budget Allocation (₦) <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none font-bold text-emerald-600 text-sm">
              ₦
            </div>
            <input
              v-model="form.balance"
              type="number"
              step="0.01"
              required
              placeholder="0.00"
              class="w-full pl-9 pr-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-base font-bold font-mono text-gray-900 outline-none transition-all shadow-2xs"
            />
          </div>
          <p class="text-[11px] text-gray-500 mt-1">
            Appropriation Act legal limit allocated to this budget line.
          </p>
        </div>

        <!-- Account Type & Date Created -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
              Account Classification <span class="text-rose-500">*</span>
            </label>
            <select
              v-model="form.account_type"
              required
              class="w-full px-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-sm text-gray-900 outline-none transition-all shadow-2xs"
            >
              <option value="standard">Standard Account (Overhead/Capital)</option>
              <option value="special">Special Account (Sub-Account / Project)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">
              Effective Date <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.date_created"
              type="date"
              required
              class="w-full px-3 py-2.5 bg-gray-50/50 hover:bg-white focus:bg-white border border-gray-200 focus:border-emerald-500 rounded-xl text-sm text-gray-900 outline-none transition-all shadow-2xs"
            />
          </div>
        </div>
      </form>

      <!-- MODAL FOOTER -->
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
          @click="addNewVote"
          :disabled="!isFormValid || form.processing"
          class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs shadow-emerald-600/30 transition-all cursor-pointer flex items-center gap-2"
        >
          <Icon v-if="form.processing" icon="solar:restart-bold" class="animate-spin" width="16" />
          <Icon v-else icon="solar:check-circle-bold" width="16" />
          <span>Register Vote Subhead</span>
        </button>
      </div>
    </section>
  </Dialog>
</template>

<script setup>
import { ref, computed, defineEmits } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import Dialog from "primevue/dialog";

const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled", "refreshVoteBook"]);
const visible = ref(true);

const form = useForm({
  name: "",
  account_number: "",
  gifmis_code: "",
  ncoa_code: "",
  fund_code: "01101",
  account_type: "standard",
  balance: "",
  date_created: new Date().toISOString().substring(0, 10),
});

const isFormValid = computed(() => {
  return (
    Boolean(form.name && form.name.trim()) &&
    Boolean(form.account_number && form.account_number.trim()) &&
    Boolean(form.gifmis_code && form.gifmis_code.trim()) &&
    Boolean(form.balance !== null && form.balance !== "" && !isNaN(Number(form.balance))) &&
    Boolean(form.account_type) &&
    Boolean(form.date_created)
  );
});

const addNewVote = () => {
  if (!isFormValid.value) return;

  form.post('/finance-and-accounts/create-vote-book', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Vote Subhead Created",
        detail: `Vote "${form.name}" registered successfully.`,
        life: 3500,
      });
      emit("refreshVoteBook");
      emit("cancelled");
    },
    onError: () => {
      toast.add({
        severity: "error",
        summary: "Creation Failed",
        detail: "Please check all required fields and try again.",
        life: 4000,
      });
    }
  });
};

const cancelModal = () => {
  emit("cancelled");
};
</script>
