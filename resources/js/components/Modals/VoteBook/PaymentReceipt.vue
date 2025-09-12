<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: {
      class: [
        'bg-transparent rounded-2xl transition-all duration-500 ease-in-out md:w-[60vw] w-[90vw] transition-all duration-500 ease-in-out lg:w-[35rem]',
      ],
    },
    header: { class: 'hidden' },
    content: {
      class: 'w-full px-0 bg-gray-100 pb-2 pt-2 m-auto rounded-2xl',
    },
    footer: { class: 'hidden' },
  }" :style="{ padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class=".custom-dialog">
    <Transition name="fade-slide" mode="out-in">
      <!-- Step 1 - View Payment Details -->
      <section v-if="currentStep === 1" key="step1"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center justify-center">
        <section class="w-[80%] m-auto">
          <div class="w-full flex items-center justify-center">
            <img src="../../../assets/images/votebook.svg" alt="reject task" class="drop-shadow-xl -mb-7" />
          </div>
          <div class="pb-4 text-center">
            <p class="text-2xl mt-3 mb-4 font-semibold text-gray-800">
              Payment Details
            </p>
            <p class="text-sm text-gray-500 font-[350]">
              Please review the following votebook account details carefully.
              Make the payment using the GIFMIS application, and ensure you save
              the receipt for upload.
            </p>
          </div>
        </section>
        <!-- THE ACCOUNT DETAILS HERE -->
        <section class="w-[94%] m-auto py-5" v-if="payment.voucher && payment.voucher.length  > 0">
          <!-- <div class="flex gap-3 items-center justify-end px-4 text-sm text-primary cursor-pointer">
            <p>Copy details</p>
            <Icon icon="si:copy-line" width="20" />
          </div> -->
            <div class="flex gap-3 items-center justify-end px-4 text-sm text-primary cursor-pointer" @click="copyAccountDetails">
            <p>Copy details</p>
            <Icon icon="si:copy-line" width="20" />
            </div>
            <div ref="accountDetailsRef" class="bg-white border border-solid flex flex-col gap-y-7 rounded-2xl px-5 py-4 mt-4">
            <div class="flex items-center justify-between">
              <p class="font-[450] text-sm">Voucher number</p>
              <p class="text-sm text-gray-600 font-[350]">
              {{ payment.voucher.length > 0 ? payment.voucher[0].voucher_number : 'N/A' }}
              </p>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-[450] text-sm">Deptal No.</p>
              <p class="text-sm text-gray-600 font-[350]">
              {{ payment.voucher.length > 0 ? payment.voucher[0].deptal_no : 'N/A' }}
              </p>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-[450] text-sm">Amount</p>
              <p class="text-sm text-gray-600 font-[350]">
              ₦{{ payment.voucher.length > 0 ? formatWithCommas(payment.voucher[0].amount) : 'N/A' }}
              </p>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-[450] text-sm">Classification Code</p>
              <p class="text-sm text-gray-600 font-[350]">
              {{ payment.voucher.length > 0 ? payment.voucher[0].classification_code : 'N/A' }}
              </p>
            </div>
            <!-- <div class="flex items-center justify-between">
              <p class="font-[450] text-sm">Total Amount Due</p>
              <p class="text-sm text-gray-600 font-[350]">
              {{ paymentAccountDetails.amountDue }}
              </p>
            </div>
            <div class="flex items-center justify-between">
              <p class="font-[450] text-sm">Payment Due Date</p>
              <p class="text-sm text-gray-600 font-[350]">
              {{ paymentAccountDetails.dueDate }}
              </p>
            </div> -->
            </div>
        </section>
        <!-- The CTA Button -->
        <div class="self-end flex items-center justify-end gap-3 mt-1 pb-2 pr-5">
          <button @click="cancelModal"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-10 bg-white" label="Cancel">
            Close
          </button>

          <button @click="currentStep++"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10" severity="secondary"
            outlined>
            Upload Reciept
          </button>
        </div>
      </section>
      <!-- Step 2 - Percentage Deduction -->
      <section v-else-if="currentStep === 2" key="step2"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-start pt-3">
        <section class="w-[92%] m-auto">
          <div @click="currentStep--" class="pb-4 flex items-center gap-x-3 cursor-pointer text-gray-700">
            <Icon icon="mdi:arrow-left" width="20" />
            <p class="font-[450] text-gray-700">Upload Payment Receipt</p>
          </div>
        </section>
        <!-- THE UPLOAD GOES HERE -->
        <section class="w-[94%] m-auto py-5">
          <div v-if="receiptFileUrl">
            <div class="w-full h-64 rounded-2xl mt-4 p-5 pb-0 border-2 border-gray-200">
              <iframe :src="receiptFileUrl" class="w-full h-[68%]"></iframe>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 bg-[#F2F4F7] w-full rounded-xl py-3 border-2 border-transparent">
                  <div class="bg-white w-fit p-2 rounded-full">
                    <Icon icon="vscode-icons:file-type-pdf2" width="25" />
                  </div>
                  <div>
                    <p class="truncate">{{ receiptFile.name }}</p>
                    <p class="text-xs text-gray-600 font-light mt-1">
                      {{ Math.round(receiptFile.size / 1024) }}KB
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-3 text-gray-400">
                  <Icon icon="ph:eye-light" width="25" class="cursor-pointer" />
                  <Icon icon="tabler:trash" width="20" class="cursor-pointer" @click="receiptFileUrl = null" />
                </div>
              </div>
            </div>
            <div class="flex items-center justify-between px-3">
              <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" />
              <p @click="triggerFileSelect"
                class="text-sm font-[450] text-gray-700 mt-4 cursor-pointer hover:underline">
                Upload another file
              </p>
              <p @click="receiptFileUrl = null"
                class="text-sm font-[450] text-gray-700 mt-4 cursor-pointer hover:underline">
                Clear Upload
              </p>
            </div>
          </div>
          <div v-else class="h-60 border-2 border-solid rounded-2xl py-3 px-2">
            <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload" />
            <div @click="triggerFileSelect"
              class="h-full border-2 border-dashed rounded-2xl flex flex-col items-center justify-center cursor-pointer">
              <Icon icon="mynaui:cloud-upload" width="30" class="text-gray-700 mb-3" />
              <p class="text-[#202020] font-medium mb-2">
                Choose a file or drag & drop it here
              </p>
              <p class="text-gray-600 font-light text-sm">
                supports PDF & Word docs
              </p>
              <button
                class="mt-3 px-4 py-2 flex items-center gap-2 rounded-lg border text-gray-700 border-gray-300 text-sm">
                <Icon icon="mdi-light:folder" width="23" />
                <span class="-mb-1">Browse computer</span>
              </button>
            </div>
          </div>
        </section>
        <!-- The CTA Button -->
        <div class="self-end flex items-center justify-end gap-3 mt-3 pb-2 pr-5">
          <button @click="cancelModal"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-10 bg-white" label="Cancel">
            Close
          </button>

          <button @click="appendReceipt"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10" :disabled="!receiptFileUrl"
            severity="secondary" outlined>
            Confirm
          </button>
        </div>
      </section>
    </Transition>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed, defineProps } from "vue";
import { useToast } from "primevue/usetoast";
import { formatWithCommas } from "../../../utils/formatAmount";
import { useForm } from "@inertiajs/vue3";

const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const currentStep = ref(1);
const fileInput = ref(null);
const props = defineProps(['payment'])

const paymentAccountDetails = ref({
  accountName: "Project Capital Fund",
  accountNumber: "1234567890",
  bankName: "Zenith Bank PLC",
  refNumber: "REF-2025-8976",
  amountDue: "₦2,500,000",
  dueDate: "15th March 2025",
});

const form = useForm({
  payment_id: props.payment.id,
  receipt: null,
});

const receiptFile = ref(null);
const receiptFileUrl = ref(null);
const triggerFileSelect = () => fileInput.value?.click();
const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  // Set file
  receiptFile.value = file;
  form.receipt = file;
  // URL for file preview
  receiptFileUrl.value = URL.createObjectURL(file);
};

const appendReceipt = async () => {
  form.post('/finance-and-accounts/payment-receipt-upload', {onSuccess: () => {
    toast.add({ severity: 'success', summary: 'Receipt uploaded successfully', life: 2000 });
    emit("confirmed");
    emit("cancelled");
  }, onError: () => {
    toast.add({ severity: 'error', summary: 'Failed to upload receipt', life: 2000 });
  }});
};
const accountDetailsRef = ref(null);

const copyAccountDetails = () => {
  const voucher = props.payment.voucher?.[0];
  if (!voucher) {
    toast.add({ severity: 'warn', summary: 'No details to copy', life: 2000 });
    return;
  }
  const details = `
Voucher number: ${voucher.voucher_number}
Deptal No.: ${voucher.deptal_no}
Amount: ₦${formatWithCommas(voucher.amount)}
Classification Code: ${voucher.classification_code}
  `.trim();
  navigator.clipboard.writeText(details)
    .then(() => {
      toast.add({ severity: 'success', summary: 'Copied to clipboard', life: 2000 });
    })
    .catch(() => {
      toast.add({ severity: 'error', summary: 'Failed to copy', life: 2000 });
    });
};

const cancelModal = () => {
  emit("cancelled");
};
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
