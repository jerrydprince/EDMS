<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-gray-100 rounded-2xl mx-2 md:mx-0' },
    header: { class: 'bg-gray-100 pb-6 rounded-2xl' },
    content: { class: 'px-0 bg-gray-100 pb-6 w-[90%] m-auto' },
    footer: { class: 'bg-gray-100 rounded-2xl' },
  }" :style="{ width: '30rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <section class="max-h-[75vh] overflow-y-scroll hide_scroll flex flex-col items-center">
      <div class="items-center justify-center flex">
        <img :src="accepttask" alt="accept task" />
      </div>
      <div class="text-center border-b border-solid border-[#E6E6E6] pb-4">
        <p class="text-2xl mt-3 mb-5 font-semibold text-gray-800">
          Accept Task?
        </p>
        <p class="text-sm text-center text-gray-500">
          By accepting this task, the task will be passed down for further
          review and processing.
        </p>
      </div>
      <!-- THE FORM GOES HERE -->
      <section>
        <div class="self-start mt-5">
          <div class="text-gray-700 flex gap-1 items-center font-normal mb-2 ml-1 text-sm">
            <Icon icon="hugeicons:quill-write-01" width="18" />
            <p class="">General Comments</p>
          </div>
          <div>
            <textarea v-model="form.generalComment" rows="3" placeholder="Add Comments"
              class="w-[23rem] md:w-[27rem] px-3 py-2 rounded-lg border border-gray-200 focus:outline-none text-sm drop-shadow-sm"></textarea>
          </div>
        </div>
        <div class="self-start mt-5">
          <div class="text-gray-700 flex gap-1 items-center font-normal mb-2 ml-1 text-sm">
            <Icon icon="uil:calender" width="18" />
            <p class="">Set Due Date</p>
          </div>
          <div>
            <input v-model="form.dueDate" type="datetime-local" placeholder=""
              class="py-2 px-3 rounded-lg focus:outline-none text-sm w-full text-gray-500 drop-shadow-sm" />
          </div>
        </div>
        <!-- Recipient Selection is based on Current Role - [Deputy Director or Assistant Director] -->
        <div class="my-4">
          <!-- Deputy Director Authorization -->
          <div class="w-full" v-if="roles.includes('division heads')">
            <p class="text-gray-700 text-sm">Select Recipient</p>
            <div
              class="bg-gray-100 rounded-2xl py-3 flex flex-col justify-between items-start gap-y-2 h-[170px] overflow-y-scroll hide_scroll">
              <!-- Assistant Directors -->
              <div v-for="(assistant, index) in assistantDirectors" :key="index" class="w-full">
                <div class="bg-white p-4 rounded-lg flex items-center gap-x-6">
                  <Checkbox v-model="form.recipient" :value="assistant.id" class="" />
                  <div>
                    <p class="text-sm">
                      Assistant Director of
                      <span class="capitalize">{{ assistant?.division.title }}[{{ assistant?.email }}]</span>
                    </p>
                    <p class="text-xs text-gray-600">{{ assistant.name }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Assistant Director Authorization -->
          <div v-if="roles.includes('unit heads')" class="self-start mt-5">
            <div class="text-gray-700 flex gap-1 items-center font-normal mb-4 ml-1 text-sm">
              <Icon icon="lucide:user" width="18" />
              <p class="">Select Recipient</p>
            </div>
            <div class="flex gap-x-3">
              <div v-if="hos_advanced_section" @click="selectRecipient('advanceSection')" :class="[
                'flex flex-col justify-center items-center text-gray-700 h-32 w-full bg-white rounded-2xl gap-y-4 border relative',
                recipient === 'advanceSection'
                  ? 'border-primary drop-shadow-lg'
                  : 'border-transparent drop-shadow-sm',
              ]">
                <Icon icon="mingcute:settings-1-line" width="25" />
                <p class="">Advance Section</p>
                <img v-if="recipient === 'advanceSection'" :src="checkBox"
                  class="absolute top-3 right-3" />
              </div>
              <div v-if="hos_other_charges" @click="selectRecipient('otherCharges')" :class="[
                'flex flex-col justify-center items-center text-gray-700 h-32 w-full bg-white rounded-2xl gap-y-4 border relative',
                recipient === 'otherCharges'
                  ? 'border-primary drop-shadow-lg'
                  : 'border-transparent drop-shadow-sm',
              ]">
                <Icon icon="ri:receipt-line" width="25" />
                <p class="">Other Charges</p>
                <img v-if="recipient === 'otherCharges'" :src="checkBox"
                  class="absolute top-3 right-3" />
              </div>
            </div>
          </div>
          <!-- Advance Section Authorization -->
          <div v-if="roles.includes('advance section(HOS)')" class="w-full mt-4">
            <p class="text-gray-700 text-sm">Select Staff</p>
            <div class="bg-white px-3 mt-2 rounded-2xl py-3 min-h-[150px]">
              <div
                class="flex flex-col justify-between items-start gap-y-2 max-h-full h-auto overflow-y-scroll hide_scroll rounded-2xl py-2">
                <!-- Search Filter -->
                <input type="search" v-model="searchStaffValue"
                  class="outline-primary/60 border border-solid border-gray-300 rounded-md w-full px-3 py-2 text-xs"
                  placeholder="Search Staff List" />
                <!-- Staff List -->
                <div v-for="(staff, index) in filteredAdvanceSection" :key="index" v-if="filteredAdvanceSection.length"
                  class="w-full">
                  <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-x-6">
                    <Checkbox :value="staff.id" v-model="form3.recipient" class="" />
                    <div>
                      <p class="text-xs text-gray-600">{{ staff.last_name+" "+staff.first_name }}</p>
                    </div>
                  </div>
                </div>
                <p v-else class="text-sm text-center w-full text-gray-700 py-8">
                  No staff found!
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </section>

    <template #footer>
      <div class="flex items-center justify-end gap-3 mt-5 pb-2 pr-7">
        <button @click="cancelModal" class="py-2 w-auto border-gray-200 border border-solid rounded-lg px-10 bg-white"
          label="Cancel">
          Cancel
        </button>

        <button @click="acceptTask" class="bg-[#0D6E1A] w-auto py-2 text-white rounded-lg disabled:opacity-70 px-10"
          :disabled="!isFormValid" severity="secondary" outlined>
          Proceed
        </button>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, ref, computed, defineProps, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import accepttask from "../../../assets/images/accepttask.svg";
