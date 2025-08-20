<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-gray-100 rounded-2xl mx-3 md:mx-0' },
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
    <section class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-start p-4 py-6">
      <div class="w-full flex justify-between items-start">
        <img
          src="/assets/images/ledger.svg"
          alt="ledger icon"
          class="w-32 md:w-40 -ml-8 -mt-6 drop-shadow-xl"
        />
        <div class="flex items-center gap-2 pt-2 pr-2">
          <div
            @click="retireAccountAction()"
            class="bg-white px-2 md:px-3 py-[10px] border-2 border-solid text-gray-700 rounded-lg drop-shadow-sm cursor-pointer flex items-center gap-2 text-xs md:text-sm"
          >
            <Icon icon="material-symbols-light:check" width="20" />
            <p>Retire Account</p>
          </div>
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
            Ledger Entry
          </p>
          <div class="flex items-center gap-3 text-xs md:text-sm mt-3">
            <p class="bg-[#E8E8E8] py-2 px-3 md:px-4 rounded text-gray-600">
              {{ ledgerDetails.ref_no }}
            </p>
            <p class="bg-[#E8E8E8] py-2 px-3 md:px-4 rounded text-gray-600">
              {{ ledgerDetails.time_stamp }}
            </p>
            <p class="bg-[#E8E8E8] py-2 px-3 md:px-4 rounded text-gray-600">
              {{ ledgerDetails.status }}
            </p>
          </div>
        </div>
        <div
          class="border-t-2 border-dashed mt-6 w-full flex justify-between pr-4 pt-4"
        >
          <div>
            <p class="text-gray-500 text-sm md:text-base">Debit (DR)</p>
            <p
              class="text-base md:text-xl text-gray-900 mt-2 ml-[0.5px] tracking-wide"
            >
              ₦<span class="ml-1 font-medium">{{ ledgerDetails.debit }}</span>
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-sm md:text-base">Credit (CR)</p>
            <p
              class="text-base md:text-xl text-gray-900 mt-2 ml-[0.5px] tracking-wide"
            >
              ₦<span class="ml-1 font-medium">{{ ledgerDetails.credit }}</span>
            </p>
          </div>
          <div>
            <p class="text-gray-500 text-sm md:text-base">Balance</p>
            <p
              class="text-base md:text-xl text-gray-900 mt-2 ml-[0.5px] tracking-wide"
            >
              ₦<span class="ml-1 font-medium">{{ ledgerDetails.balance }}</span>
            </p>
          </div>
        </div>
        <div class="bg-white mt-7 mb-3 rounded-lg py-3 px-3 md:px-4 mr-2">
          <p class="text-gray-500 mb-3 text-xs md:text-sm">Narration</p>
          <p class="text-gray-900 mb-1 text-sm md:text-base">
            {{ ledgerDetails.narration }}
          </p>
        </div>
      </section>
      <section class="w-full px-2 mt-2">
        <p class="text-gray-500 mb-2 text-xs md:text-sm">Receipts</p>
        <div
          v-for="(file, index) in ledgerDetails.receipts"
          :key="index"
          class="bg-white rounded-xl w-full px-3 py-4 border border-solid border-gray-200 mb-[5px]"
        >
          <div class="flex items-center gap-8">
            <Icon icon="vscode-icons:file-type-pdf2" width="42" />
            <div class="w-full">
              <p class="text-gray-800 font-medium mb-1 text-sm md:text-base">
                {{ file.fileName.slice(0, 35)
                }}{{ file.fileName?.length >= 35 ? "..." : "" }}
              </p>
              <div class="flex items-center gap-3 text-xs md:text-sm text-gray-500">
                <p>{{ file.fileSize }}.</p>
                <div class="flex items-center gap-1">
                  <Icon
                    icon="mynaui:check-circle-one-solid"
                    width="16"
                    class="text-primary"
                  />
                  <span>{{ file.status }}</span>
                </div>
              </div>
            </div>
            <div>
              <div class="text-gray-400 flex items-center gap-2 md:gap-4 pr-2">
                <Icon
                  icon="material-symbols:download"
                  width="23"
                  class="cursor-pointer"
                />
                <Icon
                  icon="solar:eye-outline"
                  width="20"
                  class="cursor-pointer"
                />
              </div>
            </div>
          </div>
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

const ledgerDetails = ref({
  ref_no: "REF002",
  time_stamp: "12-01-2025, 09:00 AM",
  status: "Processing",
  debit: "300,000",
  credit: "580,000",
  balance: "980,000",
  narration:
    "Payment for office supplies and maintenance of computer accessories",
  receipts: [
    {
      fileName: "Utility Bill Payment Receipt",
      fileType: "PDF",
      fileSize: "120 KB",
      status: "Completed",
      actions: {
        download: true,
        view: true,
      },
    },
    {
      fileName: "Client Refund Receipt",
      fileType: "PDF",
      fileSize: "120 KB",
      status: "Completed",
      actions: {
        download: true,
        view: true,
      },
    },
  ],
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
