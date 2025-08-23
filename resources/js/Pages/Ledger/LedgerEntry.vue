<template>
  <section>
    <!-- HEADER STARTS -->
    <div
      class="flex flex-col md:flex-row items-start md:items-center justify-between pr-9 pt-4"
    >
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
        <button
          @click="newLedgerEntryModal = true"
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3 md:px-4 text-sm md:text-base"
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
      <section class="lg:flex items-center justify-end pt-6">
        <div
          class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0 shadow-sm"
        >
          <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
          <input
            type="text"
            v-model="searchValue"
            placeholder="Search Ledger"
            class="w-96 h-7 border-0 outline-none text-[#98A2B3]"
          />
        </div>
      </section>
      <div class="mt-7 rounded-xl">
        <div class="overflow-x-scroll lg:overflow-hidden">
          <table
            class="rounded-lg min-w-max w-full border border-gray-200 text-gray-500 text-sm"
          >
            <thead class="bg-gray-50">
              <tr class="text-left">
                <th class="py-3 pl-5">
                  <Checkbox
                    v-model="selectAll"
                    @update:modelValue="toggleAll"
                    class="h-5 w-5"
                  />
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
              <tr
                v-for="(item, index) in displayedItems"
                :key="index"
                class="hover:bg-gray-50 cursor-pointer border-b border-gray-200 h-20 text-sm"
              >
                <td class="align-middle pl-5">
                  <div class="flex items-center gap-2">
                    <Checkbox v-model="checkedItems[index]" class="h-5 w-5" />
                  </div>
                </td>

                <td class="align-middle">
                  <p class="text-gray-900">
                    {{ item.date }}
                  </p>
                </td>

                <td class="align-middle">
                  <p>{{ item.reference }}</p>
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
                  <p
                    class="bg-gray-500 p-2 text-center rounded-full w-28"
                    :style="{
                      color:
                        item.status == 'Retired'
                          ? '#0B5C16'
                          : item.status == 'Processing'
                          ? '#F67E5A'
                          : item.status == 'Not Retired'
                          ? '#1D2939'
                          : 'black',
                      backgroundColor:
                        item.status == 'Retired'
                          ? '#EFF6F0'
                          : item.status == 'Processing'
                          ? '#FEF0EC'
                          : item.status == 'Not Retired'
                          ? '#F2F4F7'
                          : '#F2F4F7',
                    }"
                  >
                    {{ item.status }}
                  </p>
                </td>
                <td class="align-middle">
                  <div class="flex">
                    <div
                      @click="viewLedgerEntryModal = true"
                      class="border border-gray-300 rounded-lg p-2"
                    >
                      <Icon icon="ph:eye" width="20" />
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div
          class="absolute bottom-[5%] p-2 rounded-xl flex items-center left-[45%] bg-[#052B0A] justify-between"
          v-if="checkedCount"
        >
          <div class="flex items-center gap-2 text-white text-sm ml-3">
            <p class=" ">
              {{ checkedCount }}
              selected
            </p>
          </div>
          <button
            @click=""
            class="ml-16 bg-white text-[#052B0A] text-xs px-4 py-3 rounded-md flex items-center gap-2"
          >
            <p>Generate Report</p>
            <Icon icon="mdi:file-text-outline" width="18" />
          </button>
          <button class="cursor-pointer text-white px-3" @click="removeProfile">
            <Icon icon="mdi:times" />
          </button>
        </div>
      </div>
      <div class="mt-5">
        <!-- <paginator
          :totalItems="totalItems"
          :itemsPerPage="itemsPerPage"
          v-model="currentPage"
        /> -->
      </div>
    </section>
    <NewLedgerEntry v-if="newLedgerEntryModal" @cancelled="closeModal()" />
    <ViewLedgerEntry v-if="viewLedgerEntryModal" @cancelled="closeModal()" />
    <!-- BODY ENDS -->
  </section>
</template>
<script setup>
import { ref, watch, computed } from "vue";

import NewLedgerEntry from "../../components/Modals/Ledger/NewLedgerEntry.vue";
import ViewLedgerEntry from "../../components/Modals/Ledger/ViewLedgerEntry.vue";
//import paginator from "../../reusables/paginations.vue";
import GenerateReportButton from "../../components/GenerateReport.vue";
import LedgerImg from "../../assets/images/ledger.svg";

const searchValue = ref("");
const currentPage = ref(1);
const newLedgerEntryModal = ref(false);
const viewLedgerEntryModal = ref(false);
const itemsPerPage = 10;

const items = ref([
  {
    date: "15-01-2025, 09:00 AM",
    reference: "REF001",
    narration: "Payment for office",
    debit: 50000,
    credit: null,
    balance: 300000,
    status: "Retired",
  },
  {
    date: "12-01-2025, 09:00 AM",
    reference: "REF002",
    narration: "Service fees for ...",
    debit: 25000,
    credit: 120000,
    balance: 67000,
    status: "Not Retired",
  },
  {
    date: "13-01-2025, 09:00 AM",
    reference: "REF004",
    narration: "Client payment...",
    debit: 300000,
    credit: null,
    balance: 50000,
    status: "Not Retired",
  },
  {
    date: "12-01-2025, 08:00 PM",
    reference: "REF003",
    narration: "Refund for cancellation",
    debit: 25000,
    credit: 25000,
    balance: 300000,
    status: "Processing",
  },
  {
    date: "12-01-2025, 08:00 PM",
    reference: "REF007",
    narration: "Client payment...",
    debit: 300000,
    credit: null,
    balance: 50000,
    status: "Retired",
  },
  {
    date: "13-01-2025, 09:00 AM",
    reference: "REF006",
    narration: "Refund for cancellation",
    debit: 50000,
    credit: 50000,
    balance: 25000,
    status: "Not Retired",
  },
  {
    date: "12-01-2025, 08:00 PM",
    reference: "REF004",
    narration: "Client payment...",
    debit: 25000,
    credit: null,
    balance: 25000,
    status: "Processing",
  },
  {
    date: "13-01-2025, 09:00 AM",
    reference: "REF001",
    narration: "Client payment...",
    debit: 67000,
    credit: 67000,
    balance: 25000,
    status: "Processing",
  },
  {
    date: "12-01-2025, 08:00 PM",
    reference: "REF001",
    narration: "Monthly subscription",
    debit: 50000,
    credit: null,
    balance: 50000,
    status: "Retired",
  },
]);

const pageStart = ref(1);
const pageEnd = ref(1);
const displayedItems = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;

  pageStart.value = startIndex;
  pageEnd.value = endIndex;

  return items.value.slice(startIndex, endIndex);
});
const totalItems = computed(() => items.value.length);

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
</script>
