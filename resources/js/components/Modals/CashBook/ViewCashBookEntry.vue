<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-gray-100 rounded-2xl mx-2 md:mx-0' },
      header: { class: 'hidden' },
      content: {
        class: 'px-0 bg-gray-100 w-[100%] m-auto rounded-t-2xl rounded-b-2xl',
      },
      footer: { class: 'hidden' },
    }"
    :style="{ width: '32rem', padding: '0px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <section
      class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-start p-4 py-6"
    >
      <div class="w-full flex justify-between items-start">
        <img
          src="/assets/images/cashbook.svg"
          alt="ledger icon"
          class="w-28 md:w-40 -ml-4 md:-ml-8 -mt-6 drop-shadow-xl"
        />
        <div class="flex items-center gap-2 pt-2 pr-2">
          <Icon
            icon="lucide:edit-3"
            class="bg-white p-3 border-2 border-solid text-gray-500 rounded-lg drop-shadow-sm cursor-pointer"
            width="44"
          />
          <Icon
            icon="material-symbols:close"
            class="bg-white p-3 border-2 border-solid text-gray-500 rounded-lg drop-shadow-sm cursor-pointer"
            width="44"
            @click="cancelModal()"
          />
        </div>
      </div>
      <section class="pl-2 w-full">
        <div class="-mt-7">
          <p class="text-gray-900 text-2xl md:text-3xl font-medium">
            Cash Book Entry
          </p>
          <div class="flex items-center gap-3 text-sm mt-3">
            <p
              class="bg-[#E8E8E8] py-2 px-3 md:px-4 rounded text-gray-600 text-sm md:text-base"
            >
              {{ cashbookDetails.ref_no }}
            </p>
            <p
              class="bg-[#E8E8E8] py-2 px-3 md:px-4 rounded text-gray-600 text-sm md:text-base"
            >
              {{ cashbookDetails.time_stamp }}
            </p>
          </div>
        </div>
        <div
          class="border-t-2 border-dashed mt-6 w-full grid grid-cols-3 gap-y-7 gap-x-10 justify-between pt-4"
        >
          <div>
            <p class="text-gray-500 text-xs">NCOA</p>
            <p class="text-gray-900 mt-2 ml-[0.5px] tracking-wide">
              <span class="font-medium text-sm md:text-base">{{
                cashbookDetails.NCOA
              }}</span>
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs">DVBN</p>
            <p class="text-gray-900 mt-2 ml-[0.5px] tracking-wide">
              <span class="font-medium text-sm md:text-base">{{
                cashbookDetails.DVBN
              }}</span>
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs">TSA/Bank</p>
            <p class="text-gray-900 mt-2 ml-[0.5px] tracking-wide">
              ₦<span class="font-medium text-sm md:text-base">{{
                cashbookDetails.TSABank
              }}</span>
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs">From Whom Recived</p>
            <p class="text-gray-900 mt-2 ml-[0.5px] tracking-wide">
              <span class="font-medium text-sm md:text-base">{{
                cashbookDetails.fromWhomReceived
              }}</span>
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs">Whom to Pay</p>
            <p class="text-gray-900 mt-2 ml-[0.5px] tracking-wide">
              <span class="font-medium text-sm md:text-base">{{
                cashbookDetails.whomToPay
              }}</span>
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-xs">Receipt No</p>
            <p class="text-gray-900 mt-2 ml-[0.5px] tracking-wide">
              <span class="font-medium text-sm md:text-base">{{
                cashbookDetails.receiptNo
              }}</span>
            </p>
          </div>
        </div>
        <div class="bg-white mt-7 mb-3 rounded-lg py-3 px-3 md:px-4 mr-2">
          <p class="text-gray-500 mb-3 text-xs">Description of Receipt</p>
          <p class="text-gray-900 mb-1 text-xs md:text-sm font-[450]">
            {{ cashbookDetails.descriptionOfReceipt }}
          </p>
        </div>
        <div class="bg-white mt-7 mb-3 rounded-lg py-3 px-3 md:px-4 mr-2">
          <p class="text-gray-500 mb-3 text-xs">Description of Payments</p>
          <p class="text-gray-900 mb-1 text-xs md:text-sm font-[450]">
            {{ cashbookDetails.descriptionOfPayments }}
          </p>
        </div>
      </section>
    </section>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed } from "vue";
import { useToast } from "primevue/usetoast";

const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const toast = useToast();

const cashbookDetails = ref({
  ref_no: "REF002",
  time_stamp: "12-01-2025, 09:00 AM",
  NCOA: "23020101",
  DVBN: "FGN/2024/00233",
  TSABank: "980,000",
  fromWhomReceived: "Kabiru Ibrahim",
  whomToPay: "Nsikak Akpan",
  receiptNo: "10",
  descriptionOfReceipt:
    "An approved transfer of funds for goods, services, or obligations—often processed after deductions and review.",
  descriptionOfPayments:
    "A proof of payment confirming that goods or services were received and funds were properly used.",
});

const retireAccountAction = () => {
  toast.add({
    severity: "success",
    summary: "Account Successfully Retired",
    detail: "Retirement completed by Advance Section.",
    life: 3000,
  });

  emit("cancelled");
};

const cancelModal = () => {
  emit("cancelled");
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none; /* Hide the header */
}
</style>
