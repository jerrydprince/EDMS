<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-950 via-slate-900 to-blue-950 text-white shadow-md border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30 uppercase tracking-wider">
            SOP Section 1.8 & Appendix A
          </span>
          <span class="text-xs text-slate-300">Central File Registry Integration</span>
        </div>
        <h2 class="text-xl font-black mt-1 tracking-tight text-white flex items-center gap-2">
          <span>Sub-Registries & Appendix A Movement Deck</span>
          <Icon icon="lucide:folder-sync" class="text-amber-400" width="20" />
        </h2>
        <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
          Enforces the statutory <strong>24-Hour Rule</strong>: files must not dwell on any officer's table for more than 24 hours without treatment. Connected directly into the Central File Registry backbone.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="openNewMovementModal = true"
          class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer"
        >
          <Icon icon="lucide:file-plus" width="16" />
          <span>Dispatch File (Appendix A)</span>
        </button>
      </div>
    </div>

    <!-- 24-Hour Rule Live Watchdog Alert Banner -->
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2.5">
        <div class="p-2 bg-amber-500 text-white rounded-lg">
          <Icon icon="lucide:alarm-clock" width="20" />
        </div>
        <div>
          <div class="font-bold text-sm">24-Hour Table SLA Watchdog (SOP Rule 1.8.4)</div>
          <div class="text-amber-800 text-[11px]">
            Officers holding files past 24 hours are flagged in red for administrative query & registry recall.
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-800 border border-red-300">
          {{ breachedCardsCount }} SLA Breached Table(s)
        </span>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="relative max-w-sm w-full">
        <Icon icon="lucide:search" width="16" class="absolute left-3 top-3 text-slate-400" />
        <input
          v-model="cardSearch"
          @input="debouncedFetchCards"
          type="text"
          placeholder="Search by File No, Subject, Office..."
          class="w-full text-xs pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 focus:border-blue-600 outline-none shadow-xs"
        />
      </div>

      <div class="flex items-center gap-2 text-xs">
        <select v-model="filterStatus" @change="fetchCards" class="p-2 rounded-xl border border-slate-300 bg-white font-medium outline-none">
          <option value="all">All Movements</option>
          <option value="pending">On Table / In Transit</option>
          <option value="breached">SLA Breached (>24 Hours)</option>
          <option value="treated">Treated & Returned</option>
        </select>
      </div>
    </div>

    <!-- Movement Cards Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <div v-if="loadingCards" class="py-16 text-center text-slate-500">
        <Icon icon="lucide:loader" class="animate-spin mx-auto text-blue-600 mb-2" width="28" />
        <p class="text-xs">Loading Appendix A File Movement Cards...</p>
      </div>
      <div v-else-if="cardsList.length === 0" class="py-16 text-center text-slate-500">
        <Icon icon="lucide:folder" class="mx-auto text-slate-300 mb-2" width="36" />
        <p class="text-sm font-bold text-slate-700">No File Movement Cards on Record</p>
        <p class="text-xs text-slate-400 mt-1">Dispatch a file from sub-registry to initiate movement tracking.</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
              <th class="py-3 px-4">File No / Subject</th>
              <th class="py-3 px-4">Routing (From -> To)</th>
              <th class="py-3 px-4">Date/Time Dispatched</th>
              <th class="py-3 px-4">Holding Hours (SLA)</th>
              <th class="py-3 px-4">Treatment Status</th>
              <th class="py-3 px-4 text-center">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="card in cardsList" :key="card.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3.5 px-4">
                <div class="font-mono font-bold text-blue-900">{{ card.file_no }}</div>
                <div class="font-semibold text-slate-800 text-[11px] mt-0.5 max-w-xs">{{ card.subject }}</div>
              </td>
              <td class="py-3.5 px-4">
                <div class="text-slate-600">{{ card.from_office }}</div>
                <div class="text-[11px] text-indigo-700 font-bold flex items-center gap-1 mt-0.5">
                  <Icon icon="lucide:arrow-right" width="12" />
                  <span>{{ card.to_office }}</span>
                </div>
              </td>
              <td class="py-3.5 px-4 text-slate-700 font-medium">
                {{ formatDateTime(card.date_time_out) }}
              </td>
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-1.5">
                  <span
                    class="px-2 py-0.5 rounded text-[11px] font-black"
                    :class="{
                      'bg-red-100 text-red-800 border border-red-300 animate-pulse': card.sla_breached && !card.is_treated,
                      'bg-amber-100 text-amber-800': !card.sla_breached && card.holding_hours >= 18 && !card.is_treated,
                      'bg-slate-100 text-slate-700': !card.sla_breached && card.holding_hours < 18 && !card.is_treated,
                      'bg-emerald-100 text-emerald-800': card.is_treated,
                    }"
                  >
                    {{ card.holding_hours }} Hours
                  </span>
                  <span v-if="card.sla_breached && !card.is_treated" class="text-[10px] text-red-600 font-bold">
                    SLA Breached (>24h)
                  </span>
                </div>
              </td>
              <td class="py-3.5 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="card.is_treated ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ card.is_treated ? 'Treated / Returned' : 'On Table' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="viewMovementCardDoc(card)"
                    class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-white rounded text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                    title="Print Appendix A Card"
                  >
                    <Icon icon="lucide:printer" width="12" />
                    <span>Card</span>
                  </button>
                  <button
                    v-if="!card.is_treated"
                    @click="markTreated(card)"
                    class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded text-[11px] font-bold shadow-xs transition-all cursor-pointer inline-flex items-center gap-1"
                  >
                    <Icon icon="lucide:check" width="12" />
                    <span>Treated</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: Dispatch File / Register Movement (Appendix A) -->
    <div v-if="openNewMovementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="bg-gradient-to-r from-slate-950 to-blue-950 text-white p-5 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:folder-plus" width="18" class="text-blue-400" />
            <h3 class="font-bold text-sm">Issue Appendix A File Movement Card</h3>
          </div>
          <button @click="openNewMovementModal = false" class="text-slate-400 hover:text-white cursor-pointer">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitFileMovement" class="p-6 space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">File Number (Central Registry Ref) *</label>
            <input v-model="cardForm.file_no" type="text" required placeholder="e.g. OAGF/ADM/POOL/2026/044" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-mono font-bold" />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">File Subject / Title *</label>
            <input v-model="cardForm.subject" type="text" required placeholder="e.g. Posting of Treasury Officers to Foreign Missions" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none font-semibold" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Originating Office *</label>
              <input v-model="cardForm.from_office" type="text" required placeholder="e.g. Sub-Registry (Pool)" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Destination Table / Office *</label>
              <input v-model="cardForm.to_office" type="text" required placeholder="e.g. Director Administration Table" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Transit Remarks / Urgency</label>
            <input v-model="cardForm.remarks" type="text" placeholder="e.g. Urgent Treatment - Submission to AGF" class="w-full p-2.5 rounded-lg border border-slate-300 outline-none" />
          </div>

          <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 flex items-start gap-2">
            <Icon icon="lucide:alarm-clock" width="16" class="shrink-0 mt-0.5 text-amber-700" />
            <div>
              <div class="font-bold">24-Hour Table SLA Activated</div>
              <div class="text-[11px] text-amber-800 mt-0.5">
                The destination desk must treat or forward this file within 24 hours to prevent an automated red SLA breach alert.
              </div>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
            <button type="button" @click="openNewMovementModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold cursor-pointer">
              Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
              <Icon v-if="saving" icon="lucide:loader" class="animate-spin" width="14" />
              <span>Dispatch & Start 24-Hr SLA</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Statutory Document Modal Preview -->
    <StatutoryDocumentModal
      :show="showDocModal"
      docType="movement_card"
      :docData="activeCardDoc"
      @close="showDocModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';
