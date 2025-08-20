<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: {
        class: [
          'w-[90vw]',
          currentStep === 1
            ? 'md:w-[80vw] lg:w-[55vw]'
            : 'md:w-[60vw] lg:w-[35vw]',
          'transition-all duration-500 ease-in-out',
          'bg-white rounded-xl p-6 shadow-lg',
        ],
      },
      header: { class: 'hidden' },
      content: {
        class: 'px-0 bg-gray-100 w-[100%] m-auto rounded-t-2xl rounded-b-2xl',
      },
      footer: { class: 'hidden' },
    }"
    :style="{ padding: '0px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <Transition name="fade-slide" mode="out-in">
      <!-- Step 1: Voucher form -->
      <section
        v-if="currentStep === 1"
        key="step1"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center"
      >
        <div
          class="items-center justify-start flex border-b border-solid border-[#E6E6E6] w-full"
        >
          <div class="px-5 py-4">
            <p class="text-lg font-semibold text-gray-700">
              Voucher Payment Information
            </p>
          </div>
        </div>
        <!-- THE FORM GOES HERE -->
        <section
          class="flex flex-col md:flex-row items-start gap-5 px-6 w-full"
        >
          <section class="w-full">
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Deptal No</p>
              </div>
              <div>
                <input
                  v-model="form.deptalNo"
                  type="text"
                  placeholder="Enter Deptal No"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
                />
              </div>
            </div>
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
            <div class="self-start mt-9 flex justify-between items-center">
              <div>
                <p class="text-gray-700 mb-1 text-sm">Upload Signature</p>
                <p class="text-gray-500 text-xs">
                  (JPEG/PNG formats supported).
                </p>
              </div>
              <div>
                <!-- Hidden file input -->
                <input
                  type="file"
                  ref="fileInput"
                  class="hidden"
                  @change="handleFileUpload"
                />
                <button
                  class="text-gray-700 text-xs bg-white rounded-lg py-2 px-3 border-2 border-solid flex items-center gap-2"
                  @click="triggerFileSelect()"
                >
                  <Icon icon="lucide:upload-cloud" width="18" /><span
                    >Upload Image</span
                  >
                </button>
              </div>
            </div>
            <div class="self-start mt-8">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Amount (Naira)</p>
              </div>
              <div>
                <input
                  v-model="form.amount"
                  type="text"
                  placeholder="₦ Enter Amount"
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
                  disabled
                  placeholder="VOU-53628"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
                />
              </div>
            </div>
            <div class="self-start mt-5 w-full">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Data Type</p>
              </div>
              <div class="w-full">
                <DropdownComp
                  v-model="form.dataType"
                  :items="dataTypes"
                  placeholder="Select Data Type"
                />
              </div>
            </div>
          </section>
          <section class="w-full">
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Source</p>
              </div>
              <div>
                <input
                  v-model="form.source"
                  type="text"
                  placeholder="Enter Source"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
                />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Head</p>
              </div>
              <div>
                <DropdownComp
                  v-model="form.head"
                  :items="headList"
                  placeholder="Select Head"
                />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Checked and passed for payment at</p>
              </div>
              <div>
                <input
                  v-model="form.paymentApprovalLocation"
                  type="text"
                  placeholder="Input Text"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
                />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Classification Code</p>
              </div>
              <div>
                <DropdownComp
                  v-model="form.classificationCode"
                  :items="classificationCodes"
                  placeholder="Select Classification Code"
                />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">S/Head</p>
              </div>
              <div>
                <DropdownComp
                  v-model="form.sHead"
                  :items="subHeadList"
                  placeholder="Select S/Head"
                />
              </div>
            </div>
            <div class="self-start mt-5">
              <div class="text-gray-700 font-normal mb-2 text-sm">
                <p class="">Address</p>
              </div>
              <div>
                <input
                  v-model="form.address"
                  type="text"
                  placeholder="Enter Address"
                  class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
                />
              </div>
            </div>
          </section>
        </section>
        <div
          class="w-full self-end flex items-center justify-end gap-3 mt-5 py-5 pr-7 border-t border-dashed border-[#E6E6E6]"
        >
          <button
            @click="cancelModal"
            class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-6 md:px-10 bg-white"
            label="Cancel"
          >
            Cancel
          </button>

          <button
            @click="createNewVoucherEntry"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-6 md:px-10"
            :disabled="!isFormValid"
            severity="secondary"
            outlined
          >
            Generate Voucher
          </button>
        </div>
      </section>
      <!-- Step 2: Generating Voucher -->
      <section
        v-else-if="currentStep === 2"
        key="step2"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center"
      >
        <img
          src="/assets/images/votebook.svg"
          alt="votebook icon"
          width="170"
          height="170"
          class="drop-shadow-xl"
        />
        <div class="flex flex-col items-center p-8 gap-y-3 -mt-16">
          <Icon icon="line-md:loading-alt-loop" width="44" />
          <p class="text-2xl text-gray-800 font-semibold">
            Voucher Generating...
          </p>
          <p class="text-sm text-gray-500 text-center font-light">
            Please wait while we prepare your payment voucher. This may take a
            moment. Thank you for your patience!
          </p>
        </div>
      </section>
      <!-- Step 3: Voucher Generated -->
      <section
        v-else-if="currentStep === 3"
        key="step3"
        class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center"
      >
        <img
          src="/assets/images/voucher_generated.svg"
          alt="votebook icon"
          width="160"
          height="100"
          class="drop-shadow-xl mt-2 -mb-3"
        />
        <div class="flex flex-col items-center gap-y-3 px-7">
          <p class="text-2xl font-semibold text-gray-800">Voucher Generated</p>
          <p class="text-gray-500 text-center font-light">
            The voucher have been successfully created and are ready to be
            appended to the main documents. Once appended, all records will be
            consolidated for processing.
          </p>
        </div>
        <div class="w-full px-4 mt-10 mb-3">
          <div
            v-for="(document, index) in voucherDetails.voucherDocument"
            :key="index"
            class="rounded-xl p-2 flex items-center justify-between mb-3 bg-white w-full"
          >
            <div
              class="flex items-center gap-3 bg-[#F2F4F7] w-full rounded-xl p-3 border-2 border-transparent hover:border-primary"
            >
              <div class="bg-white w-fit p-2 rounded-full">
                <Icon :icon="document.icon" width="25" />
              </div>
              <div>
                <h1>{{ document.name }}</h1>
                <p class="text-xs text-gray-600 font-light mt-1">
                  {{ document.size }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div
          class="w-full self-end flex items-center justify-end gap-3 py-5 pr-5 border-[#E6E6E6]"
        >
          <button
            @click="currentStep = 1"
            class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-6 md:px-10 bg-white"
            label="Cancel"
          >
            Edit Voucher
          </button>

          <button
            @click="appendNewVoucherEntry"
            class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-6 md:px-10"
            severity="secondary"
            outlined
          >
            Append Voucher
          </button>
        </div>
      </section>
    </Transition>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed, nextTick } from "vue";
