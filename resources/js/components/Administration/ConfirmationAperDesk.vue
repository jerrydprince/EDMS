<template>
  <div class="space-y-6">
    <!-- Header Card -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 text-white shadow-md border border-indigo-900/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 uppercase tracking-wider">
            SOP Section 1.3 & 1.4
          </span>
          <span class="text-xs text-slate-300">Public Service Rules (PSR 020301)</span>
        </div>
        <h2 class="text-xl font-black mt-1 tracking-tight text-white flex items-center gap-2">
          <span>Appointments, Confirmations & APER Promotion Desk</span>
          <Icon icon="lucide:award" class="text-amber-400" width="20" />
        </h2>
        <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
          Statutory 24-month probationary confirmation countdown with mandatory 3-month advance trigger • Annual APER campaign ledger & Appendix D promotion vetting for the Federal Civil Service Commission (FCSC).
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="activeSubDesk = 'confirmations'"
          :class="['px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5', activeSubDesk === 'confirmations' ? 'bg-white text-indigo-950 shadow-sm' : 'bg-white/10 text-white hover:bg-white/20']"
        >
          <Icon icon="lucide:user-check" width="16" />
          <span>24-Month Confirmations</span>
        </button>
        <button
          @click="activeSubDesk = 'aper'"
          :class="['px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5', activeSubDesk === 'aper' ? 'bg-white text-indigo-950 shadow-sm' : 'bg-white/10 text-white hover:bg-white/20']"
        >
          <Icon icon="lucide:file-bar-chart" width="16" />
          <span>Annual APER Ledger</span>
        </button>
      </div>
    </div>

    <!-- SUB-DESK 1: 24-Month Confirmation Lifecycle -->
    <div v-if="activeSubDesk === 'confirmations'" class="space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="relative max-w-sm w-full">
          <Icon icon="lucide:search" width="16" class="absolute left-3 top-3 text-slate-400" />
          <input
            v-model="confSearch"
            @input="debouncedFetchConfirmations"
            type="text"
            placeholder="Search probation officers by name, IPPIS..."
            class="w-full text-xs pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 focus:border-indigo-600 outline-none shadow-xs"
          />
        </div>

        <div class="flex items-center gap-2 text-xs">
          <span class="inline-flex items-center gap-1 text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200 font-bold">
            <Icon icon="lucide:bell" width="14" />
            3-Month Advance Alert Active
          </span>
          <select v-model="confFilterStatus" @change="fetchConfirmations" class="p-2 rounded-xl border border-slate-300 bg-white font-medium outline-none">
            <option value="all">All Statuses</option>
            <option value="probation">Under Probation</option>
            <option value="recommended">Recommended by Committee</option>
            <option value="approved">Confirmed & Gazetted</option>
            <option value="deferred">Deferred (Adverse Report)</option>
          </select>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div v-if="loadingConf" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:loader" class="animate-spin mx-auto text-indigo-600 mb-2" width="28" />
          <p class="text-xs">Loading probation countdown timers...</p>
        </div>
        <div v-else-if="confirmationsList.length === 0" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:check-circle" class="mx-auto text-emerald-500 mb-2" width="36" />
          <p class="text-sm font-bold text-slate-700">No Probationary Records Found</p>
          <p class="text-xs text-slate-400 mt-1">All officers are either confirmed or nominal roll records are verified.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-4">Officer Name / IPPIS</th>
                <th class="py-3 px-4">Appointment Date</th>
                <th class="py-3 px-4">24-Month Due Date</th>
                <th class="py-3 px-4">Countdown & Advance Alert</th>
                <th class="py-3 px-4">Confirmation Status</th>
                <th class="py-3 px-4 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="c in confirmationsList" :key="c.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ c.user?.first_name }} {{ c.user?.last_name }}</div>
                  <div class="text-[11px] font-mono font-bold text-blue-700 mt-0.5">IPPIS: {{ c.user?.ippis_no || 'NOT ASSIGNED' }}</div>
                  <div class="text-[10px] text-slate-500">{{ c.user?.department?.name || 'Department' }}</div>
                </td>
                <td class="py-3.5 px-4 font-semibold text-slate-800">{{ c.appointment_date }}</td>
                <td class="py-3.5 px-4 font-bold text-slate-900">{{ c.confirmation_due_date }}</td>
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-2">
                    <span
                      class="px-2 py-0.5 rounded text-[11px] font-bold"
                      :class="{
                        'bg-red-100 text-red-800': c.is_overdue,
                        'bg-amber-100 text-amber-800': c.is_advance_alert,
                        'bg-slate-100 text-slate-700': !c.is_advance_alert && !c.is_overdue,
                      }"
                    >
                      {{ c.days_until_due <= 0 ? 'DUE / OVERDUE' : `${c.days_until_due} Days Left` }}
                    </span>
                    <span v-if="c.is_advance_alert" class="text-[10px] text-amber-700 font-bold flex items-center gap-0.5">
                      <Icon icon="lucide:alert-circle" width="12" />
                      Appraisal Triggered
                    </span>
                  </div>
                </td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="{
                      'bg-slate-100 text-slate-700 border border-slate-300': c.status === 'probation',
                      'bg-indigo-50 text-indigo-700 border border-indigo-200': c.status === 'recommended',
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': c.status === 'approved',
                      'bg-red-50 text-red-700 border border-red-200': c.status === 'deferred',
                    }"
                  >
                    {{ c.status }}
                  </span>
                  <div v-if="c.gazette_reference" class="text-[10px] font-mono text-slate-500 mt-0.5">
                    Gazette: {{ c.gazette_reference }}
                  </div>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <button
                    v-if="c.status !== 'approved'"
                    @click="openRecommendModal(c)"
                    class="px-2.5 py-1 bg-indigo-900 hover:bg-indigo-800 text-white rounded text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                  >
                    <Icon icon="lucide:clipboard-check" width="12" />
                    <span>Appraise / Vetting</span>
                  </button>
                  <span v-else class="text-[11px] text-emerald-700 font-bold flex items-center justify-center gap-1">
                    <Icon icon="lucide:check-circle" width="12" />
                    Gazetted
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SUB-DESK 2: Annual APER Campaign & Ledger (Appendix D) -->
    <div v-if="activeSubDesk === 'aper'" class="space-y-4">
      <!-- Campaign Announcement Alert -->
      <div class="p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 text-xs text-blue-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
          <div class="p-2 bg-blue-600 text-white rounded-lg">
            <Icon icon="lucide:calendar" width="18" />
          </div>
          <div>
            <div class="font-bold text-sm text-slate-900">Annual APER Campaign (Current Cycle: {{ new Date().getFullYear() }})</div>
            <div class="text-slate-600 text-[11px]">November 1 Call-up Circular • January/February 28 Final Submission Deadline (SOP Sec 1.4)</div>
          </div>
        </div>

        <div class="text-right">
          <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
            FCSC Guidelines Compliant
          </span>
        </div>
      </div>

      <!-- APER Table -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div v-if="loadingAper" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:loader" class="animate-spin mx-auto text-indigo-600 mb-2" width="28" />
          <p class="text-xs">Loading APER appraisal submissions...</p>
        </div>
        <div v-else-if="aperList.length === 0" class="py-16 text-center text-slate-500">
          <Icon icon="lucide:file-text" class="mx-auto text-slate-300 mb-2" width="36" />
          <p class="text-sm font-bold text-slate-700">No APER Submissions Found</p>
          <p class="text-xs text-slate-400 mt-1">Staff appraisal forms will populate here during the annual campaign.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                <th class="py-3 px-4">Officer / GL</th>
                <th class="py-3 px-4">Evaluation Year</th>
                <th class="py-3 px-4">Overall Score</th>
                <th class="py-3 px-4">Rating Category</th>
                <th class="py-3 px-4">FCSC Recommendation</th>
                <th class="py-3 px-4">Appendix D Checklist</th>
                <th class="py-3 px-4 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="a in aperList" :key="a.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900">{{ a.user?.first_name }} {{ a.user?.last_name }}</div>
                  <div class="text-[11px] font-mono text-slate-500">{{ a.grade_level }} • IPPIS: {{ a.user?.ippis_no }}</div>
                </td>
                <td class="py-3.5 px-4 font-bold text-slate-800">{{ a.year }}</td>
                <td class="py-3.5 px-4 font-black text-sm" :class="a.overall_score >= 70 ? 'text-emerald-700' : 'text-slate-800'">
                  {{ a.overall_score ? `${a.overall_score}%` : 'Pending' }}
                </td>
                <td class="py-3.5 px-4">
                  <span
                    v-if="a.rating_category"
                    class="px-2 py-0.5 rounded text-[10px] font-bold"
                    :class="{
                      'bg-emerald-100 text-emerald-800': a.rating_category === 'Outstanding' || a.rating_category === 'Very Good',
                      'bg-blue-100 text-blue-800': a.rating_category === 'Good',
                      'bg-amber-100 text-amber-800': a.rating_category === 'Fair',
                      'bg-red-100 text-red-800': a.rating_category === 'Poor',
                    }"
                  >
                    {{ a.rating_category }}
                  </span>
                  <span v-else class="text-slate-400">Not Graded</span>
                </td>
                <td class="py-3.5 px-4 font-semibold text-slate-800">{{ a.recommendation || 'Pending' }}</td>
                <td class="py-3.5 px-4">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                    :class="a.is_checklist_verified ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                  >
                    {{ a.is_checklist_verified ? 'VERIFIED' : 'PENDING AUDIT' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <button
                    @click="openEvaluateAperModal(a)"
                    class="px-2.5 py-1 bg-indigo-900 hover:bg-indigo-800 text-white rounded text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                  >
                    <Icon icon="lucide:sliders" width="12" />
                    <span>Evaluate</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL: Confirmation Recommendation & Vetting -->
    <div v-if="selectedConfirmation" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-indigo-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:user-check" width="18" class="text-indigo-400" />
            <h3 class="font-bold text-sm">Probation Confirmation Vetting (PSR 020301)</h3>
          </div>
          <button @click="selectedConfirmation = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitConfirmationAction" class="p-6 space-y-4 text-xs">
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <div class="font-bold text-slate-900 text-sm">{{ selectedConfirmation.user?.first_name }} {{ selectedConfirmation.user?.last_name }}</div>
            <div class="text-slate-600 mt-0.5">Date of Appointment: <strong>{{ selectedConfirmation.appointment_date }}</strong></div>
            <div class="text-slate-600 mt-0.5">Due Date: <strong>{{ selectedConfirmation.confirmation_due_date }}</strong></div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Administrative Decision *</label>
            <select v-model="confForm.action" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold">
              <option value="recommend">Recommend for Full Confirmation (Exams Passed & Satisfactory Conduct)</option>
              <option value="approve">Approve & Gazette Confirmation Immediately</option>
              <option value="defer">Defer Confirmation (Adverse Report / Failed Exams)</option>
            </select>
          </div>

          <div v-if="confForm.action === 'approve'">
            <label class="block font-bold text-slate-700 mb-1">Federal Gazette Reference Number</label>
            <input v-model="confForm.gazette_reference" type="text" placeholder="e.g. FGN Gazette Vol. 113 No. 42" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-mono" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Committee Findings & Statutory Notes *</label>
            <textarea v-model="confForm.recommendation_notes" rows="4" required placeholder="Verify completion of compulsory confirmation exams, vetting by departmental committee, and absence of pending disciplinary queries..." class="w-full p-2.5 rounded-lg border border-slate-300 outline-none"></textarea>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" @click="selectedConfirmation = null" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-indigo-900 hover:bg-indigo-800 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Record Statutory Ruling</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Evaluate APER Form (Appendix D) -->
    <div v-if="selectedAper" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-blue-950 to-slate-900 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:award" width="18" class="text-amber-400" />
            <h3 class="font-bold text-sm">APER Score & Appendix D Vetting</h3>
          </div>
          <button @click="selectedAper = null" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitAperEvaluation" class="p-6 space-y-4 text-xs">
          <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
            <div class="font-bold text-slate-900 text-sm">{{ selectedAper.user?.first_name }} {{ selectedAper.user?.last_name }}</div>
            <div class="text-slate-600 mt-0.5">Cycle: Year {{ selectedAper.year }} • Grade Level: {{ selectedAper.grade_level }}</div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Overall Appraisal Score (%) *</label>
              <input v-model.number="aperForm.overall_score" type="number" min="0" max="100" step="0.5" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-black text-sm" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Rating Category *</label>
              <select v-model="aperForm.rating_category" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold">
                <option value="Outstanding">Outstanding (85 - 100%)</option>
                <option value="Very Good">Very Good (70 - 84%)</option>
                <option value="Good">Good (55 - 69%)</option>
                <option value="Fair">Fair (40 - 54%)</option>
                <option value="Poor">Poor (Below 40%)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">FCSC Promotion Recommendation *</label>
            <select v-model="aperForm.recommendation" required class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-bold">
              <option value="Accelerated Promotion">Accelerated Promotion (Exceptional Merit)</option>
              <option value="Promotable">Promotable in Normal Turn</option>
              <option value="Normal">Satisfactory / Normal Service</option>
              <option value="Not Promotable">Not Recommended for Promotion</option>
            </select>
          </div>

          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input v-model="aperForm.is_checklist_verified" type="checkbox" class="rounded text-blue-600" />
              <span>Appendix D Vetting Checklist Fully Verified</span>
            </label>
            <p class="text-[10px] text-slate-500 mt-1">Confirms signatures of Reporting Officer, Countersigning Officer, and Departmental Committee Seal.</p>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" @click="selectedAper = null" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Record APER Score</span>
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

const activeSubDesk = ref('confirmations');
const loadingConf = ref(false);
const loadingAper = ref(false);
const saving = ref(false);

const confirmationsList = ref([]);
const aperList = ref([]);

// Modals
const selectedConfirmation = ref(null);
const selectedAper = ref(null);

// Filters
const confSearch = ref('');
const confFilterStatus = ref('all');

const confForm = ref({
  action: 'recommend',
  recommendation_notes: '',
  gazette_reference: '',
});

const aperForm = ref({
  overall_score: 75,
  rating_category: 'Very Good',
  recommendation: 'Promotable',
  is_checklist_verified: true,
});

let debounceTimer = null;
const debouncedFetchConfirmations = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchConfirmations();
  }, 400);
};

