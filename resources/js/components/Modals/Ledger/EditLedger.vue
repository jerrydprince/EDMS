<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-gray-100 rounded-2xl mx-2 md:mx-0' },
    header: { class: 'hidden' },
    content: { class: 'px-0 bg-gray-100 w-[100%] m-auto rounded-t-2xl' },
    footer: { class: 'bg-gray-100 rounded-2xl' },
  }" :style="{ width: '32rem', padding: '0px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <section class="max-h-[80vh] overflow-y-scroll hide_scroll flex flex-col items-center">
      <div class="items-center justify-start flex border-b border-solid border-[#E6E6E6] w-full">
        <img src="../../../assets/images/ledgericon.svg" alt="reject task" class="-ml-2 -mr-2 md:mr-0" />
        <div class="-mt-4">
          <p class="text-lg font-semibold text-gray-800">
            Create New Ledger Entry
          </p>
          <p class="text-xs md:text-sm text-gray-500 pr-6 md:pr-0">
            Fill in the details below to record a new transaction in the ledger.
          </p>
        </div>
      </div>
      <!-- THE FORM GOES HERE -->
      <section class="w-full px-5">
        <!-- <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Ledger Reference</p>
          </div>
          <div>
            <input v-model="form.ref" type="text" placeholder="REF002"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div> -->
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Set Due Date</p>
          </div>
          <div>
            <input v-model="form.due_date" type="datetime-local" placeholder="Add Comments"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Narration</p>
          </div>
          <div>
            <textarea v-model="form.narration" rows="3" style="resize: none" placeholder="Describe the transaction"
              class="px-3 w-full py-2 rounded-lg focus:outline-none text-sm drop-shadow-sm border border-solid border-gray-300"></textarea>
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Debit (DR)</p>
          </div>
          <div>
            <input v-model="form.debit" type="text" placeholder="Enter Amount"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Credit (CR)</p>
          </div>
          <div>
            <input v-model="form.credit" type="text" placeholder="Enter Amount"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Balance</p>
          </div>
          <div>
            <input v-model="form.balance" type="text" placeholder="Enter Amount"
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm border border-solid border-gray-300" />
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 font-normal mb-2 text-sm">
            <p class="">Workflow</p>
          </div>
          <div class="">
            <DropdownComp  v-model="form.workflow" :items="workflowData" />
          </div>
        </div>
      </section>
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-3 mt-5 py-5 pr-7 border-t border-dashed border-[#E6E6E6]">
        <button @click="cancelModal" class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-10 bg-white"
          label="Cancel">
          Cancel
        </button>

        <button @click="createNewLedgerEntry"
          class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10" :disabled="!isFormValid"
          severity="secondary" outlined>
          Save
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed, onMounted, defineProps, watch } from "vue";
import { useToast } from "primevue/usetoast";
import Dropdown from "primevue/dropdown";
import { useForm } from "@inertiajs/vue3";

import DropdownComp from "../../DropdownComp.vue";

const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const dropdownModal = ref(false);
const selectedIndex = ref(null);
const workflowData = ref([]);
const props = defineProps(['ledger']);
const workflow_id = ref(null);
const createNewLedgerEntry = () => {

  form.post("/finance-and-accounts/create-ledger-entry", {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Ledger Entry Created",
        detail: "The new ledger entry has been successfully created.",
        life: 3000,
      });
      emit("confirmed");
    },
    onError: (errors) => {
      toast.add({
        severity: "error",
        summary: "Error Creating Entry",
        detail: errors.message || "There was an error creating the ledger entry.",
        life: 3000,
      });
    },
  });
};


// const workflowData = ref([
//   { name: "Lagos Rail Expansion Payment" },
//   { name: "Lekki Toll Infrastructure Payment" },
//   { name: "State Road Rehabilitation Fund" },
//   { name: "Public Transport Upgrade Allocation" },
//   { name: "Lagos Rail Expansion Payment" },
//   { name: "State Road Rehabilitation Fund" },
// ]);

const updateWorkflow = (param, index) => {
  form.value.workflow = param;
  selectedIndex.value = index;
  dropdownModal.value = false;
};

const form = useForm({
  due_date: "",
  narration: "",
  debit: "",
  credit: "",
  balance: "",
  workflow: "",
  id: null
});

const getCurrentWorkflow = (id)=>{
    return workflowData.value.filter((item)=>item.id == id);
}

watch(() => props.ledger, (newLedger) => {
  if (newLedger) {
    workflow_id.value = newLedger.workflow || null;
    form.due_date = newLedger.due_date || "";
    form.narration = newLedger.narration || "";
    form.debit = newLedger.debit || "";
    form.credit = newLedger.credit || "";
    form.balance = newLedger.balance || "";
    form.workflow = getCurrentWorkflow(newLedger.workflow) || "";
    form.id = newLedger.id || null;
  }
}, { immediate: true });

// const isFormValid = computed(() => {
//   return Object.values(form.data()).every(val => typeof val === "string" ? val.trim() !== "" : val !== null && val !== undefined && val !== "");
// });
const isFormValid = computed(() => {
  return (
    form.due_date.trim() !== "" &&
    form.narration.trim() !== "" &&
    form.debit !== "" &&
    form.credit !== "" &&
    form.balance !== "" &&
    form.workflow !== ""
  )
})

const getWorkflow = async () => {
  try {
    const response = await axios.get("/finance-and-accounts/get-workflow");
    workflowData.value = response.data;
    if(workflow_id.value) {
      const selectedWorkflow = workflowData.value.find(w => w.id == workflow_id.value);
      console.log('selectedWorkflow', selectedWorkflow);
      if (selectedWorkflow) {
        form.workflow = selectedWorkflow;
      }
    }
  } catch (error) {
    console.error("Error fetching workflow data:", error);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Failed to load workflow data.",
      life: 3000,
    });
  }
};

const cancelModal = () => {
  emit("cancelled");
};

onMounted(() => {
  getWorkflow();
});
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>
