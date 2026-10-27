<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 max-h-[97vh] hide_scroll rounded-md overflow-y-scroll' },
    content: { class: 'px-0 pb-6' },
    header: { class: 'm-5' },
    footer: { class: 'bg-slate-100' }
  }" :style="{ width: '45rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class="custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="tabler:scanner" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Scan Document</h1>
          </div>
        </div>
        <div>
          <i @click="closeModal" class="pi pi-times cursor-pointer p-2 border rounded-md bg-white hover:bg-gray-50"></i>
        </div>
      </div>
    </template>
    
    <section class="bg-white mx-2 rounded-md p-5 flex flex-col min-h-[400px]">
      
      <!-- Scan Options -->
      <div v-if="!scanning && scannedFiles.length === 0" class="flex flex-col items-center justify-center w-full h-full my-10">
        <Icon icon="tabler:line-scan" width="80" class="mx-auto text-primary opacity-50 mb-4" />
        <h2 class="text-xl font-semibold mb-2">Ready to Scan via NAPS2</h2>
        <p class="text-gray-500 mb-6">Place your documents in the scanner and select a mode.</p>
        
        <div class="flex flex-col gap-4 w-full max-w-sm mb-6">
          <div class="mb-2">
            <p class="font-medium text-sm text-gray-700 mb-2">Paper Source</p>
            <div class="flex gap-4">
               <label class="flex items-center gap-2 cursor-pointer text-sm">
                 <input type="radio" v-model="paperSource" value="glass" name="paperSource"> Glass (Flatbed)
               </label>
               <label class="flex items-center gap-2 cursor-pointer text-sm">
                 <input type="radio" v-model="paperSource" value="feeder" name="paperSource"> Document Feeder
               </label>
               <label class="flex items-center gap-2 cursor-pointer text-sm">
                 <input type="radio" v-model="paperSource" value="duplex" name="paperSource"> Duplex
               </label>
            </div>
          </div>

          <div class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:border-primary" :class="{'border-primary bg-primary/5': scanMode === 'single'}" @click="scanMode = 'single'">
            <input type="radio" v-model="scanMode" value="single" class="cursor-pointer">
            <div>
              <p class="font-medium">Single File</p>
              <p class="text-xs text-gray-500">Scan multiple pages into one PDF document</p>
            </div>
          </div>
          
          <div class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:border-primary" :class="{'border-primary bg-primary/5': scanMode === 'multiple'}" @click="scanMode = 'multiple'">
            <input type="radio" v-model="scanMode" value="multiple" class="cursor-pointer">
            <div>
              <p class="font-medium">Multiple Files</p>
              <p class="text-xs text-gray-500">Scan multiple pages as separate PDF files</p>
            </div>
          </div>
        </div>
        
        <button @click="startScan" class="bg-primary text-white py-3 px-8 rounded-lg font-medium hover:bg-primary/90 flex items-center gap-2 mx-auto">
          <Icon icon="tabler:player-play" width="20" />
          Start Scan
        </button>
      </div>
      
      <!-- Scanning Progress -->
      <div v-if="scanning" class="flex flex-col items-center justify-center w-full h-full my-10">
        <Icon icon="tabler:loader" width="60" class="mx-auto text-primary animate-spin mb-4" />
        <h2 class="text-xl font-semibold mb-2">Scanning...</h2>
        <p class="text-gray-500 text-center max-w-sm">Please wait while NAPS2 communicates with your scanner. The physical scanner should start now.</p>
        <div class="w-full max-w-sm bg-gray-200 h-2 mt-4 rounded-full overflow-hidden">
           <div class="bg-primary h-full w-full animate-pulse"></div>
        </div>
        <button @click="cancelScan" class="mt-6 text-red-500 hover:underline text-sm font-medium">Cancel Scan</button>
      </div>

      <!-- Scan Complete / Preview -->
      <div v-if="scannedFiles.length > 0" class="flex flex-col w-full h-full">
        <div class="flex items-center justify-between mb-4 pb-3 border-b">
          <div class="flex items-center gap-2">
             <Icon icon="tabler:circle-check" width="24" class="text-green-500" />
             <h2 class="text-lg font-semibold">Scan Complete ({{ scannedFiles.length }} file{{ scannedFiles.length > 1 ? 's' : '' }})</h2>
          </div>
          <button @click="resetScan" class="text-primary hover:underline text-sm font-medium">Rescan</button>
        </div>
        
        <!-- Preview Container -->
        <div class="flex-1 bg-gray-100 rounded-lg p-2 overflow-y-auto mb-4" style="max-height: 400px;">
          <div v-if="scanMode === 'single'" class="w-full h-[400px]">
             <PDFEditor :url="scannedFiles[0].url" />
          </div>
          <div v-else class="grid grid-cols-2 gap-4">
             <div v-for="(file, index) in scannedFiles" :key="index" class="bg-white p-2 rounded border shadow-sm flex flex-col items-center">
                <div class="h-[200px] pointer-events-none"><PDFEditor :url="file.url" /></div>
                <p class="text-xs font-medium text-gray-600 truncate w-full text-center">{{ file.file.name }}</p>
             </div>
          </div>
        </div>
        
        <div class="flex justify-end mt-2">
          <button @click="uploadAndSend" class="bg-primary text-white py-2 px-6 rounded-lg font-medium hover:bg-primary/90 flex items-center gap-2">
            <Icon icon="tabler:paperclip" width="18" />
            Add Attachment{{ scannedFiles.length > 1 ? 's' : '' }}
          </button>
        </div>
      </div>
    </section>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref } from "vue";
