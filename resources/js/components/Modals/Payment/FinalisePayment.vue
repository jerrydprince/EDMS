<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-gray-100 rounded-2xl' },
      header: { class: 'bg-gray-100 pb-6 rounded-2xl' },
      content: { class: 'px-0 bg-gray-100 pb-6 w-[90%] m-auto' },
      footer: { class: 'bg-gray-100 rounded-2xl' },
    }"
    :style="{ width: '34rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <section class="flex flex-col items-start">
      <div class="items-start justify-center flex -ml-3 -mt-2">
        <img src="/assets/images/accepttask.svg" alt="reject task" />
      </div>
      <div class="border-b border-solid border-[#E6E6E6] pb-4">
        <p class="text-2xl mt-3 mb-3 font-semibold text-gray-800">
          Finalise Payment Confirmation
        </p>
        <p class="text-sm text-gray-500">
          By accepting this task, you confirm that the payment process has been
          fully completed, including initiation, execution, and payout. This
          action will officially close the transaction and mark the payment as
          settled in the system.
        </p>
      </div>
      <!-- THE FORM GOES HERE -->
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-3 mt-5 pb-2 pr-7">
        <button
          @click="cancelModal"
          class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-10 bg-white"
          label="Cancel"
        >
          Cancel
        </button>

        <button
          @click="finalisePayment"
          class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10"
          severity="secondary"
          outlined
        >
          Confirm
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed } from "vue";
import { useToast } from "primevue/usetoast";
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const finalisePayment = () => {
  // emit("Accepted");
  toast.add({
    severity: "success",
    summary: "Payment Process Completed",
    detail: "The task has been accepted and finalized.",
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
