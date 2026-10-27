<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl max-h-[95vh] h-[95vh] flex flex-col w-[95vw] lg:w-[85vw] max-w-[1400px]' },
    content: { class: 'p-0 flex flex-col overflow-hidden h-full flex-1 min-h-0' },
    header: { class: 'hidden' },
    footer: { class: 'hidden' }
  }">
    
    <!-- Custom Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5 flex items-center justify-between shrink-0 rounded-t-2xl">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center text-white border border-white/30 shadow-inner">
          <Icon icon="lucide:check-circle-2" width="28" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-white tracking-tight">Submit Task</h1>
          <p class="text-blue-100 text-sm mt-0.5">Complete and sign off on this task</p>
        </div>
      </div>
      <button @click="closeModal" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors">
        <Icon icon="lucide:x" width="20" />
      </button>
    </div>

    <!-- Main Content -->
    <div class="flex-1 relative overflow-hidden bg-slate-50/50">
      <div class="absolute inset-0 flex flex-col lg:flex-row">
      
      <!-- Left: Form -->
      <div class="w-full lg:w-[45%] h-full relative border-r border-gray-200">
        <div class="absolute inset-0 p-6 md:p-8 flex flex-col gap-6 overflow-y-auto pb-12">
      
      <!-- Upload Section -->
      <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
              <Icon icon="lucide:file-up" width="16" />
            </div>
            <h2 class="text-gray-800 font-semibold text-lg">Supporting Documents</h2>
          </div>
          <span class="text-xs font-medium px-2 py-1 bg-gray-100 text-gray-500 rounded-md">Optional</span>
        </div>
        
        <div class="relative group">
          <div class="border-2 border-dashed border-gray-200 group-hover:border-blue-400 bg-gray-50 group-hover:bg-blue-50/30 transition-all duration-300 rounded-xl flex flex-col items-center justify-center py-10 px-4 text-center cursor-pointer overflow-hidden">
            
            <!-- Default State -->
            <div v-if="!files || files.length == 0" class="flex flex-col items-center pointer-events-none">
              <div class="w-16 h-16 bg-white shadow-sm rounded-full flex items-center justify-center text-gray-400 group-hover:text-blue-500 group-hover:scale-110 transition-all duration-300 mb-4">
                <Icon icon="lucide:cloud-upload" width="32" />
              </div>
              <h3 class="font-semibold text-gray-700 text-base mb-1">Click to upload or drag & drop</h3>
              <p class="text-sm text-gray-400">PDF, DOCX, JPG or PNG (max. 10MB)</p>
            </div>
            
            <!-- File Previews -->
            <div v-else class="flex flex-wrap gap-4 items-center justify-center pointer-events-none w-full">
              <div v-for="file in files" class="relative bg-white p-2 rounded-lg shadow-sm border border-gray-100 flex items-center justify-center max-w-[120px] aspect-square overflow-hidden">
                <img :src="getFileURL(file)" class="w-full h-full object-cover rounded-md" alt="Preview" />
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                  <Icon icon="lucide:refresh-cw" class="text-white" width="24" />
                </div>
              </div>
            </div>

            <!-- The actual hidden input spanning the whole container -->
            <input type="file" multiple @change="handleFileUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
          </div>
        </div>
      </div>

      <!-- Description Section -->
      <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
              <Icon icon="lucide:align-left" width="16" />
            </div>
            <h2 class="text-gray-800 font-semibold text-lg">Closing Remarks</h2>
          </div>
          <span class="text-xs font-medium px-2 py-1 bg-gray-100 text-gray-500 rounded-md">Optional</span>
        </div>
        
        <textarea v-model="form.description" rows="3" placeholder="Add any final notes or description about the completion of this task..." class="w-full border border-gray-200 rounded-xl p-4 text-gray-700 outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50 transition-all resize-none shadow-sm"></textarea>
      </div>

      <!-- Digital Signature Section -->
      <div class="shrink-0 bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-2xl border border-amber-200 shadow-inner relative overflow-hidden">
        <!-- Decorative bg -->
        <Icon icon="lucide:shield-check" width="120" class="absolute -bottom-6 -right-6 text-amber-500/10 rotate-[-15deg] pointer-events-none" />
        
        <div class="relative z-10 flex flex-col items-center justify-center">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
              <Icon icon="lucide:key-round" width="16" />
            </div>
            <h2 class="text-amber-900 font-bold text-lg">Digital Signature</h2>
          </div>
          <p class="text-amber-700/80 text-sm text-center mb-6 max-w-xs">Please authorize this submission by entering your secure PIN.</p>
          
          <div class="w-full max-w-xs relative">
            <input id="approval_pin" v-model="form.approval_pin" type="password" placeholder="****" class="w-full text-center tracking-widest text-xl font-bold bg-white/80 border-2 border-amber-200 rounded-xl py-2 px-4 outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/20 transition-all shadow-sm text-amber-900 placeholder:text-amber-200" />
            <div v-if="form.errors.approval_pin" class="absolute -bottom-7 left-0 right-0 text-red-500 text-xs font-medium text-center flex items-center justify-center gap-1">
              <Icon icon="lucide:alert-circle" width="12" /> {{ form.errors.approval_pin }}
            </div>
          </div>
        </div>
      </div>
      </div>
      
      </div>
      
      
      <!-- Right: Preview Pane -->
      <div class="hidden lg:flex flex-col w-full lg:w-[55%] h-full bg-gray-100 overflow-hidden relative">
        <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between shadow-sm z-10 shrink-0">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:eye" class="text-blue-500" width="18" />
            <h3 class="font-semibold text-gray-700 text-sm">Document Preview</h3>
          </div>
          <div class="flex items-center gap-2 bg-gray-100 p-1 rounded-lg">
             <button @click="previewSource = 'task'" :class="previewSource === 'task' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 text-xs font-medium rounded-md transition-all">Attached to Task</button>
             <button @click="previewSource = 'upload'" :class="previewSource === 'upload' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 text-xs font-medium rounded-md transition-all">My Uploads</button>
          </div>
        </div>

        <div class="flex-1 overflow-hidden relative bg-gray-100 flex items-center justify-center p-4">
           <!-- Task Document Preview -->
           <div v-if="previewSource === 'task'" class="w-full h-full flex flex-col">
              <div v-if="!props.task || !props.task.taskdocs || props.task.taskdocs.length === 0" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                 <Icon icon="lucide:file-x" width="48" class="mb-3 opacity-50" />
                 <p>No documents attached to this task.</p>
              </div>
              <div v-else class="w-full h-full bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                 <div class="bg-gray-50 border-b border-gray-200 p-2 flex items-center gap-2 overflow-x-auto hide-scroll shrink-0">
                    <button v-for="(doc, idx) in props.task.taskdocs" :key="idx" @click="activeTaskDoc = doc" :class="activeTaskDoc === doc ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'" class="px-3 py-1.5 rounded-lg border text-xs font-medium whitespace-nowrap flex items-center gap-2 transition-all">
                       <Icon icon="lucide:file-text" width="14" />
                       {{ doc.file_name || doc.name || 'Document ' + (idx+1) }}
                    </button>
                 </div>
                 <div class="flex-1 w-full h-full p-0 relative">
                    <PDFEditor ref="taskPdfEditorRef" v-if="activeTaskDoc" :url="getTaskDocUrl(activeTaskDoc)" />
                 </div>
              </div>
           </div>

           <!-- Uploaded File Preview -->
           <div v-if="previewSource === 'upload'" class="w-full h-full flex flex-col">
              <div v-if="!files || files.length === 0" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                 <Icon icon="lucide:upload-cloud" width="48" class="mb-3 opacity-50" />
                 <p>Upload a document to preview it here.</p>
              </div>
              <div v-else class="w-full h-full bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                 <div class="bg-gray-50 border-b border-gray-200 p-2 flex items-center gap-2 overflow-x-auto hide-scroll shrink-0">
                    <button v-for="(file, idx) in files" :key="idx" @click="activeUploadFile = file" :class="activeUploadFile === file ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'" class="px-3 py-1.5 rounded-lg border text-xs font-medium whitespace-nowrap flex items-center gap-2 transition-all">
                       <Icon icon="lucide:file" width="14" />
                       {{ file.name }}
                    </button>
                 </div>
                 <div class="flex-1 w-full h-full p-0 relative">
                    <PDFEditor ref="uploadPdfEditorRef" v-if="activeUploadFile" :url="getActualFileURL(activeUploadFile)" />
                 </div>
              </div>
           </div>
        </div>
      </div>
    </div>
    </div>

    <!-- Custom Footer -->
    <div class="bg-gray-50/90 backdrop-blur-md px-6 py-5 border-t border-gray-200 flex items-center justify-end gap-3 shrink-0 rounded-b-2xl">
      <button @click="closeModal" class="px-6 py-2.5 rounded-xl font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 hover:text-gray-800 transition-all shadow-sm focus:outline-none focus:ring-4 focus:ring-gray-100">
        Cancel
      </button>
      <button @click="submit" :disabled="form.processing || isPreparingSubmission" class="px-8 py-2.5 rounded-xl font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all focus:outline-none focus:ring-4 focus:ring-blue-500/30 flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
        <Icon v-if="form.processing" icon="lucide:loader-2" class="animate-spin" width="18" />
        <Icon v-else icon="lucide:check-circle-2" width="18" />
        {{ form.processing ? 'Submitting...' : 'Submit Task' }}
      </button>
    </div>
  </Dialog>

  <!-- <Progress  v-model="progressModal" /> -->

