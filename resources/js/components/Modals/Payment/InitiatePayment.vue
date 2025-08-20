<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="w-full flex space-between items-start">
        <div class="w-[100%] flex flex-col items-start justify-center">
          <p class="text-lg font-bold">Initiate New Payment</p>
          <p class="text-gray-500 text-sm mt-1">
            Fill in the details to start a new payment initiation.
          </p>
        </div>
      </div>
    </template>
    <Divider class="-mt-1 mb-8" />
    <section class="px-7 h-[50vh] overflow-y-auto hide_scroll">
      <div>
        <!-- Payment Form here -->
        <div>
          <div class="w-full">
            <label for="Title" class="text-gray-700 text-sm">Payment Title <span class="text-red-500">*</span></label>
            <br />
            <input type="text" v-model="form.title"
              class="outline-none border border-solid border-gray-300 rounded-md w-full p-3 mt-2"
              placeholder="Task title" />
              <span class=" text-sm text-red-400" v-if="form.errors.title">{{ form.errors.title }}</span>
          </div>
          <div class="w-full mt-4">
            <label for="Date" class="text-gray-700 text-sm">Set Due Date <span class="text-red-500">*</span></label>
            <br />
            <input type="datetime-local" v-model="form.dueDate"
              class="outline-none border border-solid border-gray-300 rounded-md w-full p-3 mt-2" />
              <span class=" text-sm text-red-400" v-if="form.errors.dueDate">{{ form.errors.dueDate }}</span>
          </div>
          <div class="mt-4">
            <label for="" class="text-gray-700 text-sm">General Comments</label>
            <br />
            <textarea name="" id="" cols="20" rows="4" v-model="form.comments"
              class="p-3 outline-none border border-solid border-gray-300 rounded-md resize-none w-full mt-2"></textarea>
          </div>
          <section class="mt-4">
            <p class="text-gray-700 text-sm">Select Recipient <span class="text-red-500">*</span></p>
            <span class=" text-sm text-red-400" v-if="form.errors.recipient">{{ form.errors.recipient }}</span>
            <div
              class="bg-gray-100 rounded-2xl mt-2 p-3 flex flex-col justify-between items-start gap-y-2 min-h-[100px] max-h-[300px] overflow-y-scroll hide_scroll">
              <!-- Loop for the directors assigned start -->
              <div v-for="(deputy, index) in divisionHeads" :key="index" class="w-full">
                <div class="bg-white p-4 rounded-lg flex items-center gap-x-6">
                  <Checkbox
                    :value="getValue(deputy.id)"
                    v-model="form.recipient"
                    class=""
                  />
                  <div>
                    <p class="text-sm">
                     {{ deputy.last_name+ " "+deputy.first_name }}
                      <span class="capitalize">{{ deputy.department }}</span>
                    </p>
                    <p class="text-xs text-gray-600">{{ deputy.name }}</p>
                  </div>
                </div>
              </div>
              <!-- Loop for directors end -->
            </div>
          </section>
          <!-- Attached files Section -->
          <section class="mt-4">
            <p class="text-gray-700 text-sm">Attached Document <span class="text-red-500">*</span></p>
            <span class=" text-sm text-red-400" v-if="form.errors.documents">{{ form.errors.documents }}</span>
            <div
              class="bg-gray-100 rounded-2xl mt-2 p-3 flex flex-col justify-between items-start gap-y-2 min-h-[100px] max-h-[300px] overflow-y-auto hide_scroll">
              <!-- Loop for the directors assigned start -->
              <div v-for="(file, index) in selectedFiles" :key="index"
                class="w-full bg-white p-4 rounded-lg flex justify-between items-center">
                <div class="flex items-center gap-x-4">
                  <Icon :icon="getFileIcon(file.file_name)" width="30" class="" />
                  <div>
                    <p class="text-sm mb-1">
                      <span class="capitalize text-gray-700">{{
                        file.file_name.split('/').pop()
                      }}</span>
                    </p>
                    <p class="text-xs text-gray-400">{{ file.size }}</p>
                  </div>
                </div>
                <div class="bg-gray-100 rounded-full p-1 cursor-pointer">
                  <Icon icon="prime:times" width="20" @click="removeFile(file)" />
                </div>
              </div>
              <!-- Loop for directors end -->
            </div>
          </section>
        </div>
      </div>
    </section>
    <Divider class="-mb-2" />
    <template #footer>
      <div class="flex justify-end items-center w-full">
        <div>
          <Button @click="closeModal" label="Cancel" class="mr-3 cursor-pointer" :pt="{
            root: {
              class:
                'py-2 px-8 border border-gray-300 text-gray-700 bg-white font-light rounded-lg',
            },
          }" />
          <Button @click="submit" label="Create" :disabled="false" class="ml-3 disabled:opacity-40 cursor-pointer" :pt="{
            root: {
              class:
                'py-2 px-8 border border-gray-300 text-white bg-primary font-light rounded-lg',
            },
          }" />
        </div>
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, watch, onMounted } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm } from "@inertiajs/vue3";

