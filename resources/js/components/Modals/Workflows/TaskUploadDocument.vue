<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 max-h-[97vh] hide_scroll rounded-md overflow-y-scroll' },
    content: { class: 'px-0 pb-6' },
    header: { class: 'm-5' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class=".custom-dialog">
    
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white cursor-pointer" @click="closeModal">
            <Icon icon="ion:arrow-back-outline" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Upload or Scan Document</h1>
            <p class="text-sm text-gray-500 mt-1">Attach a file to this task.</p>
          </div>
        </div>
      </div>
    </template>
    
    <section class="bg-white mx-2 rounded-md p-5">
      <div class="p-1 border rounded-xl mt-5">
        <div class="bg-[#FCFCFD] border-dashed flex flex-col items-center py-7 rounded-xl border-[3px]">
          <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
          <div class="text-center">
            <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
            <p class="text-sm leading-7 mb-7 text-[#475467]">
              Supports PDF, Images & Word docs
            </p>
          </div>

          <button class="bg-white border-gray-300 border p-3 flex items-center gap-2 rounded-lg relative hover:bg-gray-50">
            <Icon icon="ph:folder-light" width="25" />
            <input type="file" @change="handleFileUpload" multiple class="opacity-0 absolute w-full inset-0 cursor-pointer" />
            Browse Computer
          </button>
          
          <button class="mt-4 bg-white border-gray-300 border p-3 flex items-center gap-2 rounded-lg hover:bg-gray-50" @click="triggerScan">
            <Icon icon="tabler:line-scan" width="25" />
            Scan Document
          </button>
        </div>
      </div>

      <div v-if="files && files.length > 0">
        <div class="w-full mt-3 gap-4 flex items-center" v-for="(file, index) in files" :key="index">
          <div class="border p-3 rounded-md w-full">
            <div class="flex justify-between">
              <div class="flex items-center gap-1 justify-center">
                <img :src="getFileURL(file)" alt="" class="w-7" />
                <div>
                  <h1 class="text-[#1D2939] pl-3 truncate max-w-[200px]" :title="file.name">{{ file.name }}</h1>
                  <div class="flex items-center gap-5">
                    <p class="text-xs leading-7 text-[#667085]" v-if="loading">
                      Uploading...
                    </p>
                    <p class="text-xs leading-7 text-[#667085]" v-else>
                      {{ (file.size / 1024).toFixed(2) }} KB
                    </p>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                 <Icon v-if="!loading" icon="fluent:delete-48-regular" class="cursor-pointer text-red-500 hover:text-red-700" width="20" @click="removeFile(index)" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <template #footer>
      <div class="flex items-center justify-end px-7 mt-4">
        <Button class="mr-4" :pt="{ root: { class: 'py-2 bg-white px-8 border-gray-200 border border-solid rounded-lg' } }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        
        <Button label="Upload" :disabled="!files || files.length === 0 || loading" :pt="{ root: { class: 'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg disabled:opacity-50' } }" @click="submit" />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const props = defineProps(["modelValue", "task"]);
const emit = defineEmits(["update:modelValue", 'update']);
const toast = useToast();
const loading = ref(false);
const files = ref([]);
const page = usePage();

const visible = computed({
  get() { return props.modelValue; },
  set(value) { emit("update:modelValue", value); }
});

const closeModal = () => {
  files.value = [];
  emit("update:modelValue", false);
};

const handleFileUpload = (event) => {
  const selectedFiles = Array.from(event.target.files);
  files.value = [...files.value, ...selectedFiles];
};

const removeFile = (index) => {
  files.value.splice(index, 1);
};

const triggerScan = () => {
  toast.add({ severity: 'info', summary: 'Scanner Required', detail: 'Local scanner interface not detected. Please upload from computer.', life: 4000 });
};

const getFileURL = (file) => {
  if (file.type) {
    if (file.type.startsWith('image/')) return URL.createObjectURL(file);
    if (file.type === 'application/pdf') return page.props.base + '/images/pdf.svg';
    if (file.type.includes('word')) return page.props.base + '/images/word.svg';
    if (file.type.includes('excel') || file.type.includes('spreadsheet')) return page.props.base + '/images/excel.svg';
  }
  return page.props.base + '/images/file.svg';
};

const submit = () => {
  if (!files.value || files.value.length === 0) return;
  
  loading.value = true;
  router.post('/dashboard/upload-task-document', {
    task_id: props.task.id,
    files: files.value
  }, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      files.value = [];
      loading.value = false;
      closeModal();
      emit('update');
      toast.add({ severity: "success", summary: 'Success', detail: "Documents attached successfully.", life: 3000 });
    },
    onError: (errors) => {
      loading.value = false;
      toast.add({ severity: "error", summary: 'Upload Failed', detail: errors.error || "An error occurred during upload.", life: 5000 });
    }
  });
};
</script>

<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
