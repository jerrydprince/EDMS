<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '50rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
    <template #header>
      <div class=" flex items-start justify-between w-full">
        <div class=" flex items-center gap-3">
          <div class=" p-3 border rounded-full">
            <Icon icon="iconamoon:edit-bold" width="30" />
          </div>
          <div>
            <h1 class=" text-2xl font-medium">Edit Entry</h1>
            <p class=" text-sm">Update entry metadata and destination</p>
          </div>
        </div>
      </div>
    </template>
    
    <Divider class=" -mt-1" />
    
    <section class=" w-full px-7 min-h-[30vh] mt-2">
      <div class="flex flex-col gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label for="name" class="font-semibold text-gray-700">File Name <span class="text-red-500">*</span></label>
            <input type="text" v-model="formData.name"
              class="p-2.5 border border-gray-300 rounded-lg outline-none text-sm w-full focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              placeholder="Enter file name" required>
            <div v-if="formData.errors.name" class="text-red-500 text-xs">{{ formData.errors.name }}</div>
          </div>

          <div class="flex flex-col gap-2">
            <label for="document_number" class="font-semibold text-gray-700">Document Number</label>
            <input type="text" v-model="formData.document_number"
              class="p-2.5 border border-gray-300 rounded-lg outline-none text-sm w-full focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
              placeholder="e.g. DOC-2023-001" required>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label class="font-semibold text-gray-700">Entry Type <span class="text-red-500">*</span></label>
            <Dropdown v-model="formData.file_type" :options="type" optionLabel="name" placeholder="Select Type" :pt="{
              root: { class: 'w-full flex border border-solid border-gray-300 rounded-lg items-center' }
            }">
              <template #value="slotProps">
                <div v-if="slotProps.value" class="flex items-center gap-2">
                  <div class="p-1.5 rounded-full border border-gray-200 bg-gray-50">
                    <Icon :icon="slotProps.value.icon" width="16" />
                  </div>
                  <span>{{ slotProps.value.name }}</span>
                </div>
                <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
              </template>
              <template #option="slotProps">
                <div class="flex items-center gap-2">
                  <div class="p-1.5 rounded-full border border-gray-200 bg-gray-50">
                    <Icon :icon="slotProps.option.icon" width="16" />
                  </div>
                  <span>{{ slotProps.option.name }}</span>
                </div>
              </template>
            </Dropdown>
            <div v-if="formData.errors.file_type" class="text-red-500 text-xs">{{ formData.errors.file_type }}</div>
          </div>

          <div class="flex flex-col gap-2">
            <label class="font-semibold text-gray-700">Destination Branch <span class="text-red-500">*</span></label>
            <Dropdown v-model="formData.branch" :options="allBranches" optionLabel="title" placeholder="Select Branch" :pt="{
              root: { class: 'w-full flex border border-solid border-gray-300 rounded-lg items-center' }
            }">
              <template #value="slotProps">
                <div v-if="slotProps.value" class="flex items-center gap-2">
                  <div class="p-1.5 rounded-full border border-gray-200 bg-gray-50">
                    <Icon icon="gg:organisation" width="16" />
                  </div>
                  <span class="truncate max-w-[150px]">{{ slotProps.value.title }}</span>
                </div>
                <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
              </template>
              <template #option="slotProps">
                <div class="flex items-center gap-2">
                  <div class="p-1.5 rounded-full border border-gray-200 bg-gray-50">
                    <Icon icon="gg:organisation" width="16" />
                  </div>
                  <span>{{ slotProps.option.title }}</span>
                </div>
              </template>
            </Dropdown>
            <div v-if="formData.errors.branch" class="text-red-500 text-xs">{{ formData.errors.branch }}</div>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label for="description" class="font-semibold text-gray-700">Description</label>
          <textarea v-model="formData.description" rows="3"
            class="p-2.5 border border-gray-300 rounded-lg outline-none text-sm w-full resize-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
            placeholder="Add brief description"></textarea>
        </div>

      </div>

    </section>

    <!-- Footer actions -->
    <div class="w-full flex items-center justify-between px-7 mt-6 border-t pt-4">
      <button @click="closeModal"
        class="border border-gray-300 rounded-lg py-2.5 px-6 font-medium text-gray-700 hover:bg-gray-50 transition-colors">
        Cancel
      </button>
      <button @click="submit" :disabled="formData.processing"
        class="bg-primary text-white py-2.5 px-8 rounded-lg font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
        <span v-if="formData.processing"><i class="pi pi-spinner pi-spin"></i></span>
        Save Changes
      </button>
    </div>

  </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { Icon } from "@iconify/vue";