import { useForm, usePage } from "@inertiajs/vue3";
import checkBox from "../../../assets/images/checkBox.svg";
import axios from "axios";

const props = defineProps(['paymentId']);
const page = usePage();
const roles = computed(() => page.props.roles);
const toast = useToast();
const emit = defineEmits(["confirmed", "cancelled"]);
const visible = ref(true);
const searchStaffValue = ref("");
const hos_advanced_section = ref();
const hos_other_charges = ref();
const recipient = ref()
const assistantDirectors = ref([]);
const advance_section_users = ref([])

const form = useForm({
  generalComment: "",
  dueDate: "",
  recipient: "",
  payment_id: props.paymentId,
});

//for unit heads
const form2 = useForm({
  generalComment: "",
  dueDate: "",
  recipient: "",
  payment_id: props.paymentId,
});

//for advance section heads
const form3 = useForm({
  generalComment: "",
  dueDate: "",
  recipient: "",
  payment_id: props.paymentId
})

const assignAdvanceOtherCharges = ()=>{
  if(form2.generalComment.trim() === ""){
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please add a general comment.",
      life: 3000,
    });
    return;
  } else if (form2.dueDate.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please set a due date.",
      life: 3000,
    });
    return;
  }else if (form2.recipient === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please select a recipient.",
      life: 3000,
    });
    return;
  }

  form2.post('/finance-and-accounts/assign-to-advance-other-section', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Task Accepted",
        detail: "The file has been passed to the Advance section.",
        life: 3000,
      });
      emit("cancelled");
    },
    onError: (error) => {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: error.response.data.message || "An error occurred while accepting the task.",
        life: 3000,
      });
    },
  });
}

