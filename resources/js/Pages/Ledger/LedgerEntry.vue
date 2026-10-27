<template>
  <section>
    <!-- HEADER STARTS -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between pr-9 pt-4">
      <div class="flex items-center h-auto">
        <img :src="LedgerImg" alt="ledger icon" />
        <div class="-mt-5">
          <p class="text-gray-800 text-2xl md:text-3xl mb-1">Advances & General Ledger</p>
          <p class="text-gray-400 text-xs md:text-sm font-extralight">
            View, Track & Manage Federal Treasury Ledger Entries and FR 1420 Compliance
          </p>
        </div>
      </div>
      <div class="flex items-center gap-x-3 -mt-4 ml-6 md:ml-0">
        <GenerateReportButton @select="generateReportFunction" />
        <button 
          v-if="can('ledger create') || hasRole('super admin')"
          @click="newLedgerEntryModal = true"
          class="flex gap-2 items-center bg-[#0B5C16] text-white hover:bg-[#084510] transition-colors rounded-lg py-2 px-3 md:px-4 text-sm md:text-base shadow-sm"
        >
          <Icon icon="ic:outline-plus" width="20" />
          <p>Create Entry</p>
        </button>
      </div>
    </div>
    <!-- HEADER ENDS -->

    <hr class="hidden md:block" />

    <!-- BODY STARTS -->
    <section class="px-9 pb-10">
      <!-- FILTERS & SEARCH ROW -->
      <section class="flex flex-col md:flex-row items-start md:items-center justify-between pt-6 gap-4">
        <!-- Status Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 max-w-full">
          <button 
            v-for="tab in filterTabs" :key="tab.value"
            @click="selectedStatus = tab.value"
            :class="[
              'px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer whitespace-nowrap',
              selectedStatus === tab.value 
                ? 'bg-[#0B5C16] text-white shadow-xs' 
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            ]"
          >
            {{ tab.label }}
          </button>
        </div>

        <div class="flex items-center border border-gray-200 bg-white p-2 rounded-xl gap-2 text-sm shadow-xs w-full md:w-auto">
          <i class="pi pi-search text-gray-400" style="font-size: 0.9rem"></i>
          <input type="text" v-model="searchValue" placeholder="Search by Ref, Narration, Amount..."
            class="w-full md:w-80 h-7 border-0 outline-none text-gray-700 text-xs md:text-sm bg-transparent" />
          <button v-if="searchValue" @click="searchValue = ''" class="text-gray-400 hover:text-gray-600">
            <Icon icon="mdi:close" width="16" />
          </button>
        </div>
      </section>

      <!-- TABLE CONTAINER -->
      <div class="mt-5 rounded-xl">
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-xs">
          <table class="min-w-full text-gray-600 text-xs md:text-sm">
            <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase text-[11px] tracking-wider font-semibold">
              <tr class="text-left">
                <th class="py-3.5 pl-4 w-10">
                  <Checkbox v-model="selectAll" @update:modelValue="toggleAll" class="h-4 w-4" />
                </th>
                <th class="py-3 px-3">Due Date</th>
                <th class="py-3 px-3">Reference</th>
                <th class="py-3 px-3">Narration / Description</th>
                <th class="py-3 px-3 text-right">Debit (DR)</th>
                <th class="py-3 px-3 text-right">Credit (CR)</th>
                <th class="py-3 px-3 text-right">Balance</th>
                <th class="py-3 px-3 text-center">Status</th>
                <th class="py-3 pr-4 text-center w-16">Action</th>
              </tr>
            </thead>
            <tbody v-if="filteredLedgers.length > 0" class="divide-y divide-gray-100">
              <tr v-for="(item, index) in filteredLedgers" :key="item.id || index"
                class="hover:bg-slate-50/80 transition-colors cursor-pointer text-xs md:text-sm">
                <td class="py-3.5 pl-4 align-middle">
                  <Checkbox v-model="checkedItems[index]" class="h-4 w-4" />
                </td>

                <td class="py-3 px-3 align-middle font-medium text-gray-900 whitespace-nowrap">
                  {{ formatDate(item.due_date || item.created_at) }}
                </td>

                <td class="py-3 px-3 align-middle font-mono font-semibold text-gray-800 whitespace-nowrap">
                  {{ item.ref_no || '---' }}
                </td>

                <td class="py-3 px-3 align-middle text-gray-700 max-w-xs truncate" :title="item.narration">
                  {{ item.narration || 'General Ledger Entry' }}
                </td>

                <td class="py-3 px-3 align-middle text-right font-mono text-gray-800 whitespace-nowrap">
                  <span v-if="item.debit && Number(item.debit) > 0" class="text-rose-600 font-semibold">
                    ₦{{ Number(item.debit).toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                  </span>
                  <span v-else class="text-gray-300">---</span>
                </td>

                <td class="py-3 px-3 align-middle text-right font-mono text-gray-800 whitespace-nowrap">
                  <span v-if="item.credit && Number(item.credit) > 0" class="text-emerald-600 font-semibold">
                    ₦{{ Number(item.credit).toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                  </span>
                  <span v-else class="text-gray-300">---</span>
                </td>

                <td class="py-3 px-3 align-middle text-right font-mono font-bold text-gray-900 whitespace-nowrap">
                  ₦{{ Number(item.balance || 0).toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                </td>

                <td class="py-3 px-3 align-middle text-center whitespace-nowrap">
                  <span :class="[
                    'inline-block px-3 py-1 rounded-full text-xs font-semibold capitalize',
                    item.status === 'retired' 
                      ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' 
                      : item.status === 'processing' 
                        ? 'bg-amber-50 text-amber-800 border border-amber-200' 
                        : 'bg-rose-50 text-rose-800 border border-rose-200'
                  ]">
                    {{ item.status || 'not retired' }}
                  </span>
                </td>

                <td class="py-3 pr-4 align-middle text-center">
                  <button @click.stop="viewLedger(item)" 
                    class="p-1.5 text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                    title="View Ledger Details">
                    <Icon icon="ph:eye" width="18" />
                  </button>
                </td>
              </tr>
            </tbody>

            <!-- EMPTY STATE -->
            <tbody v-else>
              <tr>
                <td colspan="9" class="py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                      <Icon icon="icon-park-outline:bookshelf" width="24" />
                    </div>
                    <p class="text-gray-700 font-semibold text-sm">No Ledger Records Found</p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm">No ledger entries match the selected status filter or search query.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- FLOATING BULK ACTIONS BAR -->
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 p-2.5 px-4 rounded-2xl flex items-center bg-[#052B0A] shadow-xl text-white gap-4 z-40"
          v-if="checkedCount > 0">
          <div class="flex items-center gap-2 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>{{ checkedCount }} entry(s) selected</span>
          </div>
          <button @click="generateReportFunction('all')" 
            class="bg-white text-[#052B0A] text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 hover:bg-gray-100 transition shadow-xs cursor-pointer">
            <span>Export Report</span>
            <Icon icon="mdi:file-text-outline" width="16" />
          </button>
          <button class="text-white/70 hover:text-white transition cursor-pointer p-1" @click="removeProfile">
            <Icon icon="mdi:close" width="18" />
          </button>
        </div>
      </div>

      <!-- PAGINATION -->
      <div class="mt-5" v-if="totalItems > itemsPerPage">
        <pagination class="my-4"
          :totalItems="totalItems"
          :itemsPerPage="itemsPerPage"
          v-model="currentPage"
          @setPage="(page) => currentPage = page"
        />
      </div>
    </section>

    <!-- MODALS -->
    <NewLedgerEntry v-if="newLedgerEntryModal" @cancelled="closeModal()" @confirmed="ConfirmedEdit" />
    <GeneralViewLedger :ledger="selectedLedger" @editLedgerEntry="editLedgerEntry" v-if="viewLedgerEntryModal"
      @cancelled="closeModal()" />
    <EditLedger v-if="is_edit_ledger" @confirmed="ConfirmedEdit" @cancelled="is_edit_ledger = false"
      :ledger="editLedgerValues" />
  </section>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import axios from "axios";

import NewLedgerEntry from "../../components/Modals/Ledger/NewLedgerEntry.vue";
import GeneralViewLedger from "../../components/Modals/Ledger/GeneralViewLedger.vue";
import GenerateReportButton from "../../components/GenerateReport.vue";
import LedgerImg from "../../assets/images/ledger.svg";
import EditLedger from "../../components/Modals/Ledger/EditLedger.vue";
import pagination from "../../reusables/pagination.vue";
import { usePermissions } from "@/composables/usePermissions";

const { can, hasRole } = usePermissions();

const searchValue = ref("");
const currentPage = ref(1);
const newLedgerEntryModal = ref(false);
const viewLedgerEntryModal = ref(false);
const itemsPerPage = ref(10);
const ledgers = ref([]);
const totalItems = ref(0);
const selectedLedger = ref(null);
const is_edit_ledger = ref(false);
const editLedgerValues = ref(null);
const selectedStatus = ref("all");

const filterTabs = [
  { label: "All Entries", value: "all" },
  { label: "Active Advances (Unretired)", value: "not retired" },
  { label: "Retired Advances", value: "retired" },
  { label: "Processing Clearance", value: "processing" },
];

const getLedgers = async () => {
  try {
    const res = await axios.get('/finance-and-accounts/get-ledgers', { 
      params: { 
        search: searchValue.value, 
        page: currentPage.value 
      } 
    });
    ledgers.value = res.data.data || [];
    currentPage.value = res.data.current_page || 1;
    itemsPerPage.value = res.data.per_page || 10;
    totalItems.value = res.data.total || 0;
    checkedItems.value = new Array(ledgers.value.length).fill(false);
    selectAll.value = false;
  } catch (e) {
    console.error("Failed to load ledgers", e);
  }
};

const filteredLedgers = computed(() => {
  if (selectedStatus.value === "all") return ledgers.value;
  return ledgers.value.filter(item => (item.status || "not retired").toLowerCase() === selectedStatus.value);
});

const checkedItems = ref([]);
const selectAll = ref(false);

const toggleAll = () => {
  const newState = selectAll.value;
  checkedItems.value = new Array(filteredLedgers.value.length).fill(newState);
};

const checkedCount = computed(() => {
  return checkedItems.value.filter(Boolean).length;
});

const removeProfile = () => {
  checkedItems.value = new Array(filteredLedgers.value.length).fill(false);
  selectAll.value = false;
};

const ConfirmedEdit = () => {
  is_edit_ledger.value = false;
  newLedgerEntryModal.value = false;
  getLedgers();
};

const editLedgerEntry = (ledger) => {
  editLedgerValues.value = ledger;
  is_edit_ledger.value = true;
  closeModal();
};

const viewLedger = (ledger) => {
  viewLedgerEntryModal.value = true;
  selectedLedger.value = ledger;
};

const generateReportFunction = (option) => {
  const filter = typeof option === 'string' ? option : 'all';
  window.open(`/finance-and-accounts/download-report?report=${encodeURIComponent(filter)}`, '_blank');
};

const closeModal = () => {
  newLedgerEntryModal.value = false;
  viewLedgerEntryModal.value = false;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  const d = new Date(dateStr);
  return isNaN(d) ? dateStr : d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

watch(currentPage, () => {
  getLedgers();
});

watch(searchValue, () => {
  currentPage.value = 1;
  getLedgers();
});

onMounted(() => {
  getLedgers();
});
</script>

