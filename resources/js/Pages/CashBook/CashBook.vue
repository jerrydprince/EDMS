<template>
  <main>
    <!-- HEADER STARTS -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-y-3 pr-12 pt-6 pl-5">
      <div class="flex items-center h-auto -ml-5 md:ml-0">
        <img :src="CashBookImg" alt="ledger icon" />
        <div class="-mt-2">
          <p class="text-primary text-2xl md:text-3xl mb-1">Cash Book</p>
        </div>
      </div>
      <div class="flex items-center gap-x-3 -mt-4">
        <GenerateCashbookReport :vote_book_id="selectedAccount.id" @select="generateReportFunction" />
        <button @click="newCashbookEntryModal = true"
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3 md:px-4 text-sm md:text-base">
          <Icon icon="ic:outline-plus" width="20" />
          <p>Add Entry</p>
        </button>
      </div>
    </div>
    <!-- HEADER ENDS -->

    <!-- BODY STARTS -->
    <section class="mb-14">
      <section class="px-4 md:px-8 lg:px-12 pt-6 mb-5">
        <div class="grid grid-cols-2 md:grid-cols-3 items-center gap-4 w-full relative" >
          <div v-for="(account, index) in votebooks.standard" :key="index"
            class="bg-[#F9FAFB] px-4 py-3 pb-6 rounded-2xl h-full w-full cursor-pointer" :class="selectedAccount === account
                ? 'border-[1.5px] border-[#12B76A] drop-shadow-md'
                : ''
              " @click="selectAccount(account)">
            <div class="">
              <!-- Checkbox here -->
              <div class="">
                <div class="">
                  <Checkbox class="h-5 w-5" :model-value="selectedAccount === account"
                    @update:modelValue="(val) => val && selectAccount(account)" />
                </div>
                <section class="flex items-center justify-between mt-12">
                  <div>
                    <div v-if="account?.hasBreakdown" class="">
                      <p class="mb-1 font-medium text-xs md:text-sm">
                        {{ account.name }}
                      </p>
                      <div>
                        <p v-if="selectedSpecialAccount?.account_type" class="text-primary text-xs md:text-sm">
                          {{ selectedSpecialAccount?.account_type }} Account -
                          <span class="text-xs text-gray-500">currently viewing</span>
                        </p>
                        <p v-else class="text-xs font-light text-gray-600">
                          Select to view breakdown
                        </p>
                      </div>
                    </div>
                    <div v-else class="">
                      <p class="mb-1 font-medium text-xs md:text-sm">
                        {{ account.name }}
                      </p>
                      <p class="text-xs font-light text-gray-600">
                        {{ account.account_number }} - GIFMIS
                      </p>
                    </div>
                  </div>
                  <div v-if="account?.hasBreakdown" class="bg-white rounded-full p-[6px] drop-shadow-lg">
                    <Icon v-if="isOpen" icon="mdi:chevron-up" width="16" />
                    <Icon v-else icon="mdi:chevron-down" width="16" />
                  </div>
                </section>
              </div>
            </div>
          </div>
          <!-- BREAKDOWN SPECIAL ACCOUNT SECTION START -->
          <div v-if="isOpen"
            class="absolute bottom-0 left-0 md:left-auto md:right-2 top-[22rem] md:top-40 z-10 rounded-2xl bg-gray-100 shadow-lg overflow-y-scroll w-[95%] md:w-[50%] lg:w-[31%] h-48 md:h-56 p-4 md:p-7 flex flex-col gap-y-6">
            <div v-for="(special, index) in accountList[2].subAccounts" :key="index"
              @click="selectSpecialAccount(special)" class="flex items-center justify-between cursor-pointer">
              <div class="flex items-center gap-6">
                <p class="font-[450] text-sm md:text-base">
                  {{ special.accountType }}
                </p>
                <p class="text-gray-600 text-xs">
                  {{ special.accountNumber }} <span class="mx-1"> - </span>
                  {{ special.platform }}
                </p>
              </div>
              <Icon icon="ic:chevron-right" width="18" />
            </div>
          </div>
          <!-- BREAKDOWN SPECIAL ACCOUNT SECTION END -->
        </div>
      </section>
      <hr />
      <section class="px-4 md:px-8 lg:px-12">
        <section class="flex items-center justify-end pt-6">
          <div class="flex items-center border p-2 rounded-lg gap-2 text-sm lg:mt-0 shadow-sm">
            <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
            <input type="text" v-model="searchValue" placeholder="Search Records"
              class="w-64 md:w-96 h-5 md:h-7 border-0 outline-none text-[#98A2B3]" />
          </div>
          <div class="ml-4 md:ml-12 flex items-center gap-2 md:gap-4">
            <Icon @click="scrollLeft" icon="mdi:chevron-left" width="20" class="cursor-pointer" />
            <Icon @click="scrollRight" icon="mdi:chevron-right" width="20" class="cursor-pointer" />
          </div>
        </section>
        <div class="mt-7 rounded-xl">
          <div ref="scrollContainer" class="overflow-x-scroll">
            <table class="rounded-lg min-w-max w-full border border-gray-200 text-gray-500 text-sm">
              <thead class="bg-gray-50">
                <tr class="text-left">
                  <th class="py-3 pl-5">
                    <Checkbox :model-value="allSelected" @update:modelValue="toggleAll" class="h-5 w-5" />
                  </th>
                  <th class="font-medium">Date</th>
                  <th class="font-medium">Voucher No</th>
                  <th class="font-medium">From</th>
                  <th class="font-medium">To</th>
                  <th class="font-medium">NCOA</th>
                  <th class="font-medium">Treasury Receipt</th>
                  <th class="font-medium">TSA/Bank</th>
                  <th class="font-medium">DVBN</th>
                  <th class="font-medium">Receipt Description</th>
                  <th class="font-medium">Payment Description</th>
                  <th class="font-medium">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in cashbooks" :key="index"
                  class="hover:bg-gray-50 cursor-pointer border-b border-gray-200 h-20 text-sm text-gray-900">
                  <td class="align-middle pl-5">
                    <div class="flex items-center gap-2">
                      <Checkbox :model-value="checkedItems[index]" @update:model-value="toggleItem(index)"
                        class="h-5 w-5" />
                    </div>
                  </td>

                  <td class="align-middle">
                    <p class="font-medium">
                      {{ item.date }}
                    </p>
                  </td>

                  <td class="align-middle">
                    <p>{{ item.voucher_no }}</p>
                  </td>
                  <td class="align-middle">
                    <p>{{ item.from }}</p>
                  </td>
                  <td class="align-middle">
                    <p>{{ item.to }}</p>
                  </td>
                  <td class="align-middle">
                    <p>{{ item.ncoa_code }}</p>
                  </td>
                  <td class="align-middle">
                    <p>{{ item.treasury_receipt }}</p>
                  </td>
                  <td class="align-middle">
                    <p>{{ item.tsa_bank }}</p>
                  </td>
                  <td class="align-middle">
                    <p>{{ item.dvbn }}</p>
                  </td>
                  <td class="align-middle">
                    <p class="truncate">{{ item.receipt_description }}</p>
                  </td>
                  <td class="align-middle">
                    <p class="truncate">{{ item.payment_description }}</p>
                  </td>
                  <td class="align-middle">
                    <div class="flex">
                      <div @click="viewCashbookEntryModal = true" class="border border-gray-300 rounded-lg p-2">
                        <Icon icon="ph:eye" width="20" />
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <pagination class="my-4"
            :totalItems="totalItems"
            :itemsPerPage="itemsPerPage"
            v-model="currentPage"
            @setPage="(page) => currentPage = page"
          />
          </div>
        </div>
      </section>
    </section>
    <CreateCashBookEntry :selectedAccount="selectedAccount" v-if="newCashbookEntryModal && selectedAccount" @cancelled="closeModal()" />
    <ViewCashBookEntry v-if="viewCashbookEntryModal" @cancelled="closeModal()" />
  </main>