</template>

<script setup>
import { defineEmits, defineProps, computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { useForm, router, usePage } from "@inertiajs/vue3";
import PDFEditor from "../../../components/PDFViewer/PDFEditor.vue";

const page = usePage();
const toast = useToast();
const props = defineProps(["modelValue", 'task', 'assignedToMe', 'modifiedPdfBlob', 'activeDocPath']);
const emit = defineEmits(["update:modelValue", "refresh"]);
const files = ref([]);

const previewSource = ref('task'); // 'task' or 'upload'
const activeTaskDoc = ref(null);
const activeUploadFile = ref(null);

const form = useForm({
  task_id: null,
  description: "",
  is_revision: 0,
  files: null,
  modified_file: null,
  modified_file_path: null,
  assigned_to: (props.assignedToMe) ? props.assignedToMe?.assigned_to : '',
  assigned_by: (props.assignedToMe) ? props.assignedToMe?.assigned_by : '',
  task_assignment_id: (props.assignedToMe) ? props.assignedToMe.id : null,
  approval_pin: ''
});

// When task is available, set the first doc as active by default
watch(() => props.task, (newTask) => {
   if (newTask && newTask.taskdocs && newTask.taskdocs.length > 0) {
      activeTaskDoc.value = newTask.taskdocs[0];
   }
}, { immediate: true });

// When files are uploaded, auto-switch to 'upload' and show the first file
watch(() => files.value, (newFiles) => {
   if (newFiles && newFiles.length > 0) {
      activeUploadFile.value = newFiles[0];
      previewSource.value = 'upload';
   } else {
      activeUploadFile.value = null;
      previewSource.value = 'task';
   }
}, { deep: true });

const getTaskDocUrl = (doc) => {
    if (!doc || !doc.path) return '';
    const base = page.props.base || '';
    const path = doc.path.startsWith('/') ? doc.path : '/' + doc.path;
    return base + path;
};

const objectUrlCache = new WeakMap();

const getActualFileURL = (file) => {
    if (!file) return '';
    if (objectUrlCache.has(file)) return objectUrlCache.get(file);
    const url = URL.createObjectURL(file);
    objectUrlCache.set(file, url);
    return url;
};

// import Progress from "../../../components/Modals/Workflows/progress.vue";

const progressModal = ref(false);

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

const handleFileUpload = (event) => {
  if (event.target.files.length > 0) {
    const newFiles = Array.from(event.target.files);
    files.value = [...files.value, ...newFiles];
    form.files = files.value;
  }
  // Reset input value so same files can be re-selected if needed
  event.target.value = '';
}

const removeFile = (index) => {
  files.value.splice(index, 1);
  form.files = files.value;
}

const submit = async () => {
  isPreparingSubmission.value = true;
  let submissionFiles = [...files.value];
  let blobToUse = props.modifiedPdfBlob || null;
  let targetDocPath = "";

  // Check if user edited the active document directly in this modal
  try {
    if (previewSource.value === 'task' && taskPdfEditorRef.value && taskPdfEditorRef.value.hasModifications) {
      blobToUse = await taskPdfEditorRef.value.getPdfBlob();
      targetDocPath = activeTaskDoc.value?.path || "";
    } else if (previewSource.value === 'upload' && uploadPdfEditorRef.value && uploadPdfEditorRef.value.hasModifications) {
      blobToUse = await uploadPdfEditorRef.value.getPdfBlob();
      targetDocPath = "";
    }
  } catch (err) {
    console.error('Error generating PDF from modal editor', err);
  }

  if (blobToUse) {
      let filePath = targetDocPath || props.activeDocPath;
      if (!filePath) {
        if (props.task && props.task.file && props.task.file.path) {
            filePath = props.task.file.path;
        } else if (props.task && props.task.taskdocs && props.task.taskdocs.length > 0) {
            filePath = props.task.taskdocs[0].path;
        }
      }
      const rawBaseName = filePath ? filePath.split('/').pop().split('?')[0] : 'document.pdf';
      const fileName = "signed_" + rawBaseName;
      const modifiedFileObj = new File([blobToUse], fileName, { type: 'application/pdf' });
      form.modified_file = modifiedFileObj;
      form.modified_file_path = filePath;
      submissionFiles.push(modifiedFileObj);
  }
  
  form.files = submissionFiles;
  form.task_id = props.task.id;
  if (!form.task_assignment_id) {
    const assignment = props.assignedToMe || props.task?.assigned_to_me || (props.task?.assignments ? props.task.assignments.find(a => a.assigned_to == page.props.user?.id) : null);
    if (assignment) {
      form.assigned_to = assignment.assigned_to;
      form.assigned_by = assignment.assigned_by;
      form.task_assignment_id = assignment.id;
    }
  }
  
  if (!form.approval_pin) {
    isPreparingSubmission.value = false;
    form.setError('approval_pin', 'Please enter your digital signature PIN to authorize submission.');
    return false;
  }
  
  form.post('/dashboard/task-submission', {
    onFinish: () => {
      isPreparingSubmission.value = false;
    },
    onSuccess: (page) => {
      emit("update:modelValue", false);
      form.reset();
      files.value = [];
      toast.add({
        severity: "success",
        summary: 'Task Submitted',
        detail: "Your task has been marked as complete and submitted for review.",
        life: 3000,
      });
      if (window.location.pathname.includes('/view-task')) {
        router.visit('/dashboard/workflow');
      } else {
        emit("refresh");
      }
    }
  });
};

const getFileURL = (file) => {
  if (file.type) {
    if (file.type == 'image/jpeg' || file.type == 'image/jpeg' || file.type == 'image/png' || file.type == 'image/gif') {
      return getActualFileURL(file);
    }

    if (file.type == 'application/pdf') {
      return page.props.base + '/images/pdf.svg'
    }

    if (file.type == 'application/msword' || file.type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
      return page.props.base + '/images/word.svg'
    }

    if (file.type == 'application/vnd.ms-excel' || file.type == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
      return page.props.base + '/images/excel.svg'
    }
  } else {
    // If type is not available, manually determine file type based on extension
    const extension = file.name.split('.').pop();
    const mimeTypes = {
      'jpg': 'image/jpeg',
      'jpeg': 'image/jpeg',
      'png': 'image/png',
      'gif': 'image/gif',
      'pdf': 'application/pdf',
      'doc': 'application/msword',
      'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'xls': 'application/vnd.ms-excel',
      'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    };

    return URL.createObjectURL(new Blob([file], { type: mimeTypes[extension] || 'application/octet-stream' }));
  }
}
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
