<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    content: { class: 'px-0 bg-white pb-6' },
  }" :style="{ width: '70rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class=" flex items-start justify-between w-full">
        <div class=" flex items-center gap-3">
          <div class=" p-3 border rounded-full">
            <Icon icon="iconamoon:file-add-bold" width="30" />
          </div>
          <div>
            <h1 class=" text-2xl font-medium">New Entry</h1>
            <p class=" text-sm">Add new file and track movement seamlessly</p>
          </div>
        </div>
      </div>
    </template>
    
    <Divider class=" -mt-1" />
    
    <section class=" w-full px-7 min-h-[55vh] mt-2">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 h-full">
        
        <!-- Left Column: Metadata -->
        <div class="flex flex-col gap-6">
          <!-- Basic Details Grid -->
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
              <div v-if="formData.errors.document_number" class="text-red-500 text-xs">{{ formData.errors.document_number }}</div>
            </div>
          </div>

          <!-- Type and Branch Grid -->
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

          <div class="p-4 border rounded-lg bg-gray-50 flex flex-col gap-3">
              <div class="flex items-center justify-between">
                  <div>
                      <h3 class="font-medium text-sm">Route to Workflow</h3>
                      <p class="text-xs text-gray-500">Automatically instantiate a workflow for this entry</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" v-model="formData.route_to_workflow" class="sr-only peer">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                  </label>
              </div>
              
              <div v-if="formData.route_to_workflow" class="mt-2">
                  <label class="text-sm font-semibold text-gray-700">Select Workflow Template <span class="text-red-500">*</span></label>
                  <Dropdown v-model="formData.workflow_version_id" :options="workflows" optionLabel="label" optionValue="id"
                      placeholder="Select Workflow Template" filter filterPlaceholder="Search templates..." :pt="{
                          root: { class: 'w-full mt-2 flex border border-solid border-gray-300 rounded-lg' }
                      }">
                  </Dropdown>
                  <div v-if="formData.errors.workflow_version_id" class="text-red-300 text-xs mt-1">{{ formData.errors.workflow_version_id }}</div>
              </div>
          </div>
        </div>

        <!-- Right Column: Documents Upload -->
        <div class="flex flex-col h-full bg-gray-50/50 p-4 border border-gray-100 rounded-xl">
          <label class="font-semibold text-gray-700 mb-2">Attachments</label>
          
          <div class=" p-1 border rounded-xl bg-white shadow-sm transition-all hover:border-primary/50">
            <div class="bg-[#FCFCFD] border-dashed flex flex-col items-center py-6 rounded-xl border-[2px] border-gray-300 relative overflow-hidden">
              <i class="pi pi-cloud-upload py-2 px-3 text-2xl text-primary/80"></i>
              <div class="text-center mb-4">
                <h1 class="font-semibold text-sm">Choose files or drag & drop</h1>
                <p class="text-xs mt-1 text-[#475467]">
                  Supports PDF, Images & Word docs
                </p>
              </div>

              <div class="flex gap-4 z-10">
                <button class="bg-white border-gray-300 border py-2 px-4 flex items-center gap-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors relative">
                  <Icon icon="ph:folder-light" width="20" />
                  <span>Browse Computer</span>
                  <input type="file" @change="handleFileUpload" multiple class="opacity-0 absolute inset-0 cursor-pointer w-full h-full z-20" />
                </button>
                <button type="button" @click="newScanModalVisible = true" class="bg-white border-gray-300 border py-2 px-4 flex items-center gap-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                  <Icon icon="tabler:line-scan" width="20" />
                  <span>Scan Document</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Document List -->
          <div class="mt-4 flex-1 overflow-y-auto pr-2 max-h-[300px]">
            <div v-if="files && files.length > 0" class="flex flex-col gap-3">
              <div v-for="(file, index) in files" :key="index" class="border bg-white p-3 rounded-lg flex items-center justify-between shadow-sm hover:shadow-md transition-shadow">
                
                <div class="flex items-center gap-3 overflow-hidden flex-1">
                  <!-- Thumbnail / Icon -->
                  <div class="shrink-0 w-10 h-10 rounded-md border flex items-center justify-center bg-gray-50 overflow-hidden">
                    <img v-if="isImage(file)" :src="getFileURL(file)" class="w-full h-full object-cover" />
                    <img v-else :src="getFileURL(file)" class="w-6" />
                  </div>
                  
                  <div class="flex flex-col truncate">
                    <h1 class="text-sm font-medium text-gray-800 truncate">{{ file.name }}</h1>
                    <span class="text-xs text-gray-500">{{ formatBytes(file.size) }}</span>
                  </div>
                </div>

                <div class="flex items-center gap-1 shrink-0 ml-3">
                  <button type="button" @click.prevent="openPreview(file)" class="p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-md transition-colors" title="Preview">
                    <Icon icon="ph:eye" width="18" />
                  </button>
                  <button type="button" @click.prevent="downloadAttachedFile(file)" class="p-1.5 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-md transition-colors" title="Download">
                    <Icon icon="material-symbols-light:download" width="18" />
                  </button>
                  <button type="button" @click.prevent="removeFile(index)" class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Remove">
                    <Icon icon="ph:trash" width="18" />
                  </button>
                </div>

              </div>
            </div>
            
            <div v-else class="h-full flex flex-col items-center justify-center text-gray-400 py-10">
              <Icon icon="ph:files-duotone" width="48" class="text-gray-300 mb-2" />
              <p class="text-sm">No documents attached</p>
            </div>
          </div>
        </div>

      </div>
    </section>
    
    <Divider />
    <template #footer>
      <div class="px-6 pb-2 pt-1 flex justify-end w-full">
        <Button class="mr-4" :pt="{
          root: { class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg hover:bg-gray-50 transition-colors' },
        }" @click="closeModal" label="Cancel" severity="secondary" outlined />

        <Button @click="submit" label="Create Entry" class="" :pt="{
          root: { class: 'py-2 bg-primary px-10 border-0 text-white font-medium rounded-lg hover:bg-primary/90 transition-colors' },
        }" />
      </div>
    </template>
  </Dialog>

  <!-- Document Preview Modal -->
  <Dialog v-model:visible="previewVisible" header="Document Preview" modal :style="{ width: '50rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
    <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg min-h-[400px]">
      <img v-if="previewFile && isImage(previewFile)" :src="previewFileUrl" class="max-w-full max-h-[600px] object-contain shadow-md rounded" />
      <object v-else-if="previewFile && isPDF(previewFile)" :data="previewFileUrl" type="application/pdf" width="100%" height="600px" class="rounded shadow-md">
        <p>PDF preview is not available in your browser. <a :href="previewFileUrl" target="_blank" class="text-primary">Download it here</a>.</p>
      </object>
      <div v-else class="text-center text-gray-500">
        <Icon icon="ph:file-x" width="64" class="mx-auto mb-4 text-gray-300" />
        <p>Preview is not available for this file type.</p>
        <p class="text-sm mt-2">{{ previewFile?.name }}</p>
      </div>
    </div>
  </Dialog>
  
  <!-- New Scan Modal -->
  <NewScanModal v-model="newScanModalVisible" @scanComplete="handleScanComplete" />
