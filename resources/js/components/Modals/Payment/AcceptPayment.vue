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
        <div class="self-start mt-5" v-if="!roles.includes('advance section')">
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
                      <span class="capitalize">{{ assistant?.unit?.title }} [{{ assistant?.email }}]</span>
                    </p>
                    <p class="text-xs text-gray-600">{{ assistant?.name }}</p>
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
          <!-- Advance Section  HOS forward task -->
          <div v-if="roles.includes('advance section(HOS)') && assignTask" class="w-full mt-4">
            <p class="text-gray-700 text-sm">Select Staff</p>
            <div class="bg-white px-3 mt-2 rounded-2xl py-3 min-h-[150px]">
              <div
                class="flex flex-col justify-between items-start gap-y-2 max-h-full h-auto overflow-y-scroll hide_scroll rounded-2xl py-2">
                <!-- Search Filter -->
                <input type="search" v-model="searchStaffValue"
                  class="outline-primary/60 border border-solid border-gray-300 rounded-md w-full px-3 py-2 text-xs"
                  placeholder="Search Staff List" />
                <!-- Staff List -->
                 <div v-if="filteredAdvanceSection.length" class="w-full">
                    <div v-for="(staff, index) in filteredAdvanceSection" :key="index" 
                    class="w-full">
                    <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-x-6">
                      <Checkbox :value="staff.id" v-model="form.recipient" class="" />
                      <div>
                        <p class="text-xs text-gray-600">{{ staff.last_name+" "+staff.first_name }}</p>
                      </div>
                    </div>
                  </div>
                 </div>
                <p v-else class="text-sm text-center w-full text-gray-700 py-8">
                  No staff found!
                </p>
              </div>
            </div>
          </div>

          <!-- Other charge HOS forward task -->
          <div v-if="roles.includes('other charges(HOS)') && assignTask" class="w-full mt-4">
            <p class="text-gray-700 text-sm">Select Staff</p>
            <div class="bg-white px-3 mt-2 rounded-2xl py-3 min-h-[150px]">
              <div
                class="flex flex-col justify-between items-start gap-y-2 max-h-full h-auto overflow-y-scroll hide_scroll rounded-2xl py-2">
                <!-- Search Filter -->
                <input type="search" v-model="searchStaffValue"
                  class="outline-primary/60 border border-solid border-gray-300 rounded-md w-full px-3 py-2 text-xs"
                  placeholder="Search Staff List" />
                <!-- Staff List -->
                <div v-if="filteredOtherChargesSection.length" class="w-full">
                  <div v-for="(staff, index) in filteredOtherChargesSection" :key="index" class="w-full">
                    <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-x-6">
                      <Checkbox :value="staff.id" v-model="form.recipient" class="" />
                      <div>
                        <p class="text-xs text-gray-600">{{ staff.last_name+" "+staff.first_name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <p v-else class="text-sm text-center w-full text-gray-700 py-8">
                  No staff found!
                </p>
              </div>
            </div>
          </div>

          <!-- Advance to other charges -->
          <div v-if="roles.includes('advance section(HOS)') && !assignTask" class="self-start mt-5">
             <p class="text-gray-700 text-sm">Select Recipient</p>
            <div class="bg-white px-3 mt-2 rounded-2xl py-3 min-h-[150px]">
              <div
                class="flex flex-col justify-between items-start gap-y-2 max-h-full h-auto overflow-y-scroll hide_scroll rounded-2xl py-2">
                <div class="w-full">
                  <div class="w-full" v-if="hos_other_charges">
                    <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-x-6">
                      <Checkbox :value="hos_other_charges.id" v-model="form.recipient" class="" />
                      <div>
                        <p class="text-xs text-gray-600">{{ hos_other_charges.last_name+" "+hos_other_charges.first_name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- other charges, checking, cpo, internal audit and final account hos task forwarding to other hos -->
          <div v-if="(roles.includes('other charges(HOS)') || roles.includes('checking(HOS)') || roles.includes('CPO(HOS)') || roles.includes('internal audit(HOS)') || roles.includes('final account(HOS)') ) && !assignTask" class="self-start mt-5">
            <!-- <div v-show="false" class="text-gray-700 flex gap-1 items-center font-normal mb-4 ml-1 text-sm">
              <Icon icon="lucide:user" width="18" />
              <p class="">Select Recipient</p>
            </div>
            <div class="flex gap-x-3">
              <div @click="selectRecipient('advanceSection')" :class="[
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
            </div> -->
             <p class="text-gray-700 text-sm">Select Recipient</p>
            <div class="bg-white px-3 mt-2 rounded-2xl py-3 min-h-[150px]">
              <div
                class="flex flex-col justify-between items-start gap-y-2 max-h-full h-auto overflow-y-scroll hide_scroll rounded-2xl py-2">
                <div class="w-full">
                  <div class="w-full" v-if="hos">
                    <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-x-6">
                      <Checkbox :value="hos.id" v-model="form.recipient" class="" />
                      <div>
                        <p class="text-xs text-gray-600">{{ hos.last_name+" "+hos.first_name }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- cpo Section Authorization  -->
          <div v-if="( roles.includes('checking(HOS)') || roles.includes('CPO(HOS)') || roles.includes('internal audit(HOS)') || roles.includes('final account(HOS)') ) && assignTask" class="w-full mt-4">
            <p class="text-gray-700 text-sm">Select Staff</p>
            <div class="bg-white px-3 mt-2 rounded-2xl py-3 min-h-[150px]">
              <div
                class="flex flex-col justify-between items-start gap-y-2 max-h-full h-auto overflow-y-scroll hide_scroll rounded-2xl py-2">
                <!-- Search Filter -->
                <input type="search" v-model="sectionStaff"
                  class="outline-primary/60 border border-solid border-gray-300 rounded-md w-full px-3 py-2 text-xs"
                  placeholder="Search Staff List" />
                <!-- Staff List -->
                <div v-for="(staff, index) in filterSectionUsers" :key="index" v-if="filterSectionUsers.length"
                  class="w-full">
                  <div class="bg-gray-100 p-4 rounded-lg flex items-center gap-x-6">
                    <Checkbox :value="staff.id" v-model="form.recipient" class="" />
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
import { defineEmits, ref, computed, defineProps, onMounted, watch } from "vue";
import { useToast } from "primevue/usetoast";
import accepttask from "../../../assets/images/accepttask.svg";
import { useForm, usePage } from "@inertiajs/vue3";
import checkBox from "../../../assets/images/Checkbox.svg";
import axios from "axios";

const props = defineProps(['paymentId', 'payment', 'assignTask']);
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
const checking = ref([])
const checkingStaff = ref('')
const other_charges = ref([])
const hos = ref()
const section_users = ref([])
const sectionStaff = ref('')
//const otherChargesHOS = ref(null)

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

//for advance section
const form4 = useForm({
  generalComment: "",
  dueDate: "",
  recipient: "",
  payment_id: props.paymentId
})

//submit back to the advance section hos
const form5 = useForm({
  generalComment: form.generalComment,
  dueDate: "",
  recipient:"",
  payment_id: props.paymentId
})

//assign task form by other charges hos
const form6 = useForm({
  generalComment: form.generalComment,
  dueDate: "",
  recipient:"",
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

const assignToHos = ()=>{
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
}

const assignToOtherChargesByHOS = ()=>{
  form6.generalComment = form.generalComment  
  form6.dueDate = form.dueDate
  form6.recipient = form.recipient

  if(form6.generalComment.trim() === ""){
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please add a general comment.",
      life: 3000,
    });
    return;
  } else if (form6.dueDate.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please set a due date.",
      life: 3000,
    });
    return;
  }else if (form6.recipient === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please select a recipient.",
      life: 3000,
    });
    return;
  }

  form6.post('/finance-and-accounts/assign-to-other-charges-by-hos', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Task Accepted",
        detail: "The file has been passed to the Other charges section.",
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
  form3.recipient = form.recipient

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

const assignToChecking = ()=>{
  form4.generalComment = form.generalComment
  form4.dueDate = form.dueDate

   if (form4.recipient === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please select a recipient.",
      life: 3000,
    });
    return;
  } else if (form4.generalComment.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please add a general comment.",
      life: 3000,
    });
    return;
  } else if (form4.dueDate.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please set a due date.",
      life: 3000,
    });
    return;
  }

  form4.post('/finance-and-accounts/assign-to-checking', {
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

const assignToOtherCharges = ()=>{
  form5.generalComment = form.generalComment
  form5.recipient = hos_other_charges.value.id
  form.recipient = hos_other_charges.value.id

   if (form5.recipient === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please select a recipient.",
      life: 3000,
    });
    return;
  } else if (form5.generalComment.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please add a general comment.",
      life: 3000,
    });
    return;
  } else if (form5.dueDate.trim() === "") {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please set a due date.",
      life: 3000,
    });
    return;
  }

  form5.post('/finance-and-accounts/assign-to-advance-other-section', {
    onSuccess: () => {
      toast.add({
        severity: "success",
        summary: "Task Accepted",
        detail: "The file has been passed to the Other section.",
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

const assignToSectionUsers = ()=>{
form.recipient = form4.recipient? form4.recipient: form.recipient

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

  form.post('/finance-and-accounts/assign-to-section-users', {
    onSuccess: () => {
      console.log('i am successful')
      toast.add({
        severity: "success",
        summary: "Task Accepted",
        detail: "The file has been sent.",
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
    if(props.assignTask == true){
      assignToAdvanceSection()
    }else{
      assignToOtherCharges()
    }
  }

  if(roles.value.includes('advance section')){
    assignToChecking()
  }

  if(roles.value.includes('other charges(HOS)')){
    assignToOtherChargesByHOS()
  }

  if(roles.value.includes('checking(HOS)') || roles.value.includes('CPO(HOS)') || roles.value.includes('internal audit(HOS)') || roles.value.includes('final account(HOS)')){
   
      assignToSectionUsers()
    
  }

  // if(roles.value.includes('advance section') || roles.includes('other charges')){
  //   assignAdvanceOtherCharges();
  // }
};

// const isFormValid = computed(() => {
//   return (
//     form.generalComment.trim() !== "" && form.dueDate.trim() !== ""
//   );
// });
const isFormValid = computed(()=>{
  if(!roles.value.includes('advance section')){
    return (
      form.generalComment.trim() !== "" &&
      form.dueDate !== "" &&
      form.recipient !== ""
    )
  }else{
    return (
      form.generalComment.trim() !==""
    )
  }
})

const selectRecipient = (value) => {
  recipient.value = value;
  form2.recipient = recipient.value =="advanceSection"? hos_advanced_section.value.id: hos_other_charges.value.id
  form.recipient = recipient.value =="advanceSection"? hos_advanced_section.value.id: hos_other_charges.value.id
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

const getOtherChargesHos = async ()=>{
  await axios.get('/finance-and-accounts/get-other-charges-hos').then((response)=>{
    hos_other_charges.value = response.data;
  })
}

const getOtherCharges = async ()=>{
  await axios.get('/finance-and-accounts/get-other-charges-users').then((response)=>{
    other_charges.value = response.data;
  })
}

const getCheckingUsers = async ()=>{
  await axios.get('/finance-and-accounts/get-checking-users').then((response)=>{
    checking.value = response.data;
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

// const getHOS = async(role)=>{
//   await axios.get('/finance-and-accounts/get-hos',{params:{role: role}}).then((response)=>{
//     hos.value = response.data
//   })
// }

const getSectionUsers = async(role)=>{
  if(role.includes("(HOS)")){
    await axios.get('/finance-and-accounts/get-section-users',{params:{role: role}}).then((response)=>{
      hos.value = response.data
    })
  }else{
    await axios.get('/finance-and-accounts/get-section-users',{params:{role: role}}).then((response)=>{
      section_users.value = response.data
    })
  }
}

const filteredAdvanceSection = computed(()=>{
  return advance_section_users.value.filter((item)=>{
    return (
      item?.first_name?.toLowerCase().includes(searchStaffValue.value.toLowerCase()) ||
      item?.last_name?.toLowerCase().includes(searchStaffValue.value.toLowerCase())
    )
  })
})

const filteredOtherChargesSection = computed(()=>{
  return other_charges.value.filter((item)=>{
    return (
      item?.first_name?.toLowerCase().includes(searchStaffValue.value.toLowerCase()) ||
      item?.last_name?.toLowerCase().includes(searchStaffValue.value.toLowerCase())
    )
  })
})

const filterCheckingUsers = computed(()=>{
  return checking.value.filter((item)=>{
    return (
      item?.first_name?.toLowerCase().includes(checkingStaff.value.toLowerCase()) ||
      item?.last_name?.toLowerCase().includes(checkingStaff.value.toLowerCase())
    )
  })
})

const filterSectionUsers = computed(()=>{
  return section_users.value.filter((item)=>{
    return (
      item?.first_name?.toLowerCase().includes(sectionStaff.value.toLowerCase()) ||
      item?.last_name?.toLowerCase().includes(sectionStaff.value.toLowerCase())
    )
  })
})

const cancelModal = () => {
  emit("cancelled");
};

watch(()=>form.dueDate, ()=>{
  //watch dueDate and set recipient only when the advance section hos wants to submit
  if(roles.value.includes('advance section(HOS)')){
    //form5.recipient = hos_other_charges.value?.id
    //form.recipient.push(hos_other_charges.value?.id)
    form5.generalComment = form.generalComment
    form5.dueDate = form.dueDate
  }
}, {immediate: true})

onMounted(() => {
  getUnitHeads();
  if(roles.value.includes('unit heads')){
    getAdvancedSectionAndOtherChargesUsers()
  }
  if(roles.value.includes('advance section(HOS)')){
    getAdvanceSectionUser()
    getOtherChargesHos()
  }

  if(roles.value.includes('advance section')){
    //getCheckingUsers()
    getAdvanceSectionUser()
  }

  if(roles.value.includes('other charges(HOS)')){
    getOtherCharges()
    getSectionUsers('checking(HOS)')
  }

  if(roles.value.includes('checking(HOS)')){
    getSectionUsers('internal audit(HOS)')
    getSectionUsers('checking')
  }

  if(roles.value.includes('internal audit(HOS)')){
    getSectionUsers('CPO(HOS)')
    getSectionUsers('internal audit')
  }

  if(roles.value.includes('CPO(HOS)')){
    getSectionUsers('final account(HOS)')
    getSectionUsers('CPO')
  }
  if(roles.value.includes('final account(HOS)')){
    getSectionUsers('final account')
  }
});
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  /* Hide the header */
}
</style>
