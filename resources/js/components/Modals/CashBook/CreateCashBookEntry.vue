<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-gray-100 rounded-2xl mx-2 md:mx-0' },
    header: { class: 'hidden' },
    content: { class: 'px-0 bg-gray-100 w-[100%] m-auto rounded-t-2xl' },
    footer: { class: 'bg-gray-100 rounded-2xl' },
  }" :style="{ width: '32rem', padding: '0px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <section class="max-h-[80vh] overflow-y-scroll hide_scroll flex flex-col items-center pt-3">
      <div class="items-center justify-start flex border-b border-solid border-[#E6E6E6] w-full">
        <img src="../../../assets/images/cashbook.svg" alt="reject task" class="" />
        <div class="-mt-2">
          <p class="text-xl font-medium text-primary">Create Cash Book Entry</p>
        </div>
      </div>
      <!-- THE FORM GOES HERE -->
      <section class="w-full px-5 pb-5">
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Date</p>
          </div>
          <div>
            <input v-model="form.date" type="datetime-local" placeholder="Add Comments"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Voucher No</p>
          </div>
          <div>
            <input v-model="form.voucher_no" type="text" placeholder="Enter Voucher No"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">NCOA Code</p>
          </div>
          <div>
            <input v-model="form.ncoa_code" type="text" placeholder="Enter NCOA Code"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">DVBN</p>
          </div>
          <div>
            <input v-model="form.dvbn" type="text" placeholder="Enter DVBN"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">From Whom Received</p>
          </div>
          <div>
            <input v-model="form.from" type="text" placeholder="Enter Name"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Whom to Pay</p>
          </div>
          <div>
            <input v-model="form.to" type="text" placeholder="Enter Name"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Description of Receipt</p>
          </div>
          <div>
            <textarea v-model="form.receipt_description" rows="3" style="resize: none" placeholder="Enter Message"
              class="px-3 w-full py-2 rounded-lg focus:outline-none text-sm drop-shadow-sm border border-solid border-gray-300"></textarea>
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Description of Payment</p>
          </div>
          <div>
            <textarea v-model="form.payment_description" rows="3" style="resize: none" placeholder="Enter Message"
              class="px-3 w-full py-2 rounded-lg focus:outline-none text-sm drop-shadow-sm border border-solid border-gray-300"></textarea>
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">TSA/Bank</p>
          </div>
          <div>
            <input v-model="form.tsa_bank" type="text" placeholder="Enter Amount"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">No of Treasury Receipt</p>
          </div>
          <div>
            <input v-model="form.treasury_receipt" type="text" placeholder="Enter Figure"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
      </section>
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-3 mt-5 py-5 pr-7 border-t border-dashed border-[#E6E6E6]">
        <button @click="cancelModal" class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-10 bg-white"
          label="Cancel">
          Cancel
        </button>

        <button @click="createNewCashBookEntry"
          class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10" :disabled="!isFormValid"
          severity="secondary" outlined>
          Save
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed, defineProps } from "vue";
import { useToast } from "primevue/usetoast";
import Dropdown from "primevue/dropdown";
import { useForm } from "@inertiajs/vue3";
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const props = defineProps(['selectedAccount'])
const createNewCashBookEntry = () => {
  // emit("Accepted");
  form.vote_book_id = props.selectedAccount.id
  if(form.vote_book_id){
      form.post('/finance-and-accounts/create-cashbook',{onSuccess:()=>{
        emit("cancelled");
          toast.add({
          severity: "success",
          summary: "Entry Added Successfully",
          detail: "The transaction has been recorded in the cashbook.",
          life: 3000,
        });
        form.reset()
      },
      onError:()=>{
          toast.add({
        severity: "error",
        summary: "Error!",
        detail: "An error occured, please check all fields.",
        life: 3000,
      });
      }
    })
  }
  
};

// const form = ref({
//   date: "",
//   voucherNo: "",
//   NCOACode: "",
//   DVBN: "",
//   fromWhomReceived: "",
//   whomToPay: "",
//   descriptionOfPayment: "",
//   descriptionOfReceipt: "",
//   TSABank: "",
//   noOfTreasuryReceipt: "",
// });
const form = useForm({
  vote_book_id: '',
  date:'',
  voucher_no:'',
  ncoa_code: '',
  dvbn:'',
  from:'',
  to:'',
  receipt_description:'',
  payment_description:'',
  tsa_bank:'',
  treasury_receipt: ''
})

const isFormValid = computed(() => {
  return (
    form.date !== "" &&
    form.voucher_no.trim() !== "" &&
    form.ncoa_code.trim() !== "" &&
    form.dvbn.trim() !== "" &&
    form.from.trim() !== "" &&
    form.to.trim() !== "" &&
    form.tsa_bank.trim() !== "" &&
    form.treasury_receipt.trim() !== ""
  );
});

const cancelModal = () => {
  emit("cancelled");
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>