</template>

<script setup>
import { ref, computed, watch, defineProps } from "vue";

import CreateCashBookEntry from "../../components/Modals/CashBook/CreateCashBookEntry.vue";
import ViewCashBookEntry from "../../components/Modals/CashBook/ViewCashBookEntry.vue";
import GenerateCashbookReport from "../../components/GenerateCashbookReport.vue";
import CashBookImg from "../../assets/images/cashbook.svg";
import pagination from "../../reusables/pagination.vue";

const newCashbookEntryModal = ref(false);
const viewCashbookEntryModal = ref(false);
const isOpen = ref(false);
const scrollContainer = ref(null);
const props = defineProps(['votebooks'])
const cashbooks = ref([])
const totalItems = ref(0)
const itemsPerPage = ref(10)
const currentPage = ref(1)

const scrollLeft = () => {
  scrollContainer.value?.scrollBy({ left: -200, behavior: "smooth" });
};

const scrollRight = () => {
  scrollContainer.value?.scrollBy({ left: 200, behavior: "smooth" });
};

// TABLE DATA START
const searchValue = ref("");

const items = ref([
  {
    date: "15-01-2025, 09:00 AM",
    voucherNo: "REF001",
    fromWhomReceived: "Adedayo Adebayo",
    whomToPay: "Ayodeji Alabi",
    ncoa: "23020101",
    noOfTreasuryReceipt: "17",
    tsaBank: "300,000",
    dvbN: "FGN/2024/00233",
    descriptionOfReceipt: "Payment for Stationery",
    descriptionOfPayments: "Maintenance of General...",
  },
  {
    date: "12-01-2025, 09:00 AM",
    voucherNo: "REF002",
    fromWhomReceived: "Ayodeji Alabi",
    whomToPay: "Babatunde Oduyemi",
    ncoa: "23020101",
    noOfTreasuryReceipt: "20",
    tsaBank: "67,000",
    dvbN: "FGN/2024/00233",
    descriptionOfReceipt: "Payment for Stationery",
    descriptionOfPayments: "Maintenance of General...",
  },
  {
    date: "13-01-2025, 09:00 AM",
    voucherNo: "REF004",
    fromWhomReceived: "Olamide Olowu",
    whomToPay: "Adedayo Adebayo",
    ncoa: "23020101",
    noOfTreasuryReceipt: "14",
    tsaBank: "50,000",
    dvbN: "FGN/2024/00233",
    descriptionOfReceipt: "Payment for Stationery",
    descriptionOfPayments: "Maintenance of General...",
  },
  {
    date: "12-01-2025, 08:00 PM",
    voucherNo: "REF003",
    fromWhomReceived: "Babatunde Oduyemi",
    whomToPay: "Tolulope Adefemi",
    ncoa: "23020101",
    noOfTreasuryReceipt: "24",
    tsaBank: "300,000",
    dvbN: "FGN/2024/00233",
    descriptionOfReceipt: "Payment for Stationery",
    descriptionOfPayments: "Maintenance of General...",
  },
  {
    date: "12-01-2025, 08:00 PM",
    voucherNo: "REF007",
    fromWhomReceived: "Abiola Ajibade",
    whomToPay: "Abiola Ajibade",
    ncoa: "23020101",
    noOfTreasuryReceipt: "14",
    tsaBank: "50,000",
    dvbN: "FGN/2024/00233",
    descriptionOfReceipt: "Payment for Stationery",
    descriptionOfPayments: "Maintenance of General...",
  },
  {
    date: "13-01-2025, 09:00 AM",
    voucherNo: "REF006",
    fromWhomReceived: "Kehinde Bankole",
    whomToPay: "Kehinde Bankole",
    ncoa: "23020101",
    noOfTreasuryReceipt: "02",
    tsaBank: "25,000",
    dvbN: "FGN/2024/00233",
    descriptionOfReceipt: "Payment for Stationery",
    descriptionOfPayments: "Maintenance of General...",
  },
  {
    date: "12-01-2025, 08:00 PM",
    voucherNo: "REF004",
    fromWhomReceived: "Tolulope Adefemi",
    whomToPay: "Olamide Olowu",
    ncoa: "23020101",
    noOfTreasuryReceipt: "01",
    tsaBank: "25,000",
    dvbN: "FGN/2024/00233",
    descriptionOfReceipt: "Payment for Stationery",
    descriptionOfPayments: "Maintenance of General...",
  },
]);