import { Icon } from '@iconify/vue';
import { useToast } from "primevue/usetoast";
import axios from 'axios';

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue", 'scanComplete']);
const toast = useToast();

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const scanMode = ref('single');
const paperSource = ref('glass');
const scanning = ref(false);
const scannedFiles = ref([]); // Array of { file: File, url: String }

const closeModal = () => {
  emit("update:modelValue", false);
  if (scanning.value) cancelScan();
  setTimeout(() => {
    resetScan();
  }, 300);
};

const resetScan = () => {
  scanning.value = false;
  // Cleanup object URLs
  scannedFiles.value.forEach(item => URL.revokeObjectURL(item.url));
  scannedFiles.value = [];
};

const b64toBlob = (b64Data, contentType='', sliceSize=512) => {
  const byteCharacters = atob(b64Data);
  const byteArrays = [];

  for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
    const slice = byteCharacters.slice(offset, offset + sliceSize);
    const byteNumbers = new Array(slice.length);
    for (let i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    byteArrays.push(byteArray);
  }

  return new Blob(byteArrays, {type: contentType});
}

const startScan = async () => {
  scanning.value = true;
  
  try {
    const response = await fetch(`http://127.0.0.1:9880/scan?type=${scanMode.value}&source=${paperSource.value}`, {
        method: 'GET',
        mode: 'cors'
    });
    
    scanning.value = false;
    
    if (response.status === 204) {
      toast.add({ severity: 'warn', summary: 'Cancelled', detail: 'Scan was cancelled from the scanner.', life: 3000 });
      return;
    }
    
    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(errorText || `HTTP Error ${response.status}`);
    }
    
    if (scanMode.value === 'single') {
        const blob = await response.blob();
        const file = new File([blob], `Scanned_Document_${Date.now()}.pdf`, { type: 'application/pdf' });
        scannedFiles.value = [{
            file: file,
            url: URL.createObjectURL(file)
        }];
    } else {
        const data = await response.json();
        const results = [];
        data.forEach(item => {
            const blob = b64toBlob(item.data, item.type);
            const file = new File([blob], item.name, { type: item.type });
            results.push({
                file: file,
                url: URL.createObjectURL(file)
            });
        });
        scannedFiles.value = results;
    }
  } catch (error) {
    scanning.value = false;
    console.error(error);
    toast.add({ severity: 'error', summary: 'Scanner Error', detail: error.message || 'Failed to communicate with NAPS2 local service.', life: 10000 });
  }
};

const cancelScan = async () => {
  scanning.value = false;
  try {
     await fetch('http://127.0.0.1:9880/scan', { method: 'DELETE', mode: 'cors' });
  } catch(e) {}
};

const uploadAndSend = () => {
  // Emit all scanned files
  scannedFiles.value.forEach(item => {
      emit('scanComplete', item.file);
  });
  closeModal();
};
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}
</style>
