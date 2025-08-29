<template>
  <section>
    <!-- THE HEADER SECTION STARTS -->
    <section class="flex flex-col lg:flex-row">
      <!-- VOTE BOOK SUMMARY START -->
      <div class="relative flex-[1.2] w-full lg:border-r border-gray-200 p-4 md:p-9">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
          <div class="flex items-center mb-2 md:mb-0">
            <img :src="VoteBookImg" alt="votebook icon" width="70" height="70" class="-ml-4 md:ml-0" />
            <p class="text-2xl text-gray-800 -mt-2">Vote Book</p>
          </div>
          <div class="flex items-center gap-x-3 -mt-3">
            <GenerateReportButton size="md" @select="generateReportFunction" />
            <button @click="addNewVoteModal = true"
              class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3 md:px-4 text-sm md:text-base">
              <Icon icon="ic:outline-plus" width="20" />
              <p>Add Vote</p>
            </button>
          </div>
        </div>
        <!-- standard account -->
        <div class="mt-8 md:mt-4 max-h-[300px] overflow-y-auto hide_scroll">
          <div v-for="(account, index) in standardAccounts" :key="account.account_number"
            @click="openAccountBreakdown(account)" class="mb-3 bg-[#F9FAFB] px-3 md:px-6 py-3 rounded-xl" :class="checkedVoteBook.includes(account.id)
                ? 'border-[1.5px] border-[#12B76A] drop-shadow-md'
                : ''
              ">
            <div class="flex items-center">
              <!-- Checkbox here -->
              <div class="flex flex-[1.7] items-center gap-3">
                <div class="mr-4">
                  <div v-if="account.hasBreakdown" class="text-gray-300 cursor-pointer">
                    <Icon v-if="isOpen" icon="mdi:chevron-up" width="25" />
                    <Icon v-else icon="mdi:chevron-down" width="25" />
                  </div>
                  <Checkbox  v-else class="h-5 w-5" :inputId="account.id" :value="account.id" v-model="checkedVoteBook"
                   />
                </div>
                <div>
                  <p class="mb-1 font-medium">{{ account.name }}</p>
                  <p v-if="!account.hasBreakdown" class="text-sm font-light text-gray-600">
                    {{ account.account_number }} - {{ account.platform }}
                  </p>
                  <p v-else class="text-sm font-light text-gray-600">
                    Select to view breakdown
                  </p>
                </div>
              </div>
              <div class="flex-[0.3]">
                <p class="text-gray-600 text-xs font-extralight mb-1">
                  Balance
                </p>
                <p class="font-semibold">
                  ₦{{ formatAmount(account.balance) }}
                </p>
              </div>
            </div>
          </div>
          <div v-if="isOpen"
            class="absolute left-5 md:left-10 z-10 w-[90%] rounded-xl bg-white p-3 shadow-lg border h-64 overflow-y-scroll hide_scroll">
            <div v-for="(account, index) in specialAccounts" :key="index" @click="openAccountBreakdown(account)"
              class="mb-3 hover:bg-[#F9FAFB] px-3 md:px-6 py-3 rounded-xl" :class="selectedVoteBookAccount === account
                  ? 'border-[1.5px] border-[#12B76A] drop-shadow-md'
                  : ''
                ">
              <div class="flex items-center">
                <!-- Checkbox here -->
                <div class="flex flex-[1.7] items-center gap-3">
                  <div class="mr-4">
                    <Checkbox class="h-5 w-5" :model-value="selectedVoteBookAccount === account" @update:modelValue="
                      (val) => val && openAccountBreakdown(account)
                    " />
                  </div>
                  <div>
                    <p class="mb-1 font-medium">{{ account.name }}</p>
                    <p class="text-sm font-light text-gray-600">
                      {{ account.account_number }} - {{ account.platform }}
                    </p>
                  </div>
                </div>
                <div class="flex-[0.3]">
                  <p class="text-gray-600 text-xs font-extralight mb-1">
                    Balance
                  </p>
                  <p class="font-semibold">
                    ₦{{ formatAmount(account.balance) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- VOTE BOOK SUMMARY END -->

      <!-- VOTE BOOK GRAPH START -->
      <div class="flex-[0.8] w-full p-5 pt-10 border-t lg:border-none">
        <div class="flex items-center justify-between border-b-2 border-dashed border-gray-200 pb-5 mb-5">
          <p class="text-gray-800 text-2xl md:text-3xl font-semibold">
            {{ selectedVoteBook?.name }}
          </p>
          <div class="flex items-center gap-x-3">
            <button @click="topUpAccountModal = true"
              class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border-2 rounded-lg py-3 px-3 md:px-4">
              <Icon icon="ic:round-plus" width="20" />
            </button>
            <button @click="editAccount"
              class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border-2 rounded-lg py-3 px-3 md:px-4">
              <Icon icon="lucide:edit-3" width="20" />
            </button>
          </div>
        </div>
        <div>
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm font-light text-gray-600 mb-1">Balance</p>
              <p class="text-xl leading-7">
                ₦<span class="font-medium">{{
                  formatWithCommas(selectedVoteBook?.balance)
                  }}</span>
              </p>
            </div>
            <div class="text-gray-500 flex items-center cursor-pointer">
              <p class="text-xs">View Transactions</p>
              <Icon icon="ic:chevron-right" width="15" />
            </div>
          </div>
          <div>
            <!-- Graph Goes Here -->
            <VotebookLineChart />
          </div>
        </div>
      </div>
      <!-- VOTE BOOK GRAPH END -->
    </section>
    <hr />
    <!-- THE HEADER SECTION ENDS -->
    <RouterView />
    <!-- Modals Start -->
    <AddNewVote  @refreshVoteBook="refreshVoteBook"  v-if="addNewVoteModal" @cancelled="closeModal()" />
    <editVoteBook  @refreshVoteBook="refreshVoteBook" @cancelled="closeModal()" :selectedVoteBook="selectedVoteBook" v-if="isEditVoteBook" />
    <TopUpAccount @refreshVoteBook="refreshVoteBook" @account="makeAccountSelected" v-if="topUpAccountModal" :accountSelected="selectedVoteBook" @cancelled="closeModal()" />
    <!-- Modals End -->
  </section>
</template>

<script setup>
import { ref, defineProps, watch, onMounted } from "vue";
import AddNewVote from "../../components/Modals/VoteBook/AddNewVote.vue";
import TopUpAccount from "../../components/Modals/VoteBook/TopUpAccount.vue";
import GenerateReportButton from "../../components/GenerateReport.vue";
import VotebookLineChart from "../../components/VotebookLineChart.vue";
import VoteBookImg from "../../assets/images/votebook.svg";
import { router } from "@inertiajs/vue3";
import editVoteBook from "../../components/Modals/VoteBook/editVoteBook.vue";

import { formatAmount, formatWithCommas } from "../../utils/formatAmount";

const addNewVoteModal = ref(false);
const topUpAccountModal = ref(false);
const isOpen = ref(false);
const props = defineProps(['votebooks'])
const specialAccounts = ref([])
const standardAccounts = ref([])
const selectedVoteBook = ref(null)
const checkedVoteBook = ref([])
const is_account_refreshed = ref(false)
const isEditVoteBook = ref(false)


const selectedVoteBookAccount = ref([]);

const refreshVoteBook = ()=>{
  is_account_refreshed.value = true
  router.reload({only: ['votebooks'], preserveState: true})
}

const makeAccountSelected = (account)=>{
  selectedVoteBook.value = account
  checkedVoteBook.value = [account.value.id]
  is_account_refreshed.value = true
}

const openAccountBreakdown = (account) => {
  checkedVoteBook.value = []
  checkedVoteBook.value = [account.id]
  selectedVoteBook.value = account

  if (account.hasBreakdown) {
    isOpen.value = !isOpen.value;
  } else {
    selectedVoteBookAccount.value = account;
  }
};


// Generate report logic goes here
const generateReportFunction = (option) => {
  console.log(option);
};

const closeModal = () => {
  addNewVoteModal.value = false;
  isEditVoteBook.value = false;
  topUpAccountModal.value = false;
};

const editAccount = ()=>{
  isEditVoteBook.value = true
}

watch(()=>props.votebooks, ()=>{
  specialAccounts.value = props.votebooks.filter((item)=>item.account_type === 'special');
  standardAccounts.value = props.votebooks.filter((item)=> item.account_type === 'standard');

  if(standardAccounts.value && standardAccounts.value.length > 0 && is_account_refreshed.value === false){
    selectedVoteBook.value = standardAccounts.value[0]
    checkedVoteBook.value = [selectedVoteBook.value.id]
  }else{
    if(selectedVoteBook.value){
      //checkedVoteBook.value = [selectedVoteBook.value.id]
    }
  }
}, {immediate:true})
onMounted(()=>{
  // specialAccounts.value = props.votebooks.filter((item)=>item.account_type === 'special');
  // standardAccounts.value = props.votebooks.filter((item)=> item.account_type === 'standard');

  // if(standardAccounts.value && standardAccounts.value.length > 0){
  //   selectedVoteBook.value = standardAccounts.value[0]
  //   checkedVoteBook.value = [selectedVoteBook.value.id]
  // }

  // if(selectedVoteBook.value){
  //   checkedVoteBook.value = [selectedVoteBook.value.id]
  // }
})
</script>
