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
    <section class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-center">
      <div class="w-full pb-4 mt-6 ml-7">
        <p class="text-2xl font-semibold text-gray-800 mb-4">Top-up Amount</p>
      </div>
      <!-- THE CURRENT ACCOUNT DETAILS HERE -->
      <section class="w-full px-4">
        <div class="bg-white rounded-2xl p-4">
          <p class="font-medium text-sm mb-3">Add Amount To</p>
          <div class="flex items-center -mt-3 -mb-8">
            <img
              src="/assets/images/votebook.svg"
              alt="votebook"
              class="-ml-7 w-24"
            />
            <div class="-mt-4 -ml-4">
              <p class="font-medium mb-1">{{ accountSelected.accountType }}</p>
              <p class="text-sm text-gray-600">
                {{ accountSelected.accountNumber }} -
                {{ accountSelected.platform }}
              </p>
            </div>
          </div>
        </div>
        <!-- Form Field -->
        <div
          class="flex flex-col gap-y-10 justify-center items-center mt-12 mb-2"
        >
          <div class="flex items-center gap-1">
            <Icon icon="mdi:naira" width="40" class="text-gray-400" />
            <input
              v-model="form.amount"
              type="number"
              placeholder="8,374,562.27"
              class="py-2 rounded-lg focus:outline-none text-4xl w-[220px] text-gray-700 border-transparent bg-transparent font-medium"
            />
          </div>
          <p class="text-sm text-gray-400">
            Current Balance ₦{{ formatWithCommas(accountSelected.balance) }}
          </p>
        </div>
      </section>
    </section>

    <template #footer>
      <div
        class="flex items-center justify-end gap-3 mt-5 py-3 pr-5 border-t-2 border-solid border-[#E6E6E6]"
      >
        <button
          @click="cancelModal"
          class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-10 bg-white"
          label="Cancel"
        >
          Cancel
        </button>

        <button
          @click="addNewVote"
          class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10"
          :disabled="!form.amount"
          severity="secondary"
          outlined
        >
          Add
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed } from "vue";
import { useToast } from "primevue/usetoast";
//import { formatWithCommas, formatAmount } from "../../utils/formatAmount";
import { formatWithCommas, formatAmount } from "../../../utils/formatAmount";
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
// Defining Props which is the Account details
const { accountSelected } = defineProps({
  accountSelected: {
    type: Object,
    required: true,
  },
});
const visible = ref(true);

const form = ref({
  amount: null,
});

const addNewVote = () => {
  toast.add({
    severity: "success",
    summary: "Amount Successfully Added!",
    detail: `₦${formatAmount(
      form.value?.amount
    )} has been added to the selected vote.`,
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

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  margin: 0;
}
</style>
