<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-950 via-slate-900 to-teal-950 text-white shadow-md border border-emerald-900/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 uppercase tracking-wider">
            SOP Section 1.5 & 1.6
          </span>
          <span class="text-xs text-slate-300">Training & Staff Welfare Division</span>
        </div>
        <h2 class="text-xl font-black mt-1 tracking-tight text-white flex items-center gap-2">
          <span>Staff Welfare & Statutory Training Desk</span>
          <Icon icon="lucide:heart-handshake" class="text-emerald-400" width="20" />
        </h2>
        <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
          Enforcing the statutory 48-Hour Service Level Agreement (SLA) for burial, medical, and compassionate welfare claims • Civil service training development programs & professional accounting sponsorships.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="openNewClaimModal = true"
          class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer"
        >
          <Icon icon="lucide:plus-circle" width="16" />
          <span>Submit Welfare Claim</span>
        </button>
      </div>
    </div>

    <!-- Sub-tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
      <button
        @click="subTab = 'welfare'"
        :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', subTab === 'welfare' ? 'bg-emerald-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100']"
      >
        <Icon icon="lucide:clock" width="16" />
        <span>Staff Welfare Claims (48-Hr SLA)</span>
      </button>
      <button
        @click="subTab = 'training'"
        :class="['px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2', subTab === 'training' ? 'bg-emerald-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100']"
      >
        <Icon icon="lucide:graduation-cap" width="16" />
        <span>Statutory Training & Capacity Building</span>
      </button>
    </div>

    <!-- SUB-TAB 1: Welfare Claims -->
    <div v-if="subTab === 'welfare'" class="space-y-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div v-if="loadingClaims" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:loader" class="animate-spin mx-auto text-emerald-600 mb-2" width="28" />
          <p class="text-xs">Monitoring 48-hour welfare SLA...</p>
        </div>
        <div v-else-if="claimsList.length === 0" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:check-circle" class="mx-auto text-emerald-500 mb-2" width="36" />
          <p class="text-sm font-bold text-slate-700">No Welfare Claims on Record</p>
          <p class="text-xs text-slate-400 mt-1">Submit a staff claim to begin the 48-hour treatment cycle.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-4">Claimant Officer</th>
                <th class="py-3 px-4">Claim Type</th>
                <th class="py-3 px-4">Requested / Approved (₦)</th>
                <th class="py-3 px-4">48-Hour SLA Watchdog</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="c in claimsList" :key="c.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ c.user?.first_name }} {{ c.user?.last_name }}</div>
                  <div class="text-[11px] text-slate-500">{{ c.user?.department?.name || 'Department' }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <span class="font-bold uppercase tracking-wide text-slate-800 text-[11px]">{{ c.claim_type }}</span>
                  <div class="text-[10px] text-slate-500 truncate max-w-xs">{{ c.justification }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">₦{{ formatCurrency(c.amount_requested) }}</div>
                  <div v-if="c.amount_approved > 0" class="text-[11px] text-emerald-700 font-bold mt-0.5">
                    Approved: ₦{{ formatCurrency(c.amount_approved) }}
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-1.5">
                    <span
                      class="px-2 py-0.5 rounded text-[11px] font-bold"
                      :class="{
                        'bg-red-100 text-red-800': c.is_sla_breached,
                        'bg-amber-100 text-amber-800': !c.is_sla_breached && c.hours_remaining <= 12 && c.status === 'pending',
                        'bg-emerald-100 text-emerald-800': !c.is_sla_breached && c.hours_remaining > 12,
                        'bg-slate-100 text-slate-600': inArray(c.status, ['approved', 'disbursed', 'rejected']),
                      }"
                    >
                      {{ inArray(c.status, ['approved', 'disbursed', 'rejected']) ? 'COMPLETED' : (c.is_sla_breached ? 'SLA BREACHED' : `${c.hours_remaining}h Left`) }}
                    </span>
                  </div>
                  <div class="text-[10px] text-slate-400 mt-0.5">Deadline: {{ c.sla_deadline }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="{
                      'bg-amber-50 text-amber-700 border border-amber-200': c.status === 'pending',
                      'bg-indigo-50 text-indigo-700 border border-indigo-200': c.status === 'recommended',
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': c.status === 'approved',
                      'bg-purple-50 text-purple-700 border border-purple-200': c.status === 'disbursed',
                      'bg-red-50 text-red-700 border border-red-200': c.status === 'rejected',
                    }"
                  >
                    {{ c.status }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div v-if="c.status === 'pending'" class="flex items-center justify-center gap-1">
                    <button
                      @click="processClaim(c, 'recommend')"
                      class="px-2 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded text-[10px] font-bold transition-all cursor-pointer"
                    >
                      Recommend
                    </button>
                    <button
                      @click="processClaim(c, 'approve')"
                      class="px-2 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[10px] font-bold transition-all cursor-pointer"
                    >
                      Approve
                    </button>
                  </div>
                  <div v-else-if="c.status === 'recommended'" class="flex items-center justify-center gap-1">
                    <button
                      @click="processClaim(c, 'approve')"
                      class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[11px] font-bold transition-all cursor-pointer"
                    >
                      Approve
                    </button>
                  </div>
                  <div v-else-if="c.status === 'approved'" class="flex items-center justify-center gap-1">
                    <button
                      @click="processClaim(c, 'disburse')"
                      class="px-2.5 py-1 bg-purple-600 hover:bg-purple-500 text-white rounded text-[11px] font-bold transition-all cursor-pointer"
                    >
                      Disburse
                    </button>
                  </div>
                  <span v-else class="text-[11px] text-slate-400 font-medium">Treated</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SUB-TAB 2: Statutory Training Programs -->
    <div v-if="subTab === 'training'" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-blue-50 text-blue-700 rounded-xl">
              <Icon icon="lucide:book-open" width="20" />
            </div>
            <h4 class="font-bold text-sm text-slate-900">Mandatory Continuing Professional Education (MCPE)</h4>
          </div>
          <p class="text-xs text-slate-600 leading-relaxed">
            Annual statutory MCPE program for Treasury Accountants and Auditors under ICAN & ANAN continuous professional development requirements.
          </p>
          <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500">Cycle: 2026/2027</span>
            <span class="font-bold text-blue-700">Accredited</span>
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-indigo-50 text-indigo-700 rounded-xl">
              <Icon icon="lucide:laptop" width="20" />
            </div>
            <h4 class="font-bold text-sm text-slate-900">GIFMIS & IPSAS Accrual Capacity Labs</h4>
          </div>
          <p class="text-xs text-slate-600 leading-relaxed">
            Specialized hands-on technical training on Oracle GIFMIS financials, TSA reconciliation, and Accrual IPSAS reporting for newly posted pool officers.
          </p>
          <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500">Format: Quarterly Cohorts</span>
            <span class="font-bold text-indigo-700">In Progress</span>
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3">
          <div class="flex items-center gap-2">
            <div class="p-2 bg-emerald-50 text-emerald-700 rounded-xl">
              <Icon icon="lucide:award" width="20" />
            </div>
            <h4 class="font-bold text-sm text-slate-900">Public Service Examination (Compulsory Confirmation)</h4>
          </div>
          <p class="text-xs text-slate-600 leading-relaxed">
            Statutory preparatory masterclasses for Administrative & Treasury officers preparing for the mandatory civil service confirmation examinations (ASCON).
          </p>
          <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
            <span class="text-slate-500">Eligibility: Probationers</span>
            <span class="font-bold text-emerald-700">Enrolling</span>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Submit Welfare Claim -->
    <div v-if="openNewClaimModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-emerald-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:heart-handshake" width="18" class="text-emerald-400" />
            <h3 class="font-bold text-sm">Submit Staff Welfare Claim (48-Hr SLA)</h3>
          </div>
          <button @click="openNewClaimModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitWelfareClaim" class="p-6 space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Claimant Officer IPPIS / ID *</label>
            <input v-model.number="claimForm.user_id" type="number" required placeholder="Officer User ID (e.g. 43)" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-mono" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Claim Category *</label>
              <select v-model="claimForm.claim_type" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-semibold">
                <option value="burial">Burial Assistance (Bereavement)</option>
                <option value="medical">Medical Assistance / Emergency</option>
                <option value="compassionate">Compassionate Welfare Grant</option>
                <option value="nhis">NHIS Co-pay Subsidy</option>
                <option value="housing_loan">Federal Housing Loan Facilitation</option>
              </select>
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Amount Requested (₦) *</label>
              <input v-model.number="claimForm.amount_requested" type="number" min="1000" step="500" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Statutory Justification & Circumstances *</label>
            <textarea v-model="claimForm.justification" rows="3" required placeholder="Detail the medical report, death certificate reference, or compassionate grounds..." class="w-full p-2.5 rounded-lg border border-slate-300 outline-none"></textarea>
          </div>

          <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 flex items-start gap-2">
            <Icon icon="lucide:clock" width="16" class="shrink-0 mt-0.5 text-emerald-700" />
            <div>
              <div class="font-bold">Automated 48-Hour SLA Watchdog</div>
              <div class="text-[11px] text-emerald-800 mt-0.5">
                Upon submission, a 48-hour treatment countdown starts. Director of Administration is alerted to disburse eligible claims within the window.
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" @click="openNewClaimModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Submit for 48-Hr Treatment</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const subTab = ref('welfare');
const loadingClaims = ref(false);
const saving = ref(false);
const claimsList = ref([]);
const openNewClaimModal = ref(false);

const claimForm = ref({
  user_id: 43,
  claim_type: 'burial',
  amount_requested: 250000,
  justification: '',
});

const inArray = (val, arr) => arr.includes(val);

const formatCurrency = (val) => {
  return Number(val || 0).toLocaleString('en-NG', { minimumFractionDigits: 2 });
};

const fetchWelfareClaims = async () => {
  loadingClaims.value = true;
  try {
    const res = await axios.get('/dashboard/administration/welfare');
    claimsList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to load welfare claims:', err);
  } finally {
    loadingClaims.value = false;
  }
};

const submitWelfareClaim = async () => {
  saving.value = true;
  try {
    const res = await axios.post('/dashboard/administration/welfare', claimForm.value);
    alert(res.data.message || 'Welfare claim submitted.');
    openNewClaimModal.value = false;
    fetchWelfareClaims();
  } catch (err) {
    alert(err.response?.data?.message || 'Error submitting claim.');
  } finally {
    saving.value = false;
  }
};

const processClaim = async (claim, action) => {
  let promptAmount = claim.amount_requested;
  if (action === 'approve') {
    const userInput = prompt('Enter approved amount (₦):', claim.amount_requested);
    if (!userInput) return;
    promptAmount = Number(userInput);
  } else if (!confirm(`Are you sure you want to ${action} claim #${claim.id}?`)) {
    return;
  }

  try {
    const res = await axios.post(`/dashboard/administration/welfare/${claim.id}/process`, {
      action: action,
      amount_approved: promptAmount,
    });
    alert(res.data.message || 'Claim processed.');
    fetchWelfareClaims();
  } catch (err) {
    alert(err.response?.data?.message || 'Error processing claim.');
  }
};

onMounted(() => {
  fetchWelfareClaims();
});
</script>