const assignToUnit = ()=>{
   if (form.recipient === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please select a recipient.",
      life: 3000,
    });
    return;
  } else if (form.generalComment.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please add a general comment.",
      life: 3000,
    });
    return;
  } else if (form.dueDate.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please set a due date.",
      life: 3000,
    });
    return;
  }

  form.post('/finance-and-accounts/accept-division-payment', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Task Accepted",
        detail: "The file has been passed to the Advance section.",
        life: 3000,
      });
      emit("cancelled");
    },
    onError: (error) => {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: error.response.data.message || "An error occurred while accepting the task.",
        life: 3000,
      });
    },
  });
}


const assignToAdvanceSection = ()=>{
  form3.generalComment = form.generalComment
  form3.dueDate = form.dueDate

   if (form3.recipient === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please select a recipient.",
      life: 3000,
    });
    return;
  } else if (form3.generalComment.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please add a general comment.",
      life: 3000,
    });
    return;
  } else if (form3.dueDate.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please set a due date.",
      life: 3000,
    });
    return;
  }

  form3.post('/finance-and-accounts/assign-to-advance-section', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Task Accepted",
        detail: "The file has been passed to the Advance section.",
        life: 3000,
      });
      emit("cancelled");
    },
    onError: (error) => {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: error.response.data.message || "An error occurred while accepting the task.",
        life: 3000,
      });
    },
  });
}

const currentRole = ref("Advance Section");
// Roles - Deputy Director, Assistant Director, Advance Section
const acceptTask = () => {
  if(roles.value.includes('division heads')){
    assignToUnit();
  }

  if(roles.value.includes('unit heads')){
    form2.recipient = recipient.value =="advanceSection"? hos_advanced_section.value.id: hos_other_charges.value.id
    form2.generalComment = form.generalComment
    form2.dueDate = form.dueDate
    assignAdvanceOtherCharges();
  }

  if(roles.value.includes('advance section(HOS)')){
    assignToAdvanceSection()
  }

  // if(roles.value.includes('advance section') || roles.includes('other charges')){
  //   assignAdvanceOtherCharges();
  // }
};

const isFormValid = computed(() => {
  return (
    form.generalComment.trim() !== "" && form.dueDate.trim() !== ""
  );
});

const selectRecipient = (value) => {
  recipient.value = value;
};

const getAdvancedSectionAndOtherChargesUsers = async ()=>{
  await axios.get('/finance-and-accounts/get-advanced-section-and-other-charges-users').then((response)=>{
    hos_advanced_section.value = response.data.advance_section_hos
    hos_other_charges.value = response.data.other_charges_hos
  })
}

const getAdvanceSectionUser = async ()=>{
  await axios.get('/finance-and-accounts/get-advance-section-users').then((response)=>{
    advance_section_users.value = response.data;
  })
}

const getUnitHeads = async () => {
  try {
    const response = await axios.get('/finance-and-accounts/get-unit-heads');
    assistantDirectors.value = response.data;
  } catch (error) {
    console.error("Error fetching unit heads:", error);
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Failed to fetch unit heads.",
      life: 3000,
    });
  }
};

const staffList = ref([
  {
    name: "Adebayo Adebanjo",
  },
  {
    name: "Ifeoluwa Akinbiyi",
  },
  {
    name: "Olamide Aluko",
  },
  {
    name: "Deva Aluko",
  },
  {
    name: "James Miye",
  },
]);

const filteredStaffList = computed(() => {
  return staffList.value.filter((item) =>
    item?.name.toLowerCase().includes(searchStaffValue.value.toLowerCase())
  );
});

const filteredAdvanceSection = computed(()=>{
  return advance_section_users.value.filter((item)=>{
    return (
      item?.first_name?.toLowerCase().includes(searchStaffValue.value.toLowerCase()) ||
      item?.last_name?.toLowerCase().includes(searchStaffValue.value.toLowerCase())
    )
  })
})

const cancelModal = () => {
  emit("cancelled");
};

onMounted(() => {
  getUnitHeads();
  if(roles.value.includes('unit heads')){
    getAdvancedSectionAndOtherChargesUsers()
  }
  if(roles.value.includes('advance section(HOS)')){
    getAdvanceSectionUser()
  }
});
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>
