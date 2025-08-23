<template>
  <main class="py-10 px-4 md:px-14">
    <!-- Breadcrumb and Header -->
    <div class="flex items-center gap-3 text-gray-500 text-sm">
      <div
        @click="Router.go(-1)"
        class="flex items-center gap-2 hover:underline"
      >
        <Icon icon="ri:coupon-2-line" width="15" />
        <p>Vote</p>
      </div>
      <Icon icon="material-symbols:chevron-right" width="12" />
      <p>Liability Record</p>
    </div>
    <p class="text-2xl md:text-3xl font-semibold my-8">
      {{ LiabilityDetails.id }}
    </p>

    <!-- Summary Info -->
    <div class="border-t-2 border-dashed py-8">
      <div
        class="grid grid-cols-3 gap-y-3 md:grid-cols-5 items-start justify-between w-full md:w-[90%] xl:w-[80%]"
      >
        <div v-for="(value, label) in summaryDetails" :key="label">
          <p class="text-gray-500 text-xs md:text-sm mb-1">{{ label }}</p>
          <p
            class="text-gray-900 text-sm md:text-base font-semibold flex items-center"
          >
            <Icon
              v-if="label === 'Total' || label === 'Balance'"
              icon="mdi:naira"
              width="18"
            />
            {{ value }}
          </p>
        </div>
      </div>
    </div>

    <!-- Status Section -->
    <section class="overflow-y-scroll hide_scroll">
      <div
        class="w-[700px] md:w-full xl:w-[80%] border-2 border-solid rounded-2xl mt-5"
      >
        <div
          class="text-gray-900 font-medium flex items-start justify-between border-b-2 border-solid py-4 px-7 text-sm"
        >
          <p class="w-48">Status</p>
          <p class="w-[35%]">Details</p>
          <p class="w-44">Action</p>
        </div>
        <div
          v-for="(liability, index) in LiabilityDetails?.record"
          :key="index"
          class="px-7 py-6 flex items-center justify-between border-b-2 last:border-b-0"
        >
          <p
            class="rounded-2xl px-4 py-2 text-sm"
            :class="
              liability.status == 'Processing'
                ? 'bg-[#FEF0EC] text-[#F67E5A]'
                : 'text-gray-800 bg-gray-100'
            "
          >
            {{ liability.status }}
          </p>
          <p class="text-gray-500 w-56 text-sm">
            {{ liability.details }}
          </p>
          <div class="w-48">
            <button
              @click="initiateRetirementModal = true"
              v-if="liability.status === 'Not Retired'"
              class="border border-gray-300 rounded-lg py-2 px-5"
              v-tooltip.bottom="{
                value: liabilityReceipts.length
                  ? ''
                  : 'Upload payment receipt first then try again to retire account',
                pt: {
                  arrow: {
                    style: {
                      borderRightColor: '',
                    },
                  },
                  text: 'bg-[#1D2939] text-xs text-white px-4 py-2 rounded-xl',
                },
              }"
              :disabled="!liabilityReceipts.length"
            >
              <p
                class="text-gray-700 flex items-center gap-1"
                :class="
                  !liabilityReceipts.length
                    ? 'opacity-50 cursor-not-allowed'
                    : 'opacity-100'
                "
              >
                <Icon
                  v-if="liability.status !== 'Retired'"
                  icon="material-symbols-light:check"
                  width="25"
                />
                {{ liability.status === "Retired" ? "Retired" : "Retire" }}
              </p>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- File Upload Section -->
    <div
      class="w-full xl:w-[80%] mt-5 grid grid-cols-1 md:grid-cols-2 items-start gap-x-10 gap-y-5"
    >
      <!-- Upload Box -->
      <div class="h-52 border-2 border-solid rounded-2xl py-3 px-2 order-1">
        <p class="text-gray-500 text-xs md:text-sm ml-2 mb-4 mt-1 font-medium">
          Upload Receipt
        </p>
        <input
          type="file"
          ref="fileInput"
          class="hidden"
          @change="handleFileUpload"
        />
        <div
          @click="triggerFileSelect"
          class="h-[80%] border-2 border-dashed rounded-2xl flex flex-col items-center justify-center cursor-pointer"
        >
          <p class="text-[#202020] text-base md:text-lg font-medium">
            Choose a file or drag & drop it here
          </p>
          <p class="text-gray-600 font-light text-sm md:text-base">
            supports PDF & Word docs
          </p>
        </div>
      </div>

      <!-- Particulars -->
      <div
        class="h-full border-2 border-solid rounded-2xl bg-gray-50 order-3 md:order-2"
      >
        <p class="text-gray-500 text-xs md:text-sm p-4 font-medium">
          Particulars
        </p>
        <div
          class="h-full border-t-2 border-solid px-4 py-6 text-xs md:text-sm md:font-medium"
        >
          <p class="text-gray-900 w-[90%]">
            {{ LiabilityDetails.particulars }}
          </p>
        </div>
      </div>

      <!-- Uploaded Files List -->
      <div
        v-if="liabilityReceipts.length"
        class="flex flex-col gap-y-2 bg-gray-50 w-full rounded-xl p-2 order-2 md:order-3"
      >
        <div
          v-for="(file, index) in liabilityReceipts"
          :key="index"
          class="relative bg-white rounded-xl w-full px-3 py-4 border border-solid border-gray-200"
        >
          <div class="flex items-center gap-8">
            <Icon icon="vscode-icons:file-type-pdf2" width="42" />
            <div class="w-full">
              <p class="text-gray-800 font-medium text-sm md:text-base mb-1">
                {{ file.fileName.slice(0, 35)
                }}{{ file.fileName?.length >= 35 ? "..." : "" }}
              </p>
              <div
                class="flex items-center gap-3 text-xs md:text-sm text-gray-500"
              >
                <p>{{ file.fileSize }}.</p>
                <div class="flex items-center gap-1">
                  <Icon
                    v-if="file.status === 'Completed'"
                    icon="mynaui:check-circle-one-solid"
                    width="16"
                    class="text-primary"
                  />
                  <Icon
                    v-else
                    icon="ep:loading"
                    width="16"
                    class="text-black"
                  />
                  <span>{{ file.status }}</span>
                </div>
              </div>
            </div>
            <div>
              <div
                v-if="file.status === 'Completed'"
                class="text-gray-400 flex items-center gap-2 pr-2"
              >
                <Icon icon="ph:eye" width="16" class="cursor-pointer" />
                <Icon
                  icon="material-symbols:delete-outline"
                  width="16"
                  class="cursor-pointer"
                />
              </div>
              <Icon
                v-else
                icon="material-symbols:delete-outline"
                width="16"
                class="cursor-pointer text-gray-400 absolute top-3 right-4"
              />
            </div>
          </div>
          <!-- Upload Progress Bar -->
          <div v-if="file.status === 'Uploading'" class="mt-3">
            <div class="rounded-full w-full h-1 bg-gray-200">
              <div
                class="h-full bg-primary rounded-full transition-all duration-300"
                :style="{ width: file.progress + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <InitiateRetirement v-if="initiateRetirementModal" @cancelled="closeModal" />
