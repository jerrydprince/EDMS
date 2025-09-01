<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-gray-100 rounded-2xl mx-2 md:mx-0' },
      header: { class: 'hidden' },
      content: { class: 'px-0 bg-gray-100 w-[100%] m-auto rounded-t-2xl' },
      footer: { class: 'bg-gray-100 rounded-2xl' },
    }"
    :style="{ width: '32rem', padding: '0px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <section class="max-h-[80vh] overflow-y-scroll hide_scroll flex flex-col items-center pt-3">
      <div
        class="items-center justify-start flex border-b border-solid border-[#E6E6E6] w-full"
      >
        <img src="/assets/images/cashbook.svg" alt="reject task" class="" />
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
            <input
              v-model="form.date"
              type="datetime-local"
              placeholder="Add Comments"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Voucher No</p>
          </div>
          <div>
            <input
              v-model="form.voucherNo"
              type="text"
              placeholder="Enter Voucher No"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">NCOA Code</p>
          </div>
          <div>
            <input
              v-model="form.NCOACode"
              type="text"
              placeholder="Enter NCOA Code"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">DVBN</p>
          </div>
          <div>
            <input
              v-model="form.DVBN"
              type="text"
              placeholder="Enter DVBN"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">From Whom Received</p>
          </div>
          <div>
            <input
              v-model="form.fromWhomReceived"
              type="text"
              placeholder="Enter Name"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Whom to Pay</p>
          </div>
          <div>
            <input
              v-model="form.whomToPay"
              type="text"
              placeholder="Enter Name"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Description of Receipt</p>
          </div>
          <div>
            <textarea
              v-model="form.descriptionOfReceipt"
              rows="3"
              style="resize: none"
              placeholder="Enter Message"
              class="px-3 w-full py-2 rounded-lg focus:outline-none text-sm drop-shadow-sm border border-solid border-gray-300"
            ></textarea>
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Description of Payment</p>
          </div>
          <div>
            <textarea
              v-model="form.descriptionOfPayment"
              rows="3"
              style="resize: none"
              placeholder="Enter Message"
              class="px-3 w-full py-2 rounded-lg focus:outline-none text-sm drop-shadow-sm border border-solid border-gray-300"
            ></textarea>
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">TSA/Bank</p>
          </div>
          <div>
            <input
              v-model="form.TSABank"
              type="text"
              placeholder="Enter Amount"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">No of Treasury Receipt</p>
          </div>
          <div>
            <input
              v-model="form.noOfTreasuryReceipt"
              type="text"
              placeholder="Enter Figure"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
      </section>
    </section>

    <template #footer>
      <div
        class="flex items-center justify-end gap-3 mt-5 py-5 pr-7 border-t border-dashed border-[#E6E6E6]"
      >
        <button
          @click="cancelModal"
          class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-10 bg-white"
          label="Cancel"
        >
          Cancel
        </button>

        <button
          @click="createNewCashBookEntry"
          class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10"
          :disabled="!isFormValid"
          severity="secondary"
          outlined
        >
          Save
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed } from "vue";
import { useToast } from "primevue/usetoast";
import Dropdown from "primevue/dropdown";
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const createNewCashBookEntry = () => {
  // emit("Accepted");
  toast.add({
    severity: "success",
    summary: "Entry Added Successfully",
    detail: "The transaction has been recorded in the cashbook.",
    life: 3000,
  });
  emit("cancelled");
};

const form = ref({
  date: "",
  voucherNo: "",
  NCOACode: "",
  DVBN: "",
  fromWhomReceived: "",
  whomToPay: "",
  descriptionOfPayment: "",
  descriptionOfReceipt: "",
  TSABank: "",
  noOfTreasuryReceipt: "",
});

const isFormValid = computed(() => {
  return (
    form.value.date.trim() !== "" &&
    form.value.voucherNo.trim() !== "" &&
    form.value.NCOACode.trim() !== "" &&
    form.value.DVBN.trim() !== "" &&
    form.value.fromWhomReceived.trim() !== "" &&
    form.value.whomToPay.trim() !== "" &&
    form.value.TSABank.trim() !== "" &&
    form.value.noOfTreasuryReceipt.trim() !== ""
  );
});

const cancelModal = () => {
  emit("cancelled");
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none; /* Hide the header */
}
</style>
