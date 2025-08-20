<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-gray-100 rounded-2xl mx-2 md:mx-0' },
    header: { class: 'bg-gray-100 pb-6 rounded-2xl' },
    content: { class: 'px-0 bg-gray-100 pb-6 w-[90%] m-auto' },
    footer: { class: 'bg-gray-100 rounded-2xl' },
  }" :style="{ width: '32rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <section class="flex flex-col items-center">
      <div class="items-center justify-center flex">
        <!-- <img src="/assets/images/rejecttask.svg" alt="reject task" /> -->
        <img :src="rejecttask" alt="reject task">
      </div>
      <div class="text-center border-b border-solid border-[#E6E6E6] pb-4">
        <p class="text-2xl mb-4 mt-2 font-semibold text-gray-800">
          Reject Task?
        </p>
        <p class="text-sm text-center text-gray-500 px-2 md:px-6">
          By rejecting this task, the folder will be reverted to the
          <span class="text-gray-700 font-semibold">{{
            "Director of Finance"
            }}</span>
          for further review. Are you sure you want to carry out this action?
        </p>
      </div>
      <div class="self-start mt-4">
        <div class="text-gray-700 flex gap-1 items-center font-normal mb-2 ml-1">
          <Icon icon="hugeicons:quill-write-01" width="20" />
          <p class="">Leave Reason</p>
        </div>
        <div>
          <textarea v-model="form.reasonForRejection" rows="3" placeholder="Add Comments"
            class="w-[24rem] md:w-[27rem] p-3 md:p-4 rounded-lg border border-gray-200 focus:outline-none text-sm"></textarea>
        </div>
      </div>
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-5 pr-5 mb-2">
        <button @click="cancelModal" class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-10 bg-white"
          label="Cancel">
          Cancel
        </button>

        <button @click="revertTask" class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10"
          :disabled="!form.reasonForRejection" severity="secondary" outlined>
          Revert
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, defineProps} from "vue";
import { useToast } from "primevue/usetoast";
import {usePage, useForm} from '@inertiajs/vue3';
import rejecttask from "../../../assets/images/rejecttask.svg";

const page = usePage();
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const props = defineProps(['paymentId']);

const form = useForm({
  reasonForRejection: "",
  from: "Division",
  payment_id: props.paymentId,
});


const revertTask = () => {
  if(form.reasonForRejection !== ""){
    form.post('/finance-and-accounts/reject-payment', {
      onSuccess: () => {
        //emit("confirmed");
        emit("cancelled");
         toast.add({
          severity: "danger",
          summary: "Payment Reverted",
          detail: "The file has been reverted back to the DD",
          life: 3000,
        });
      },
      onError: (error) => {
        toast.add({
          severity: "error",
          summary: "Error",
          detail: error.response.data.message || "An error occurred while rejecting the task.",
          life: 3000,
        });
      },
    });
  }
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
</style>