const props = defineProps(["modelValue", "selectedFiles", "payment"]);
const emit = defineEmits(["update:modelValue", 'removeF']);
const selectedIndex = ref(null);
const form = useForm({
  payment_id: null,
  title: "",
  dueDate: "",
  comments: "",
  recipient: null,
  documents: [],
});

const toast = useToast();
const searchInput = ref("");
const Suggestions = ref([]);
const selectedFolder = ref(null);
const matchedSubFiles = ref([]);
const divisionHeads = ref([]);
const recipient = ref();

const FoldersAvailable = ref([
  {
    id: 1,
    name: "Vendor Contracts",
    size: "200 KB",
  },
  {
    id: 2,
    name: "Purchase Orders",
    size: "400 KB",
  },
  {
    id: 3,
    name: "Expense Reports",
    size: "200 KB",
  },
]);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  emit("update:modelValue", false);
  selectedFolder.value = null;
};

const getExtension = (file_name) => {
  if (!file_name) return "";
  let file_arr = file_name.split(".");
  return file_arr.pop();
}

const getFileIcon = (fileName) => {
  const extension = getExtension(fileName)?.toLowerCase();

  switch (extension) {
    case "pdf":
      return "vscode-icons:file-type-pdf2";
    case "docx":
    case "doc":
      return "vscode-icons:file-type-word";
    case "xlsx":
    case "xls":
      return "vscode-icons:file-type-excel";
    case "ppt":
    case "pptx":
      return "vscode-icons:file-type-powerpoint";
    case "jpg":
    case "jpeg":
      return "vscode-icons:file-type-image";
    case "png":
      return "vscode-icons:file-type-image";
    default:
      return "material-icon-theme:pdf";
  }
};

// Function to remove a file from the selected files
const removeFile = (file) => {
  emit("removeF", file);
};

const getDivisionHeads = async () => {
  try {
    const response = await axios.get('/finance-and-accounts/get-division-heads');
    divisionHeads.value = response.data;
  } catch (error) {
    console.error("Error fetching division heads:", error);
  }
};

const submit = () => {
  //emit("update:modelValue", false);
  form.documents = props.selectedFiles.map(file => ({
    id: file.id,
  }));
  form.title = form.title.trim();
  form.dueDate = form.dueDate ? new Date(form.dueDate).toISOString() : null;
  form.comments = form.comments.trim();
 
  if (props.selectedFiles.length === 0) {
    toast.add({
      severity: "error",
      summary: "Error",
      detail: "Please attach at least one document.",
      life: 3000,
    });
    return;
  }
 
  form.post("/finance-and-accounts/initiate-payment", {
    onSuccess: () => {
      form.reset();
      emit("update:modelValue", false);
      console.log('this went fine')
      //clear selected files after submission
      emit("removeAllFiles");
      //notify the user
      toast.add({
        severity: "success",
        summary: "Payment Initiated Successfully",
        detail: "This task has been assigned to Deputy Director (Payment)",
        life: 3000,
      });
    },
    onError: (errors) => {
      // Handle errors
      form.errors = errors;
      toast.add({
        severity: "error",
        summary: "Error",
        detail: "Please fill in all required fields correctly.",
        life: 3000,
      });
    },
    preserveScroll: true,
  });


};

function formatToInputDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "";
  // Pad month, day, hours, minutes
  const pad = (n) => n.toString().padStart(2, "0");
  const year = date.getFullYear();
  const month = pad(date.getMonth() + 1);
  const day = pad(date.getDate());
  const hours = pad(date.getHours());
  const minutes = pad(date.getMinutes());
  return `${year}-${month}-${day}T${hours}:${minutes}`;
}

const getValue = (id) => {
  if(recipient.value && recipient.value.includes(id)) {
    return id;
  }else {
    return id;
  }
 
};
watch(()=> props.payment, (newPayment) => {
  if (newPayment && newPayment.files && Array.isArray(newPayment.files)) {
    form.documents = newPayment.files.map(file => ({
      id: file.id,
    }));
  } else {
    form.documents = [];
  }
  form.title = newPayment?.title || "";
  
  
  form.comments = newPayment?.comments || "";
   if(newPayment?.due_date) {
    form.dueDate = formatToInputDate(newPayment.due_date);
  }
  
  if(newPayment?.recipient) {
    recipient.value = [newPayment?.recipient?.id];
    form.recipient = [newPayment?.recipient?.id];
  } else {
    recipient.value = [];
  }
  form.payment_id = newPayment?.id || null;
}, { immediate: true });

onMounted(() => {
  getDivisionHeads();
  //divisionHeads.value = divisionHeads.value ? divisionHeads.value:null
});
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}

.p-inputtextarea {
  outline: none;
  box-shadow: none !important;
  resize: none;
  width: 100%;
}

.p-inputtextarea:focus {
  border-color: #d0d5dd;
  /* Hide border */
}
</style>
