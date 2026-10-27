<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden font-sans">
    <!-- Header banner -->
    <div class="bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 text-white p-5">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
              Department 2: Procurement
            </span>
            <span class="text-xs text-slate-300">Public Procurement Act (BPP 2007)</span>
          </div>
          <h2 class="text-xl font-bold mt-1 tracking-tight">Statutory Procurement & Contract Action Deck</h2>
          <p class="text-xs text-slate-300 mt-0.5">
            Tenders Evaluation • Ministerial Threshold Checks • Statutory LPO Issuance • Cross-Directorate F&A Dispatch
          </p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="openNewProcurementModal = true"
            class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-sm transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <Icon icon="lucide:plus-circle" width="16" />
            <span>New Requisition</span>
          </button>
          <button
            @click="fetchProcurements"
            class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl transition-all cursor-pointer"
            title="Refresh"
          >
            <Icon icon="lucide:refresh-cw" width="16" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>

      <!-- Action Tabs -->
      <div class="flex items-center gap-2 mt-4 pt-3 border-t border-white/10 overflow-x-auto">
        <button
          @click="activeTab = 'all'"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5', activeTab === 'all' ? 'bg-white text-emerald-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:layers" width="14" />
          <span>All Requisitions ({{ items.length }})</span>
        </button>
        <button
          @click="activeTab = 'tenders'"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5', activeTab === 'tenders' ? 'bg-white text-emerald-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:file-check-2" width="14" />
          <span>Tenders Evaluation (Sec 86)</span>
        </button>
        <button
          @click="activeTab = 'lpo'"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5', activeTab === 'lpo' ? 'bg-white text-emerald-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:file-signature" width="14" />
          <span>Award & LPO Desk (Sec 87)</span>
        </button>
        <button
          @click="activeTab = 'dispatched'"
          :class="['px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer flex items-center gap-1.5', activeTab === 'dispatched' ? 'bg-white text-emerald-900 shadow-sm' : 'text-slate-300 hover:bg-white/10']"
        >
          <Icon icon="lucide:send" width="14" />
          <span>F&A Dispatched Files</span>
        </button>
      </div>
    </div>

    <!-- Active Content Area -->
    <div class="p-5">
      <!-- Loading State -->
      <div v-if="loading" class="py-12 text-center text-slate-500">
        <Icon icon="lucide:loader" width="28" class="animate-spin mx-auto mb-2 text-emerald-600" />
        <p class="text-sm">Loading procurement registers & dossiers...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredItems.length === 0" class="py-12 text-center border-2 border-dashed border-slate-200 rounded-xl">
        <Icon icon="lucide:folder-search" width="36" class="mx-auto text-slate-400 mb-2" />
        <h4 class="text-sm font-semibold text-slate-700">No Procurement Dossiers Found</h4>
        <p class="text-xs text-slate-500 mt-1">Initiate a new requisition to begin BPP 2007 procurement workflows.</p>
        <button
          @click="openNewProcurementModal = true"
          class="mt-3 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg inline-flex items-center gap-1.5 cursor-pointer"
        >
          <Icon icon="lucide:plus" width="14" />
          <span>Create New Requisition</span>
        </button>
      </div>

      <!-- Procurement Records Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
              <th class="py-3 px-3">BPP Ref / Title</th>
              <th class="py-3 px-3">Vendor / Contractor</th>
              <th class="py-3 px-3 text-right">Contract Sum</th>
              <th class="py-3 px-3">BPP Threshold</th>
              <th class="py-3 px-3">Status</th>
              <th class="py-3 px-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-3">
                <div class="font-bold text-slate-900">{{ item.title }}</div>
                <div class="text-[11px] font-mono text-emerald-700 mt-0.5 flex items-center gap-1">
                  <Icon icon="lucide:hash" width="12" /> {{ item.bpp_ref }}
                </div>
                <div v-if="item.lpo_number" class="text-[11px] font-mono text-blue-700 mt-0.5 flex items-center gap-1">
                  <Icon icon="lucide:tag" width="12" /> {{ item.lpo_number }}
                </div>
              </td>
              <td class="py-3 px-3">
                <div class="font-semibold text-slate-800">{{ item.vendor_name }}</div>
                <div class="text-[11px] text-slate-500">
                  RC: {{ item.vendor_rc_number || 'N/A' }} • {{ item.vendor_bank_name || 'Bank' }}
                </div>
              </td>
              <td class="py-3 px-3 text-right">
                <div class="font-bold text-slate-900 text-sm">
                  ₦{{ formatNumber(item.estimated_amount) }}
                </div>
                <div class="text-[10px] text-slate-500">{{ item.delivery_period_days }} Days Delivery</div>
              </td>
              <td class="py-3 px-3">
                <span
                  class="px-2 py-0.5 rounded text-[11px] font-bold"
                  :class="{
                    'bg-sky-50 text-sky-700 border border-sky-200': item.threshold_level === 'Accounting Officer',
                    'bg-amber-50 text-amber-700 border border-amber-200': item.threshold_level === 'Ministerial Tenders Board',
                    'bg-purple-50 text-purple-700 border border-purple-200': item.threshold_level === 'Federal Executive Council',
                  }"
                >
                  {{ item.threshold_level }}
                </span>
                <div v-if="item.bpp_cert_no" class="text-[10px] text-emerald-600 mt-0.5">
                  Cert: {{ item.bpp_cert_no }}
                </div>
              </td>
              <td class="py-3 px-3">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider inline-block"
                  :class="{
                    'bg-slate-100 text-slate-600 border border-slate-200': item.status === 'draft',
                    'bg-blue-50 text-blue-700 border border-blue-200': item.status === 'approved',
                    'bg-indigo-50 text-indigo-700 border border-indigo-200': item.status === 'lpo_issued',
                    'bg-emerald-50 text-emerald-700 border border-emerald-200': item.status === 'dispatched_to_finance',
                  }"
                >
                  {{ formatStatus(item.status) }}
                </span>
                <div v-if="item.dispatched_payment_id" class="text-[10px] text-slate-500 mt-0.5">
                  F&A File #{{ item.dispatched_payment_id }}
                </div>
              </td>
              <td class="py-3 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                  <!-- Section 86: Evaluation Trigger -->
                  <button
                    v-if="item.status === 'draft'"
                    @click="openEvaluation(item)"
                    class="px-2.5 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-[11px] font-semibold transition-all cursor-pointer flex items-center gap-1"
                  >
                    <Icon icon="lucide:check-square" width="12" />
                    <span>Evaluate</span>
                  </button>

                  <!-- Section 87: Issue LPO -->
                  <button
                    v-if="item.status === 'approved'"
                    @click="generateLpo(item)"
                    class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded text-[11px] font-semibold transition-all cursor-pointer flex items-center gap-1"
                  >
                    <Icon icon="lucide:file-text" width="12" />
                    <span>Issue LPO</span>
                  </button>

                  <!-- Print LPO -->
                  <a
                    v-if="item.lpo_number"
                    :href="`/dashboard/procurement/${item.id}/print-lpo`"
                    target="_blank"
                    class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold transition-all inline-flex items-center gap-1 border border-slate-200"
                  >
                    <Icon icon="lucide:printer" width="12" />
                    <span>Print LPO</span>
                  </a>

                  <!-- Cross-Directorate Dispatch to Finance -->
                  <button
                    v-if="item.status === 'lpo_issued'"
                    @click="dispatchToFinance(item)"
                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[11px] font-semibold transition-all cursor-pointer flex items-center gap-1 shadow-xs"
                  >
                    <Icon icon="lucide:send" width="12" />
                    <span>Dispatch to F&A</span>
                  </button>

                  <!-- Dispatched Indicator -->
                  <span
                    v-if="item.status === 'dispatched_to_finance'"
                    class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1"
                  >
                    <Icon icon="lucide:check-circle-2" width="14" />
                    <span>In Finance</span>
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: New Procurement Requisition -->
    <div
      v-if="openNewProcurementModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-emerald-900 to-slate-900 text-white p-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:folder-plus" width="18" class="text-emerald-400" />
            <h3 class="font-bold text-sm">Initiate Statutory Procurement Requisition</h3>
          </div>
          <button @click="openNewProcurementModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitNewProcurement" class="p-5 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Project Title / Scope of Procurement *</label>
            <input
              v-model="newForm.title"
              type="text"
              required
              placeholder="e.g. Supply of ICT Infrastructure & Server Racks for Data Centre"
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Contractor / Vendor Name *</label>
              <input
                v-model="newForm.vendor_name"
                type="text"
                required
                placeholder="Vendor Ltd"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">CAC RC / BN Number</label>
              <input
                v-model="newForm.vendor_rc_number"
                type="text"
                placeholder="RC-123456"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Contract Sum (₦) *</label>
              <input
                v-model.number="newForm.estimated_amount"
                type="number"
                min="1000"
                step="any"
                required
                placeholder="e.g. 15000000"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none font-bold"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Timeline (Days)</label>
              <input
                v-model.number="newForm.delivery_period_days"
                type="number"
                min="1"
                placeholder="30"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
              />
            </div>
          </div>

          <!-- Dynamic BPP Threshold Visual Indicator -->
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <div class="text-[11px] text-slate-600 font-semibold mb-1">Statutory BPP 2007 Classification:</div>
            <div class="flex items-center gap-2">
              <span
                class="px-2.5 py-1 rounded text-xs font-bold"
                :class="{
                  'bg-sky-100 text-sky-800 border border-sky-300': dynamicThreshold === 'Accounting Officer',
                  'bg-amber-100 text-amber-800 border border-amber-300': dynamicThreshold === 'Ministerial Tenders Board',
                  'bg-purple-100 text-purple-800 border border-purple-300': dynamicThreshold === 'Federal Executive Council',
                }"
              >
                {{ dynamicThreshold }}
              </span>
              <span class="text-[11px] text-slate-500">
                {{ dynamicThresholdHint }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Vendor Bank Name</label>
              <input
                v-model="newForm.vendor_bank_name"
                type="text"
                placeholder="e.g. Zenith Bank Plc"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Vendor Account No</label>
              <input
                v-model="newForm.vendor_account_no"
                type="text"
                placeholder="10-digit NUBAN"
                class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none"
              />
            </div>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="openNewProcurementModal = false"
              class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Create Requisition Dossier</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: BPP Statutory Evaluation -->
    <div
      v-if="selectedItem"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
    >
      <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-amber-800 to-slate-900 text-white p-4 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:check-circle" width="18" class="text-amber-400" />
            <h3 class="font-bold text-sm">BPP Statutory Evaluation & Certification</h3>
          </div>
          <button @click="selectedItem = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitEvaluation" class="p-5 space-y-4">
          <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
            <div class="font-bold text-slate-900">{{ selectedItem.title }}</div>
            <div class="text-slate-600 mt-1">Vendor: <strong>{{ selectedItem.vendor_name }}</strong></div>
            <div class="text-slate-600 mt-0.5">Amount: <strong>₦{{ formatNumber(selectedItem.estimated_amount) }}</strong> ({{ selectedItem.threshold_level }})</div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Evaluation Decision *</label>
            <select
              v-model="evalForm.decision"
              required
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
            >
              <option value="approved">Recommend for Award / Approve</option>
              <option value="needs_clarification">Request Clarification from Vendor</option>
              <option value="rejected">Disqualify / Reject Tender</option>
            </select>
          </div>

          <div v-if="selectedItem.threshold_level !== 'Accounting Officer'">
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              BPP Certificate of "No Objection" / Tenders Board Approval Ref *
            </label>
            <input
              v-model="evalForm.bpp_cert_no"
              type="text"
              required
              placeholder="e.g. BPP/NO-OBJ/2026/0491"
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none font-mono"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Evaluation Committee Notes *</label>
            <textarea
              v-model="evalForm.evaluation_notes"
              rows="3"
              required
              placeholder="Record technical evaluation scores, commercial bid comparisons, and statutory BPP compliance observations..."
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none"
            ></textarea>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="selectedItem = null"
              class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Record Statutory Evaluation</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const props = defineProps({
  user: { type: Object, default: () => ({}) },
  department: { type: Object, default: () => ({}) }
});

const emit = defineEmits(['refresh', 'dispatched']);

const loading = ref(false);
const saving = ref(false);
const items = ref([]);
const activeTab = ref('all');
const openNewProcurementModal = ref(false);
const selectedItem = ref(null);

const newForm = ref({
  title: '',
  vendor_name: '',
  vendor_rc_number: '',
  vendor_bank_name: '',
  vendor_account_no: '',
  estimated_amount: '',
  delivery_period_days: 30,
});

const evalForm = ref({
  decision: 'approved',
  bpp_cert_no: '',
  evaluation_notes: '',
});

const dynamicThreshold = computed(() => {
  const amt = Number(newForm.value.estimated_amount) || 0;
  if (amt <= 50000000) return 'Accounting Officer';
  if (amt <= 100000000) return 'Ministerial Tenders Board';
  return 'Federal Executive Council';
});

const dynamicThresholdHint = computed(() => {
  const amt = Number(newForm.value.estimated_amount) || 0;
  if (amt <= 50000000) return 'Approved by Accounting Officer / Permanent Secretary (<= ₦50M)';
  if (amt <= 100000000) return 'Requires Ministerial Tenders Board (MTB) adjudication (₦50M - ₦100M)';
  return 'Requires Federal Executive Council (FEC) Approval & BPP Certificate (> ₦100M)';
});

const filteredItems = computed(() => {
  if (activeTab.value === 'tenders') {
    return items.value.filter(i => i.status === 'draft');
  }
  if (activeTab.value === 'lpo') {
    return items.value.filter(i => ['approved', 'lpo_issued'].includes(i.status));
  }
  if (activeTab.value === 'dispatched') {
    return items.value.filter(i => i.status === 'dispatched_to_finance');
  }
  return items.value;
});

const formatNumber = (num) => {
  return Number(num || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatStatus = (st) => {
  if (st === 'draft') return 'Pending Evaluation';
  if (st === 'approved') return 'BPP Approved';
  if (st === 'lpo_issued') return 'LPO Issued';
  if (st === 'dispatched_to_finance') return 'Dispatched to F&A';
  return st;
};

const fetchProcurements = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/dashboard/procurements');
    if (res.data && res.data.data) {
      items.value = res.data.data.data || res.data.data || [];
    }
  } catch (err) {
    console.error('Failed to fetch procurements:', err);
  } finally {
    loading.value = false;
  }
};

const submitNewProcurement = async () => {
  saving.value = true;
  try {
    const res = await axios.post('/dashboard/procurement/create', newForm.value);
    alert(res.data.message || 'Requisition created successfully.');
    openNewProcurementModal.value = false;
    newForm.value = {
      title: '',
      vendor_name: '',
      vendor_rc_number: '',
      vendor_bank_name: '',
      vendor_account_no: '',
      estimated_amount: '',
      delivery_period_days: 30,
    };
    fetchProcurements();
  } catch (err) {
    alert(err.response?.data?.message || 'Error creating requisition.');
  } finally {
    saving.value = false;
  }
};

const openEvaluation = (item) => {
  selectedItem.value = item;
  evalForm.value = {
    decision: 'approved',
    bpp_cert_no: item.bpp_cert_no || '',
    evaluation_notes: item.evaluation_notes || '',
  };
};

const submitEvaluation = async () => {
  if (!selectedItem.value) return;
  saving.value = true;
  try {
    const res = await axios.post(`/dashboard/procurement/${selectedItem.value.id}/evaluate`, evalForm.value);
    alert(res.data.message || 'Evaluation recorded successfully.');
    selectedItem.value = null;
    fetchProcurements();
  } catch (err) {
    alert(err.response?.data?.message || 'Error recording evaluation.');
  } finally {
    saving.value = false;
  }
};

const generateLpo = async (item) => {
  if (!confirm(`Generate statutory Local Purchase Order for "${item.title}"?`)) return;
  try {
    const res = await axios.post(`/dashboard/procurement/${item.id}/generate-lpo`);
    alert(res.data.message || 'LPO generated successfully.');
    fetchProcurements();
  } catch (err) {
    alert(err.response?.data?.message || 'Error generating LPO.');
  }
};

const dispatchToFinance = async (item) => {
  if (!confirm(`Dispatch LPO (${item.lpo_number}) to Finance & Accounts (Other Charges Section 81) for Vote Book Encumbrance?`)) return;
  try {
    const res = await axios.post(`/dashboard/procurement/${item.id}/dispatch-to-finance`);
    alert(res.data.message || 'Dispatched to Finance & Accounts.');
    fetchProcurements();
    emit('dispatched', res.data.data);
  } catch (err) {
    alert(err.response?.data?.message || 'Error dispatching to Finance.');
  }
};

onMounted(() => {
  fetchProcurements();
});
</script>
