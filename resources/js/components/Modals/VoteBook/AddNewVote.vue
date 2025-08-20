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
      <div
        class="items-start justify-start flex flex-col border-b-2 border-solid border-[#E6E6E6] w-full pb-4"
      >
        <img
          src="/assets/images/votebook.svg"
          alt="reject task"
          class="-ml-2 drop-shadow-lg"
        />
        <div class="-mt-5 ml-6">
          <p class="text-2xl font-semibold text-gray-800">Add New Vote</p>
        </div>
      </div>
      <!-- THE FORM GOES HERE -->
      <section class="w-full px-5">
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Vote Name</p>
          </div>
          <div>
            <input
              v-model="form.voteName"
              type="text"
              placeholder="Enter Vote Name"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Account Number</p>
          </div>
          <div>
            <input
              v-model="form.accountNumber"
              type="text"
              placeholder="Enter Account Number"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Balance</p>
          </div>
          <div>
            <input
              v-model="form.balance"
              type="text"
              placeholder="₦  Enter Balance"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">GIFMIS Code</p>
          </div>
          <div>
            <input
              v-model="form.GIFMISCode"
              type="text"
              placeholder="Enter GIFMIS Code"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Date Created</p>
          </div>
          <div>
            <input
              v-model="form.dateCreated"
              type="datetime-local"
              placeholder="Add Comments"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
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
          :disabled="!isFormValid"
          severity="secondary"
          outlined
        >
          Create
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
const addNewVote = () => {
  toast.add({
    severity: "success",
    summary: "Vote successfully added!",
    detail: "The new vote has been added to the system.",
    life: 3000,
  });
  emit("cancelled");
};

const form = ref({
  voteName: "",
  accountNumber: "",
  GIFMISCode: "",
  dateCreated: "",
  balance: "",
});

const isFormValid = computed(() => {
  return (
    form.value.voteName.trim() !== "" &&
    form.value.dateCreated.trim() !== "" &&
    form.value.GIFMISCode.trim() !== "" &&
    form.value.accountNumber.trim() !== "" &&
    form.value.balance.trim() !== ""
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