</template>

<script setup>
import { defineEmits, onMounted, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { Icon } from '@iconify/vue';
import NewScanModal from './NewScanModal.vue';

const toast = useToast();
const props = defineProps({
  modelValue: Boolean,
  branches: Array,
  isAgf: {
    type: Boolean,
    default: false
  }
});
const emit = defineEmits(["update:modelValue", "refresh"]);
const page = usePage()

const files = ref([])
const previewVisible = ref(false)
const previewFile = ref(null)
const previewFileUrl = ref('')
const newScanModalVisible = ref(false)

const handleScanComplete = (scannedFile) => {
  if (scannedFile) {
    files.value.push(scannedFile);
    toast.add({ severity: 'success', summary: 'Success', detail: 'Document scanned and attached successfully.', life: 3000 });
  }
}

const type = ref([
  { name: 'File', icon: 'ph:file-duotone' },
  { name: 'Mail', icon: 'ic:outline-mail' },
  { name: 'Correspondence', icon: 'lucide:mail-open' }
])

const handleFileUpload = (event) => {
    const newFiles = Array.from(event.target.files);
    files.value = [...files.value, ...newFiles];
    event.target.value = ''; // Reset input to allow selecting the same file again if needed
}

const removeFile = (index) => {
  files.value.splice(index, 1);
}

const openPreview = (file) => {
  previewFile.value = file;
  
  if (file.type) {
    previewFileUrl.value = URL.createObjectURL(file);
  } else {
    const extension = file.name.split('.').pop();
    const mimeTypes = {
      'jpg': 'image/jpeg', 'jpeg': 'image/jpeg', 'png': 'image/png', 'gif': 'image/gif',
      'pdf': 'application/pdf'
    };
    previewFileUrl.value = URL.createObjectURL(new Blob([file], { type: mimeTypes[extension] || 'application/octet-stream' }));
  }
  
  previewVisible.value = true;
}


const downloadAttachedFile = (file) => {
  const url = URL.createObjectURL(file);
  const a = document.createElement('a');
  a.href = url;
  a.download = file.name;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
};

const isImage = (file) => {
  const t = file.type || '';
  if (t.startsWith('image/')) return true;
  const ext = file.name.split('.').pop().toLowerCase();
  return ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
}

const isPDF = (file) => {
  const t = file.type || '';
  if (t === 'application/pdf') return true;
  const ext = file.name.split('.').pop().toLowerCase();
  return ext === 'pdf';
}

const formatBytes = (bytes, decimals = 2) => {
    if (!+bytes) return '0 Bytes'
    const k = 1024
    const dm = decimals < 0 ? 0 : decimals
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
}

const formData = useForm({
  name: "",
  file_type: "",
  description: "",
  document_number: "",
  branch: "",
  files: null,
  route_to_workflow: false,
  workflow_version_id: ""
})

const workflows = ref([]);
const allBranches = ref([]);

const getWorkflows = async () => {
  try {
    const response = await axios.get('/dashboard/workflows');
    workflows.value = response.data.data.map(w => ({
      ...w,
      label: w.workflow?.title || 'Unknown Workflow'
    }));
  } catch (error) {
    console.error('Error fetching workflows:', error);
  }
};

const getBranches = async () => {
  try {
    const response = await axios.get('/dashboard/admin-registry-branch');
    let branchesArray = Array.isArray(response.data) ? response.data : Object.values(response.data);
    
    const currentUser = page.props.user;
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
  } catch (error) {
    console.error('Error fetching branches:', error);
  }
};

onMounted(() => {
  getWorkflows();
  getBranches();
})

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
  formData.files = files.value

  formData.post(`/dashboard/registry/newfile`, {
    onSuccess: page => {
      toast.add({
        severity: "success",
        summary: "File Sent",
        detail: "File created successfully.",
        life: 3000,
      });
      files.value = []; // reset files
      emit("update:modelValue", false);
      emit('refresh')
      formData.reset();
    },
    onError: errors => {
      toast.add({
        severity: "error",
        summary: "Error",
        detail: "Failed to create entry. Please check the fields.",
        life: 3000,
      });
    }
  })
}

const getFileURL = (file) => {
  if (file.type) {
    if(file.type == 'application/pdf'){
        return page.props.base+'/images/pdf.svg' 
    }
    if(file.type == 'application/msword' || file.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'){
        return page.props.base+'/images/word.svg'
    }
    if(file.type == 'application/vnd.ms-excel' || file.type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'){
        return page.props.base+'/images/excel.svg'
    }
  } else {
    const extension = file.name.split('.').pop().toLowerCase();
    if(extension === 'pdf') return page.props.base+'/images/pdf.svg';
    if(['doc', 'docx'].includes(extension)) return page.props.base+'/images/word.svg';
    if(['xls', 'xlsx'].includes(extension)) return page.props.base+'/images/excel.svg';
  }
  // Default to a generic file icon if not an image and not a known doc type
  return page.props.base+'/images/file.svg' // assuming a generic file icon exists, fallback mechanism
}

</script>

<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
