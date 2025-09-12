<template>
  <section>
    <!-- HEADER STARTS -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between pr-9 pt-4">
      <div class="flex items-center h-auto">
        <img :src="LedgerImg" alt="ledger icon" />
        <div class="-mt-5">
          <p class="text-gray-800 text-2xl md:text-3xl mb-1">Ledger</p>
          <p class="text-gray-400 text-xs md:text-sm font-extralight">
            View, Track & Manage your financial records quickly
          </p>
        </div>
      </div>
      <div class="flex items-center gap-x-3 -mt-4 ml-6 md:ml-0">
        <GenerateReportButton @select="generateReportFunction" />
        <button @click="newLedgerEntryModal = true"
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3 md:px-4 text-sm md:text-base">
          <Icon icon="ic:outline-plus" width="20" />
          <p>Create Entry</p>
        </button>
      </div>
    </div>
    <!-- HEADER ENDS -->

    <hr class="hidden md:block" />

    <!-- BODY STARTS -->
    <section class="px-9 pb-10">
      <section class="lg:flex items-center justify-end pt-6">
        <div class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0 shadow-sm">
          <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
          <input type="text" v-model="searchValue" placeholder="Search Ledger"
            class="w-96 h-7 border-0 outline-none text-[#98A2B3]" />
        </div>
      </section>
      <div class="mt-7 rounded-xl">
        <div class="overflow-x-scroll lg:overflow-hidden">
          <table class="rounded-lg min-w-max w-full border border-gray-200 text-gray-500 text-sm">
            <thead class="bg-gray-50">
              <tr class="text-left">
                <th class="py-3 pl-5">
                  <Checkbox v-model="selectAll" @update:modelValue="toggleAll" class="h-5 w-5" />
                </th>
                <th class="font-medium">Date</th>
                <th class="font-medium">Reference</th>
                <th class="font-medium">Narration</th>
                <th class="font-medium">Debit (DR)</th>
                <th class="font-medium">Credit (CR)</th>
                <th class="font-medium">Balance</th>
                <th class="font-medium">Status</th>
                <th class="font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in ledgers" :key="index"
                class="hover:bg-gray-50 cursor-pointer border-b border-gray-200 h-20 text-sm">
                <td class="align-middle pl-5">
                  <div class="flex items-center gap-2">
                    <Checkbox v-model="checkedItems[index]" class="h-5 w-5" />
                  </div>
                </td>

                <td class="align-middle">
                  <p class="text-gray-900">
                    {{ item.due_date }}
                  </p>
                </td>

                <td class="align-middle">
                  <p>{{ item.ref_no }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.narration }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.debit || "---" }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.credit || "---" }}</p>
                </td>
                <td class="align-middle">
                  <p>{{ item.balance }}</p>
                </td>
                <td class="align-middle">
                  <p class="bg-gray-500 p-2 text-center rounded-full w-28" :style="{
                    color:
                      item.status == 'retired'
                        ? '#0B5C16'
                        : item.status == 'processing'
                          ? '#F67E5A'
                          : item.status == 'not retired'
                            ? '#1D2939'
                            : 'black',
                    backgroundColor:
                      item.status == 'retired'
                        ? '#EFF6F0'
                        : item.status == 'processing'
                          ? '#FEF0EC'
                          : item.status == 'not retired'
                            ? '#F2F4F7'
                            : '#F2F4F7',
                  }">
                    {{ item.status }}
                  </p>
                </td>
                <td class="align-middle">
                  <div class="flex">
                    <div @click="viewLedger(item)" class="border border-gray-300 rounded-lg p-2">
                      <Icon icon="ph:eye" width="20" />
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="absolute bottom-[5%] p-2 rounded-xl flex items-center left-[45%] bg-[#052B0A] justify-between"
          v-if="checkedCount">
          <div class="flex items-center gap-2 text-white text-sm ml-3">
            <p class=" ">
              {{ checkedCount }}
              selected
            </p>
          </div>
          <button @click="" class="ml-16 bg-white text-[#052B0A] text-xs px-4 py-3 rounded-md flex items-center gap-2">
            <p>Generate Report</p>
            <Icon icon="mdi:file-text-outline" width="18" />
          </button>
          <button class="cursor-pointer text-white px-3" @click="removeProfile">
            <Icon icon="mdi:times" />
          </button>
        </div>
      </div>
      <div class="mt-5">
         <pagination class="my-4"
        :totalItems="totalItems"
        :itemsPerPage="itemsPerPage"
        v-model="currentPage"
        @setPage="(page) => currentPage = page"
      />
      </div>
    </section>
    <NewLedgerEntry v-if="newLedgerEntryModal" @cancelled="closeModal()" @confirmed="ConfirmedEdit" />
    <GeneralViewLedger :ledger="selectedLedger" @editLedgerEntry="editLedgerEntry" v-if="viewLedgerEntryModal"
      @cancelled="closeModal()" />
    <EditLedger v-if="is_edit_ledger" @confirmed="ConfirmedEdit" @cancelled="is_edit_ledger = false"
      :ledger="editLedgerValues" />
    <!-- BODY ENDS -->
  </section>

