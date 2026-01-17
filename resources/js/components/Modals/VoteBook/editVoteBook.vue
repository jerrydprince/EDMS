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
          :src="votebookIcon"
          alt="reject task"
          class="-ml-2 drop-shadow-lg"
        />
        <div class="-mt-5 ml-6">
          <p class="text-2xl font-semibold text-gray-800">Edit Vote</p>
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
              v-model="form.name"
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
              v-model="form.account_number"
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
              v-model="form.gifmis_code"
              type="text"
              placeholder="Enter GIFMIS Code"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300"
            />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Account type</p>
          </div>
          <div class="">
            <DropdownComp v-model="form.account_type" :items="accountType" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Date Created</p>
          </div>
          <div>
            <input
              v-model="form.date_created"
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
          Edit
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed, defineProps, watch } from "vue";
import { useToast } from "primevue/usetoast";
import DropdownComp from "../../DropdownComp.vue";
import { useForm } from "@inertiajs/vue3";
import votebookIcon from "../../../../../public/images/votebook.svg";

const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled", "refreshVoteBook"]);
const visible = ref(true);
const props = defineProps(['selectedVoteBook'])
const addNewVote = () => {
  form.post('/finance-and-accounts/create-vote-book', {onSuccess:()=>{
    toast.add({
      severity: "success",
      summary: "Vote successfully edited!",
      detail: "Votebook was successfully edited",
      life: 3000,
    });
  emit("cancelled");
  emit('refreshVoteBook')
  },
  onError: ()=>{
    toast.add({
      severity: "error",
      summary: "Error!",
      detail: "Failed to create votebook.",
      life: 3000,
    });
  }
})
};

const form = useForm({
  name: "",
  account_number: "",
  gifmis_code: "",
  date_created: new Date(),
  account_type: "",
  balance: "",
  id: null
})

const accountType = ref([
  {name: 'Select Account'},
  { name: "Standard Account" },
  { name: "Special Account" },
]);

const isFormValid = computed(() => {
  return (
    form.name.trim() !== "" &&
    form.date_created !== "" &&
    form.gifmis_code.trim() !== "" &&
    form.account_number.trim() !== "" &&
    form.account_type !== "" &&
    form.account_type !== "Select Account" &&
    form.balance.trim() !== ""
  );
});

const cancelModal = () => {
  emit("cancelled");
};

watch(()=>props.selectedVoteBook, (newVal)=>{
  if(newVal){
    form.name = newVal.name;
    form.date_created = newVal.date_created;
    form.gifmis_code = newVal.gifmis_code;
    form.account_number = newVal.account_number
    form.account_type = accountType.value.find((item)=> item.name.toLowerCase().includes(newVal.account_type.toLowerCase()))
    form.balance = newVal.balance;
    form.id = newVal.id
  }
}, {immediate: true})
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none; /* Hide the header */
}
</style>
