<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: {
      class: [
        'bg-transparent rounded-2xl transition-all duration-500 ease-in-out md:w-[60vw] w-[90vw] transition-all duration-500 ease-in-out',
        currentStep === 1 ? 'lg:w-[38rem]' : 'lg:w-[35rem]',
      ],
    },
    header: { class: 'hidden' },
    content: { class: 'w-full px-0 bg-gray-100 pb-2 m-auto rounded-2xl' },
    footer: { class: 'mt-4 bg-gray-100 rounded-2xl' },
  }" :style="{ padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class=".custom-dialog">
    <Transition name="fade-slide" mode="out-in">
      <!-- Step 1 - Select Vote Account -->
      <section v-if="currentStep === 1" key="step1"
        class="flex flex-col items-start max-h-[75vh] overflow-y-scroll hide_scroll">
        <section class="w-[90%] m-auto">
          <div class="-ml-8 -mb-3">
            <img :src="voteBooksIcon" alt="reject task" class="drop-shadow-xl" />
          </div>
          <div class="pb-4">
            <p class="text-xl md:text-2xl mt-3 mb-3 font-semibold text-gray-800">
              Select Vote Account
            </p>
            <p class="text-xs md:text-sm text-gray-500">
              Choose the Vote account from which the payment amount will be
              deducted. Ensure that the selected account has sufficient funds
              before proceeding.
            </p>
          </div>
        </section>
        <!-- THE FORM GOES HERE -->
        <section class="w-[94%] m-auto border-t border-solid border-[#E6E6E6] py-5">
          <div class="flex gap-3 items-center px-3 text-gray-700">
            <Icon icon="material-symbols:select-all-rounded" width="20" />
            <p class="text-sm md:text-base">Select Account</p>
          </div>
          <div class="bg-white border border-solid flex flex-col gap-y-3 rounded-2xl px-3 py-3 mt-4">
            <div v-for="(account, index) in voteBooks" :key="index"
              class="bg-[#F9FAFB] px-3 md:px-6 py-3 rounded-xl" :class="selectedAccount === account
                  ? 'border-[1.5px] border-[#12B76A] drop-shadow-md'
                  : ''
                " @click="selectAccount(account)">
              <div class="flex items-center">
                <!-- Checkbox here -->
                <div class="flex flex-[1.8] md:flex-[1.7] items-center gap-3">
                  <div class="mr-4">
                    <Checkbox class="h-5 w-5" :model-value="selectedAccount" />
                  </div>
                  <div>
                    <p class="mb-1 font-medium text-sm md:text-base">
                      {{ account.name }}
                    </p>
                    <p class="text-xs md:text-sm font-light text-gray-600">
                      {{ account.account_number }} - {{ account.platform }}
                    </p>
                  </div>
                </div>
                <div class="flex-[0.2] md:flex-[0.3]">
                  <p class="text-gray-600 text-xs font-extralight mb-1 text-right md:text-left">
                    Balance
                  </p>
                  <p class="font-semibold text-sm md:text-base">
                    ₦{{ formatWithCommas(account.balance) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- The CTA Button -->
        <div class="self-end flex items-center justify-end gap-3 mt-1 pb-2 pr-5">
          <button @click="cancelModal"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-10 bg-white" label="Cancel">
            Cancel
          </button>

          <button @click="NextButton"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10"
            :disabled="!selectedAccount" severity="secondary" outlined>
            Next
          </button>
        </div>
      </section>
      <!-- Step 2 - Percentage Deduction -->
      <section v-else-if="currentStep === 2" key="step2"
        class="flex flex-col items-start max-h-[85vh] overflow-y-scroll hide_scroll">
        <section class="w-[90%] m-auto">
          <div class="-ml-8 -mb-3">
            <img :src="voteBooksIcon" alt="reject task" class="drop-shadow-xl" />
          </div>
          <div class="pb-4">
            <p class="text-xl md:text-2xl md:mt-3 mb-3 font-semibold text-gray-800">
              Select Deduction
            </p>
            <p class="text-xs md:text-sm text-gray-500">
              Choose the applicable deductions for this transaction.
            </p>
          </div>
        </section>
        <!-- THE FORM GOES HERE -->
        <section class="w-[94%] m-auto border-t border-solid border-[#E6E6E6] py-5">
          <div class="flex gap-3 items-center px-3 text-gray-700">
            <Icon icon="material-symbols:select-all-rounded" width="20" />
            <p class="text-sm md:text-base">Select Percentage</p>
          </div>
          <div class="bg-white border border-solid flex flex-col gap-y-3 rounded-2xl px-3 py-3 mt-4">
            <div v-for="(percentage, index) in percentageDeduction" :key="index"
              class="bg-[#F9FAFB] px-6 py-3 rounded-xl" :class="selectedPercentage === percentage
                  ? 'border-[1.5px] border-[#12B76A] drop-shadow-md'
                  : ''
                " @click="selectPercentage(percentage)">
              <div class="flex items-center">
                <!-- Checkbox here -->
                <div class="flex items-center gap-3">
                  <div class="mr-4">
                    <Checkbox class="h-5 w-5" :model-value="selectedPercentage === percentage" @update:modelValue="
                      (val) => val && selectAccount(percentage)
                    " />
                  </div>
                  <div>
                    <p class="mb-1 font-medium text-sm md:text-base">
                      {{ percentage.label }}
                    </p>
                    <p class="text-xs md:text-sm font-light text-gray-600 tracking-wide">
                      {{ percentage.details?.VAT }} VAT,
                      {{ percentage.details?.WHT }} WHT,
                      {{ percentage.details?.StampDuty }} Stamp duty
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-[#F9FAFB] py-3 rounded-xl" :class="selectedPercentage === 'customPercent'
                ? 'border-[1.5px] border-[#12B76A] drop-shadow-md'
                : ''
              " @click="selectPercentage('customPercent')">
              <div class="">
                <!-- Checkbox here -->
                <div class="flex flex-[1.7] items-center gap-3 px-6">
                  <div class="mr-4">
                    <Checkbox class="h-5 w-5" :model-value="selectedPercentage === 'customPercent'" @update:modelValue="
                      (val) => val && selectAccount('customPercent')
                    " />
                  </div>
                  <div>
                    <p class="mb-1 font-medium">Custom deduction</p>
                    <p class="text-sm font-light text-gray-600 tracking-wide">
                      Pick a specific figure for each deduction
                    </p>
                  </div>
                </div>
                <div v-if="selectedPercentage === 'customPercent'"
                  class="bg-white py-3 px-3 md:px-16 rounded-lg mx-1 mt-4 grid grid-cols-2 gap-y-5 text-sm">
                  <div class="flex gap-6 items-center">
                    <p class="text-sm md:text-base">VAT</p>
                    <input type="text" v-model="customPercentForm.VAT" placeholder="0%"
                      class="w-20 border-2 border-solid rounded-lg outline-none py-1 px-3" />
                  </div>
                  <div class="flex gap-4 items-center">
                    <p class="text-sm md:text-base">Stamp Duty</p>
                    <input type="text" v-model="customPercentForm.StampDuty" placeholder="0%"
                      class="w-20 border-2 border-solid rounded-lg outline-none py-1 px-3" />
                  </div>
                  <div class="flex gap-4 items-center">
                    <p class="text-sm md:text-base">WHT</p>
                    <input type="text" v-model="customPercentForm.WHT" placeholder="0%"
                      class="w-20 border-2 border-solid rounded-lg outline-none py-1 px-3" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- The CTA Button -->
        <div class="self-end flex items-center justify-end gap-3 mt-1 pb-2 pr-5">
          <button @click="cancelModal"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-10 bg-white" label="Cancel">
            Cancel
          </button>

          <button @click="NextButton"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10"
            :disabled="!selectedPercentage" severity="secondary" outlined>
            Next
          </button>
        </div>
      </section>
      <!-- Step 3 - Deduction Voucher Generating -->
      <section v-else-if="currentStep === 3" key="step3" class="flex flex-col items-center">
        <img :src="voteBooksIcon" alt="votebook icon" width="170" height="170" class="drop-shadow-xl" />
        <div class="flex flex-col items-center p-4 md:p-8 gap-y-3 -mt-16">
          <Icon icon="line-md:loading-alt-loop" width="44" />
          <p class="text-xl md:text-2xl text-gray-800 font-semibold">
            Deduction Voucher Generating...
          </p>
          <p class="text-sm text-gray-500 text-center font-light">
            Please wait while we prepare your deduction voucher. This may take a
            moment. Thank you for your patience!
          </p>
        </div>
        <!-- <div class="border-t border-solid w-full p-5 flex justify-center">
          <button
            @click="cancelModal"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-20 bg-white"
            label="Cancel"
          >
            Cancel
          </button>
        </div> -->
      </section>
      <!-- Step 4 - Voucher Generated -->
      <section v-else-if="currentStep === 4" key="step4" class="flex flex-col items-start">
        <img :src="voucherGeneratedIcon" alt="votebook icon" width="160" height="100"
          class="drop-shadow-xl mt-2 -mb-3" />
        <div class="flex flex-col items-start gap-y-3 px-7">
          <p class="text-xl md:text-2xl font-semibold text-gray-800">Voucher Generated</p>
          <p class="text-gray-500 text-left font-light text-sm md:text-base">
            The voucher have been successfully created and are ready to be
            appended to the main documents. Once appended, all records will be
            consolidated for processing.
          </p>
        </div>
        <div class="w-full px-4 mt-10 mb-3">
          <div class="bg-white w-full rounded-xl p-2 flex flex-col gap-y-2">
            <div v-for="(document, index) in payment.vat_voucher" :key="index" class="flex items-center justify-between">
              <div
                class="flex items-center gap-3 bg-[#F2F4F7] w-full rounded-xl p-3 border-2 border-transparent hover:border-primary">
                <div class="bg-white w-fit p-2 rounded-full">
                  <Icon :icon="'vscode-icons:file-type-pdf2'" width="25" />
                </div>
                <div>
                  <h1 v-if="document.path">{{ document.path.split('/').pop() }}</h1>
                  <p class="text-xs text-gray-600 font-light mt-1">
                    {{ file_sizes[index] ? file_sizes[index] : '' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          class="w-full self-end flex items-center justify-end gap-3 pt-5 pb-2 pr-5 border-[#E6E6E6] text-sm md:text-base">
          <button @click="cancelModal"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-6 md:px-10 bg-white" label="Cancel">
            Cancel
          </button>

          <button @click="appendVouchers"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-6 md:px-10"
            severity="secondary" outlined>
            Append Voucher
          </button>
        </div>
      </section>
    </Transition>
    <template #footer>
      <div v-if="currentStep === 1" class="flex items-center justify-between pb-2 pr-3 md:pr-7">
        <div class="flex items-center gap-3">
          <div class="-ml-1 mt-1 -mr-6 -mb-4">
            <img :src="voteBooksIcon" alt="reject task" class="drop-shadow-xl" width="93" />
          </div>
          <div class="mt-2">
            <p class="font-medium font-sm md:text-base mb-1">
              Initiate Account Retirement
            </p>
            <p class="text-xs md:text-sm font-light">
              Upload payment receipt to retire account to expenditure
            </p>
          </div>
        </div>
        <button @click="initiateRetirementModal = true"
          class="bg-white -mb-2 border-2 border-gray-300 rounded-lg py-2 px-3 md:px-5 cursor-pointer"
          :disabled="!selectedAccount">
          <p class="text-gray-700 flex items-center gap-1" :class="!selectedAccount ? 'opacity-50' : 'opacity-100'">
            <Icon icon="material-symbols-light:check" width="25" />
            {{ "Retire" }}
          </p>
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed , onMounted, watch, defineProps} from "vue";
import { useToast } from "primevue/usetoast";
import { formatAmount, formatWithCommas } from "../../../utils/formatAmount";
import voteBooksIcon from "../../../../../public/images/votebook.svg";
import voucherGeneratedIcon from "../../../../../public/images/voucher_generated.svg";

import { useForm, usePage } from "@inertiajs/vue3";
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled", "refreshPayment"]);
const visible = ref(true);
const currentStep = ref(1);
const selectedAccount = ref(null);
const selectedPercentage = ref(null);
const voteBooks = ref([])
const page = usePage();
const base = computed(() => page.props.base);
const file_sizes = ref([])
const selectAccount = (account) => {
  console.log('this is the account, ', account)
  selectedAccount.value = account;
  form.vote_book_id = account.id
};
const props = defineProps(['payment'])

const selectPercentage = (percent) => {
  selectedPercentage.value = percent;
};
const form = useForm({
  payment_id: props.payment.id,
  stamp_duty: "",
  vat: "",
  wht: "",  
  vote_book_id: "",
})

const customPercentForm = ref({
  VAT: "",
  StampDuty: "",
  WHT: "",
});

const getVoteBooks = async ()=>{
  await axios.get('/finance-and-accounts/get-votebooks').then((response)=>{
    voteBooks.value = response.data
  })
}

const NextButton = () => {
  if (currentStep.value === 1 && !selectedAccount.value) {
    toast.add({
      severity: "warn",
      summary: "No Account Selected",
      detail: "Please select a vote account to proceed.",
      life: 3000,
    });
    return;
  }
  if (currentStep.value === 2 && !selectedPercentage.value) {
    toast.add({
      severity: "warn",
      summary: "No Deduction Selected",
      detail: "Please select a deduction option to proceed.",
      life: 3000,
    });
    return;
  }
  currentStep.value++;
};


const percentageDeduction = ref([
  {
    label: "13.5% deduction",
    details: {
      VAT: "7.5%",
      WHT: "5%",
      StampDuty: "1%",
    },
  },
  {
    label: "18.5% deduction",
    details: {
      VAT: "7.5%",
      WHT: "10%",
      StampDuty: "1%",
    },
  },
  {
    label: "No Charge",
    details: {
      VAT: "0%",
      WHT: "0%",
      StampDuty: "0%",
    },
  },
]);

const voucherGenerated = ref([
  {
    name: "7.5% VAT",
    size: "200 KB ",
    icon: "vscode-icons:file-type-pdf2",
  },
  {
    name: "5% WHT",
    size: "200 KB ",
    icon: "vscode-icons:file-type-pdf2",
  },
  {
    name: "1% Stamp duty",
    size: "200 KB ",
    icon: "vscode-icons:file-type-pdf2",
  },
]);

const deductVoucher = async () => {
  currentStep.value = 3;

  if(selectedPercentage.value === 'customPercent'){
    form.stamp_duty = customPercentForm.value.StampDuty || 0
    form.vat = customPercentForm.value.VAT || 0
    form.wht = customPercentForm.value.WHT || 0
  }else{
    form.stamp_duty = selectedPercentage.value.details?.StampDuty || 0
    form.vat = selectedPercentage.value.details?.VAT || 0
    form.wht = selectedPercentage.value.details?.WHT || 0
  }
  form.vote_book_id = selectedAccount.value.id

  form.post('/finance-and-accounts/generate-vat-voucher',{onSuccess:()=>{
    toast.add({
      severity: "success",
      summary: "VAT Voucher Generated",
      detail: "Deduction voucher was successfully generated.",
      life: 3000,
    });

    emit('refreshPayment')
      currentStep.value = 4;
  },
  onError: (error)=>{
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "An error occurred while generating the voucher. Please try again.",
      life: 3000,
    });
    currentStep.value = 2;
  }})

  // // Simulate API delay
  // await new Promise((resolve) => setTimeout(resolve, 6000));
};

const appendVouchers = () => {
  emit("cancelled");
};

async function getPdfSize(val) {
   if(val){
    if(props.payment.vat_voucher.length > 0){
      //iterate over the vat_voucher array to get the size of each pdf
      for(let i = 0; i < props.payment.vat_voucher.length; i++){
        const response = await fetch(base.value + '/' + props.payment.vat_voucher[i].path);
        const blob = await response.blob();
        const sizeInKB = (blob.size / 1024).toFixed(2) + ' KB';
        file_sizes.value.push(sizeInKB)
    }
   }
}
}

const cancelModal = () => {
  emit("cancelled");
};

watch(currentStep, (newStep)=>{
  if(newStep === 3){
    deductVoucher()
  }
})

watch(()=>props.payment, (newPayment)=>{
  if(newPayment.vat_voucher.length > 0){
    getPdfSize(newPayment.vat_voucher)
  }
}, {immediate: true})

onMounted(()=>{
  getVoteBooks()
})
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s ease;
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