import StatutoryDocumentModal from './StatutoryDocumentModal.vue';

const loadingCards = ref(false);
const saving = ref(false);
const cardsList = ref([]);
const cardSearch = ref('');
const filterStatus = ref('all');
const openNewMovementModal = ref(false);

const showDocModal = ref(false);
const activeCardDoc = ref(null);

const cardForm = ref({
  file_no: '',
  subject: '',
  from_office: 'Sub-Registry (Admin)',
  to_office: 'Director Administration Table',
  remarks: '',
});

const breachedCardsCount = computed(() => {
  return cardsList.value.filter(c => c.sla_breached && !c.is_treated).length;
});

const formatDateTime = (dtStr) => {
  if (!dtStr) return 'N/A';
  const d = new Date(dtStr);
  return d.toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

let debounceTimer = null;
const debouncedFetchCards = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchCards();
  }, 400);
};

const fetchCards = async () => {
  loadingCards.value = true;
  try {
    const res = await axios.get('/dashboard/administration/movement-cards', {
      params: {
        search: cardSearch.value,
        sla_breached: filterStatus.value === 'breached' ? '1' : undefined,
        is_treated: filterStatus.value === 'treated' ? '1' : (filterStatus.value === 'pending' ? '0' : undefined),
      }
    });
    cardsList.value = res.data.data?.data || res.data.data || [];
  } catch (err) {
    console.error('Failed to load file movement cards:', err);
  } finally {
    loadingCards.value = false;
  }
};

const submitFileMovement = async () => {
  saving.value = true;
  try {
    const res = await axios.post('/dashboard/administration/movement-cards', cardForm.value);
    alert(res.data.message || 'File Movement Card registered.');
    openNewMovementModal.value = false;
    cardForm.value = {
      file_no: '',
      subject: '',
      from_office: 'Sub-Registry (Admin)',
      to_office: 'Director Administration Table',
      remarks: '',
    };
    fetchCards();
  } catch (err) {
    alert(err.response?.data?.message || 'Error registering file movement.');
  } finally {
    saving.value = false;
  }
};

const markTreated = async (card) => {
  const remarks = prompt('Enter treatment or transit remarks:', 'Treated and cleared to Central Registry');
  if (remarks === null) return;

  try {
    const res = await axios.post(`/dashboard/administration/movement-cards/${card.id}/treat`, { remarks });
    alert(res.data.message || 'File marked as treated.');
    fetchCards();
  } catch (err) {
    alert(err.response?.data?.message || 'Error updating card.');
  }
};

const viewMovementCardDoc = (card) => {
  activeCardDoc.value = card;
  showDocModal.value = true;
};

onMounted(() => {
  fetchCards();
});
</script>