const fetchConfirmations = async () => {
  loadingConf.value = true;
  try {
    const res = await axios.get('/dashboard/administration/confirmations', {
      params: {
        search: confSearch.value,
        status: confFilterStatus.value,
      }
    });
    confirmationsList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to load confirmations:', err);
  } finally {
    loadingConf.value = false;
  }
};

const fetchAper = async () => {
  loadingAper.value = true;
  try {
    const res = await axios.get('/dashboard/administration/aper');
    aperList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to load APER:', err);
  } finally {
    loadingAper.value = false;
  }
};

const openRecommendModal = (conf) => {
  selectedConfirmation.value = conf;
  confForm.value = {
    action: 'recommend',
    recommendation_notes: conf.recommendation_notes || '',
    gazette_reference: conf.gazette_reference || '',
  };
};

const submitConfirmationAction = async () => {
  if (!selectedConfirmation.value) return;
  saving.value = true;
  try {
    const res = await axios.post(`/dashboard/administration/confirmations/${selectedConfirmation.value.id}/recommend`, confForm.value);
    alert(res.data.message || 'Confirmation status updated.');
    selectedConfirmation.value = null;
    fetchConfirmations();
  } catch (err) {
    alert(err.response?.data?.message || 'Error processing confirmation.');
  } finally {
    saving.value = false;
  }
};

const openEvaluateAperModal = (aper) => {
  selectedAper.value = aper;
  aperForm.value = {
    overall_score: aper.overall_score || 75,
    rating_category: aper.rating_category || 'Very Good',
    recommendation: aper.recommendation || 'Promotable',
    is_checklist_verified: aper.is_checklist_verified ?? true,
  };
};

const submitAperEvaluation = async () => {
  if (!selectedAper.value) return;
  saving.value = true;
  try {
    const res = await axios.post(`/dashboard/administration/aper/${selectedAper.value.id}/evaluate`, aperForm.value);
    alert(res.data.message || 'APER evaluation saved.');
    selectedAper.value = null;
    fetchAper();
  } catch (err) {
    alert(err.response?.data?.message || 'Error evaluating APER.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchConfirmations();
  fetchAper();
});
</script>