const displayedItems = computed(() => {
  return items.value;
});

const checkedItems = ref([]);

watch(
  displayedItems,
  (newVal) => {
    checkedItems.value = newVal.map(() => false);
  },
  { immediate: true }
);

const toggleItem = (index) => {
  checkedItems.value[index] = !checkedItems.value[index];
};

const allSelected = computed(
  () =>
    checkedItems.value.length > 0 && checkedItems.value.every((item) => item)
);

const toggleAll = () => {
  const shouldSelectAll = !allSelected.value;
  checkedItems.value = displayedItems.value.map(() => shouldSelectAll);
};

// TABLE DATA ENDS HERE

// ACCOUNTS START HERE
const selectedAccount = ref(null);
const selectedSpecialAccount = ref(null);

const selectAccount = (account) => {
  if (account.hasBreakdown) {
    isOpen.value = !isOpen.value;
  } else {
    selectedSpecialAccount.value = null;
  }
  selectedAccount.value = account;
};

const selectSpecialAccount = (account) => {
  selectedSpecialAccount.value = account;
  isOpen.value = false;
};

const accountList = ref([
  {
    accountType: "Capital Account",
    accountNumber: "2205338523",
    platform: "GIFMIS",
    balance: "2.54B",
  },
  {
    accountType: "Overhead/Re-occurring Account",
    accountNumber: "4205338594",
    platform: "GIFMIS",
    balance: "1.91T",
  },
  {
    accountType: "Special Account",
    hasBreakdown: true,
    subAccounts: [
      {
        accountType: "Payroll",
        accountNumber: "2205338523",
        platform: "GIFMIS",
      },
      {
        accountType: "Logistics",
        accountNumber: "2205338523",
        platform: "GIFMIS",
      },
      {
        accountType: "Contingency",
        accountNumber: "2205338523",
        platform: "GIFMIS",
      },
      {
        accountType: "Medical Expenses",
        accountNumber: "2205338523",
        platform: "GIFMIS",
      },
    ],
  },
]);

const getCashbooks = ()=>{
  if(selectedAccount.value){
    axios.get('/finance-and-accounts/get-cashbooks', {params:{vote_book_id: selectedAccount.value.id, search: searchValue.value, page: currentPage.value}}).then((response)=>{
      cashbooks.value = response.data.data
      currentPage.value = response.data.current_page
      itemsPerPage.value = response.data.per_page
      totalItems.value = response.data.total
    })
  }
}

watch(searchValue, () => {
  if (searchValue.value.trim()) {
    clearTimeout(window.__searchDebounce__);
    window.__searchDebounce__ = setTimeout(() => {
      getCashbooks();
    }, 400);
  } else {
    getCashbooks();
  }
  
});


// Generate report logic goes here
const generateReportFunction = (option) => {
  console.log(option);
};

watch(()=>props.votebooks, (newVal)=>{
  if(newVal && newVal.standard){
    selectedAccount.value = newVal?.standard[0]
  }
},{immediate: true})

watch(selectedAccount, ()=>{
  getCashbooks()
}, {immediate: true})

const closeModal = () => {
  newCashbookEntryModal.value = false;
  viewCashbookEntryModal.value = false;
};
</script>