</template>
<script setup>
import { ref, watch, computed, onMounted } from "vue";

import NewLedgerEntry from "../../components/Modals/Ledger/NewLedgerEntry.vue";
//import ViewLedgerEntry from "../../components/Modals/Ledger/ViewLedgerEntry.vue";
import GeneralViewLedger from "../../components/Modals/Ledger/GeneralViewLedger.vue";
//import paginator from "../../reusables/paginations.vue";
import GenerateReportButton from "../../components/GenerateReport.vue";
import LedgerImg from "../../assets/images/ledger.svg";
import EditLedger from "../../components/Modals/Ledger/EditLedger.vue";
import pagination from "../../reusables/pagination.vue";

const searchValue = ref("");
const currentPage = ref(1);
const newLedgerEntryModal = ref(false);
const viewLedgerEntryModal = ref(false);
const itemsPerPage = ref(10);
const ledgers = ref([])
const selectedLedger = ref(null)
const is_edit_ledger = ref(false)
const editLedgerValues = ref(null)

const getLedgers = async () => {
  await axios.get('/finance-and-accounts/get-ledgers', { params: { search: searchValue.value, page: currentPage.value } }).then((response) => {
    ledgers.value = response.data.data
    currentPage.value = response.data.current_page
        itemsPerPage.value = response.data.per_page
        totalItems.value = response.data.total
  })
}

const ConfirmedEdit = () => {
  is_edit_ledger.value = false
  newLedgerEntryModal.value = false
  getLedgers()
}

const editLedgerEntry = (ledger) => {
  editLedgerValues.value = ledger;
  is_edit_ledger.value = true;
  closeModal()
}

const viewLedger = (ledger) => {
  viewLedgerEntryModal.value = true
  selectedLedger.value = ledger
}

const pageStart = ref(1);
const pageEnd = ref(1);
const displayedItems = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;

  pageStart.value = startIndex;
  pageEnd.value = endIndex;

  return ledgers.value.slice(startIndex, endIndex);
});
const totalItems = ref(0);

const checkedItems = ref(new Array(displayedItems.value.length).fill(false));

const selectAll = ref(false);

const toggleAll = () => {
  const newState = selectAll.value;
  checkedItems.value = checkedItems.value.map(() => newState);
};

// Checked files Logic
const checkedCount = computed(() => {
  return checkedItems.value.filter((checked) => checked).length;
});

const removeProfile = () => {
  checkedItems.value = [];
};

// Generate report logic goes here
const generateReportFunction = (option) => {
  console.log(option);
};

const closeModal = () => {
  newLedgerEntryModal.value = false;
  viewLedgerEntryModal.value = false;
};

watch(currentPage, () => {
  getLedgers()
})

watch(searchValue, () => {
  getLedgers()
})

onMounted(() => {
  getLedgers()
})
</script>
