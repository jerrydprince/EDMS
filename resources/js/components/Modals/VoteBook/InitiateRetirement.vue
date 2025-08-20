<template>
  <Dialog
    v-model:visible="visible"
    :closable="false"
    modal
    :pt="{
      root: { class: 'bg-gray-100 rounded-2xl mx-2 md:mx-0' },
      header: { class: 'bg-gray-100 pb-6 rounded-2xl' },
      content: { class: 'px-0 bg-gray-100 pb-6 w-[90%] m-auto' },
      footer: { class: 'bg-gray-100 rounded-b-2xl border-t-2 border-solid pt-4' },
    }"
    :style="{ width: '32rem', padding: '10px 0px' }"
    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog"
  >
    <section class="max-h-[85vh] overflow-y-scroll hide_scroll flex flex-col items-start">
      <div class="items-center justify-center flex -ml-6 -mb-6 -mt-6">
        <img src="/assets/images/votebook.svg" alt="reject task" class="drop-shadow-lg" />
      </div>
      <div class="pl-2 text-left">
        <p class="text-2xl mb-4 mt-2 font-semibold text-gray-800">
          Initiate Retirement?
        </p>
        <p class="text-sm text-left w-[95%] text-gray-500">
          By initiating retirement, you confirm that all necessary receipts have
          been uploaded, and the advance payment process will now move to the
          next stage for review and confirmation.
        </p>
      </div>
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-5 pr-5 mb-2">
        <button
          @click="cancelModal"
          class="py-2 w-auto border-gray-200 border-2 border-solid rounded-lg px-10 bg-white"
          label="Cancel"
        >
          Cancel
        </button>

        <button
          @click="retirePayment"
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
import { defineEmits, ref } from "vue";
import { useToast } from "primevue/usetoast";
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const retirePayment = () => {
  // emit("Rejected");
  toast.add({
    severity: "success",
    summary: "Retirement Initiated",
    detail: "Receipt uploaded. Retirement process started.",
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