import Divider from "primevue/divider";
import Dropdown from "primevue/dropdown";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import axios from "axios";

const emit = defineEmits(["update:modelValue", "refresh"]);
const props = defineProps({
  modelValue: Boolean,
  branches: Array,
  isAgf: {
    type: Boolean,
    default: false
  },
  entry: {
    type: Object,
    default: null
  }
});

const toast = useToast();
const page = usePage();

const type = ref([
  { name: "Public", icon: "material-symbols:public" },
  { name: "Secret", icon: "mdi:security-lock" },
  { name: "Top Secret", icon: "mdi:security-lock-outline" },
  { name: "Confidential", icon: "mdi:file-document-lock-outline" },
  { name: "De-classified", icon: "mdi:file-document-check-outline" },
]);

const formData = useForm({
  id: "",
  name: "",
  document_number: "",
  file_type: "",
  branch: "",
  description: "",
});

const allBranches = ref([]);

const getBranches = async () => {
  try {
    const response = await axios.get('/dashboard/admin-registry-branch');
    let branchesArray = Array.isArray(response.data) ? response.data : Object.values(response.data);
    
    const currentUser = page.props.auth.user;
    const currentBranchType = page.props.branch;

    if (props.isAgf) {
      allBranches.value = branchesArray.filter(branch => branch.branch_type === 'department');
    } else {
      if (currentBranchType === 'department') {
        allBranches.value = branchesArray.filter(branch =>
          (branch.branch_type === 'division' && branch.department_id === currentUser.department_id) ||
          (branch.branch_type === 'agf')
        );
      } else if (currentBranchType === 'division') {
        allBranches.value = branchesArray.filter(branch =>
          (branch.branch_type === 'unit' && branch.division_id === currentUser.division_id)
        );
      } else {
        allBranches.value = branchesArray;
      }
    }
    
    // Set initial branch if editing
    if (props.entry && allBranches.value.length > 0) {
      initFormData();
    }
  } catch (error) {
    console.error('Error fetching branches:', error);
  }
};

const initFormData = () => {
  if (props.entry) {
    formData.id = props.entry.id;
    formData.name = props.entry.name || "";
    formData.document_number = props.entry.document_number || "";
    formData.description = props.entry.description || "";
    
    if (props.entry.file_type) {
        let t = type.value.find(t => t.name.toLowerCase() == props.entry.file_type.toLowerCase());
        if(t) formData.file_type = t;
    }
    
    // Determine the current destination branch based on sent_to_X
    let branchId = null;
    let branchType = null;
    if (props.entry.sent_to_department) { branchId = props.entry.sent_to_department; branchType = 'department'; }
    else if (props.entry.sent_to_division) { branchId = props.entry.sent_to_division; branchType = 'division'; }
    else if (props.entry.sent_to_unit) { branchId = props.entry.sent_to_unit; branchType = 'unit'; }
    else if (props.entry.sent_to_agf) { branchId = props.entry.sent_to_agf; branchType = 'agf'; }

    if (branchId && allBranches.value.length > 0) {
      let b = allBranches.value.find(b => b.id == branchId && b.branch_type == branchType);
      if(b) formData.branch = b;
    }
  }
};

watch(() => props.entry, () => {
  initFormData();
});

onMounted(() => {
  getBranches();
});

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
};

const submit = async () => {
  formData.errors.branch = "";
  if (!formData.branch) {
      toast.add({
        severity: "error",
        summary: "Validation Error",
        detail: "Destination Branch is required. Please select a branch.",
        life: 3000,
      });
      formData.errors.branch = "Destination Branch is required";
      return;
  }

  formData.put(`/dashboard/registry/editfile/${formData.id}`, {
    onSuccess: page => {
      toast.add({
        severity: "success",
        summary: "Entry Updated",
        detail: "File metadata and destination updated successfully.",
        life: 3000,
      });
      emit("update:modelValue", false);
      emit('refresh')
      formData.reset();
    },
    onError: errors => {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: "Failed to update entry. Please check the fields.",
        life: 3000,
      });
    }
  });
};
</script>