import { useToast } from "primevue/usetoast";

import DropdownComp from "../../DropdownComp.vue";

const emit = defineEmits(["confirmed", "cancelled"]);
const toast = useToast();

const visible = ref(true);
const currentStep = ref(1); // 1 = form, 2 = generating, 3 = success

const fileInput = ref(null);

const classificationCodes = ref([
  {
    title: "FGN-34983",
    value: "fgn123",
  },
  {
    title: "STATE-78291",
    value: "state456",
  },
  {
    title: "LGA-56742",
    value: "lga789",
  },
  {
    title: "MIN-19835",
    value: "min012",
  },
  {
    title: "DEPT-92674",
    value: "dept345",
  },
  {
    title: "AGY-41567",
    value: "agy678",
  },
]);

const headList = ref([
  {
    title: "Adebayo Ogundimu",
    value: "adebayo",
  },
  {
    title: "Chinelo Okwu",
    value: "chinelo",
  },
  {
    title: "Fatima Abdullahi",
    value: "fatima",
  },
  {
    title: "Emeka Nwankwo",
    value: "emeka",
  },
  {
    title: "Aisha Bello",
    value: "aisha",
  },
  {
    title: "Olumide Adeyemi",
    value: "olumide",
  },
]);

const subHeadList = ref([
  {
    title: "Kemi Olatunji",
    value: "kemi",
  },
  {
    title: "Chioma Ikechukwu",
    value: "chioma",
  },
  {
    title: "Ibrahim Musa",
    value: "ibrahim",
  },
  {
    title: "Ngozi Anyanwu",
    value: "ngozi",
  },
  {
    title: "Yakubu Garba",
    value: "yakubu",
  },
  {
    title: "Folake Adebisi",
    value: "folake",
  },
]);

const dataTypes = ref([
  {
    title: "String",
    value: "string",
  },
  {
    title: "Boolean",
    value: "boolean",
  },
  {
    title: "Number",
    value: "number",
  },
  {
    title: "Array",
    value: "array",
  },
]);

const form = ref({
  deptalNo: "",
  source: "",
  date: "",
  head: "",
  signature: null,
  paymentApprovalLocation: "",
  amount: "",
  classificationCode: "",
  voucherNo: "",
  sHead: "",
  dataType: "",
  address: "",
});

const isFormValid = computed(() => {
  const f = form.value;
  // return (
  //   f.deptalNo.trim() !== "" &&
  //   f.source.trim() !== "" &&
  //   f.date.trim() !== "" &&
  //   f.head.trim() !== "" &&
  //   f.paymentApprovalLocation.trim() !== "" &&
  //   f.amount.trim() !== "" &&
  //   f.classificationCode.trim() !== "" &&
  //   f.voucherNo.trim() !== "" &&
  //   f.sHead.trim() !== "" &&
  //   f.dataType.trim() !== "" &&
  //   f.address.trim() !== ""
  // );
  return true;
});

const triggerFileSelect = () => {
  fileInput.value?.click();
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  form.value.signature = file;
};

const createNewVoucherEntry = async () => {
  // if (!isFormValid.value) {
  //   toast.add({
  //     severity: "warn",
  //     summary: "Invalid Form",
  //     detail: "Please fill in all fields",
  //     life: 3000,
  //   });
  //   return;
  // }

  currentStep.value = 2;

  // Simulate API delay
  await new Promise((resolve) => setTimeout(resolve, 6000));

  currentStep.value = 3;
};

const appendNewVoucherEntry = () => {
  emit("cancelled");
  visible.value = false;
};

const cancelModal = () => {
  emit("cancelled");
  visible.value = false;
};

// This is after voucher has been created
const voucherDetails = ref({
  voucherDocument: [
    {
      name: "Main Payment Voucher",
      size: "200 KB ",
      icon: "vscode-icons:file-type-pdf2",
    },
  ],
});
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none; /* Hide the header */
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