</template>

<script setup>
import { ref } from "vue";

import InitiateRetirement from "../../components/Modals/VoteBook/InitiateRetirement.vue";


const initiateRetirementModal = ref(false);
const fileInput = ref(null);
const liabilityReceipts = ref([]);

const LiabilityDetails = ref({
  id: "REF-240280",
  date: "12-01-2025, 08:00 PM",
  record: [
    {
      status: "Retired",
      details: "Workflow Retired",
    },
    {
      status: "Processing",
      details:
        "Retirement initiated on 15/01/2025, 9:30 AM. Awaiting confirmation",
    },
    {
      status: "Not Retired",
      details: "Workflow passed from advance section",
    },
  ],
  voucherNo: "REF003",
  lineNo: "02",
  particulars:
    "Payment for office supplies delivered by ABC Supplies, including stationery, printing paper, and toner cartridges. The supplies were received on March 15, 2025",
  payment: 25000,
  totalPayment: 25000,
  balance: 300000,
});

const summaryDetails = {
  Date: LiabilityDetails.value.date,
  "Voucher No": LiabilityDetails.value.voucherNo,
  Payments: LiabilityDetails.value.voucherNo,
  Total: new Intl.NumberFormat("en-NG").format(
    LiabilityDetails.value.totalPayment
  ),
  Balance: new Intl.NumberFormat("en-NG").format(
    LiabilityDetails.value.balance
  ),
};

const triggerFileSelect = () => fileInput.value?.click();

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  const newFile = {
    fileName: file.name,
    fileSize: `${Math.round(file.size / 1024)} KB`,
    status: "Uploading",
    progress: 0,
  };

  // Push new file to list
  liabilityReceipts.value.push(newFile);

  // Start simulated upload using index
  simulateUpload(liabilityReceipts.value.length - 1);
};

// You can do the API integrations here
const simulateUpload = (index) => {
  const interval = setInterval(() => {
    const file = liabilityReceipts.value[index];

    if (file.progress >= 100) {
      file.progress = 100;
      file.status = "Completed";
      clearInterval(interval);
    } else {
      file.progress += 10;
    }

    liabilityReceipts.value[index] = { ...file };
  }, 200);
};

const closeModal = () => (initiateRetirementModal.value = false);
</script>
