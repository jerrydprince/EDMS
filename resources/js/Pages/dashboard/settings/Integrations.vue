<template>
   <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
      <div class="flex items-center gap-5">
        <div class=" border rounded-full p-3"  >
          <Icon icon="mi:filter" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939] ">Integrations</h1>
          <p class=" text-gray-500">Connect and sync with essential tools and platforms.</p>
        </div>
      </div>
      <div>
        <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded-md font-medium hover:bg-blue-700 transition">
          Add Integration
        </button>
      </div>
    </div>
    <Divider class=" " />

    <div class=" lg:pl-7  ">

      
      <section class=" lg:flex items-center justify-between  pr-7">
       
       <div class="flex items-center justify-between  lg:w-[40%] text-sm">
     <div class="bg-gray-100 flex items-center rounded-md p-1">
       <button
         @click="activeTab = 'All Apps'"
         class="rounded-lg py-1 px-4 transition-colors"
         :class="activeTab === 'All Apps' ? 'bg-white shadow-md border-gray-300 text-black' : 'text-gray-400'"
       >
         <p>All Apps</p>
       </button>
       <button
         @click="activeTab = 'Connected'"
         class="flex gap-1 items-center rounded-lg py-1 px-4 transition-colors"
         :class="activeTab === 'Connected' ? 'bg-white shadow-md border-gray-300 text-black' : 'text-gray-400'"
       >
         <p>Connected</p>
       </button>
       <button
         @click="activeTab = 'Disconnected'"
         class="flex gap-1 items-center rounded-lg py-1 px-4 transition-colors"
         :class="activeTab === 'Disconnected' ? 'bg-white shadow-md border-gray-300 text-black' : 'text-gray-400'"
       >
         <p>Disconnected</p>
       </button>
     </div>
   
   </div>

 <div class=" flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
     <i class="pi pi-search text-gray-300 " style="font-size: 1rem"></i>
     <Input type="text" v-model="searchValue" placeholder="Search Apps" class=" w-96 border-0 outline-none text-[#98A2B3] bg-transparent" />
 </div>
   </section>

   <div class=" mt-10">
    <div>
          <h1 class="text-2xl font-semibold text-[#1D2939] ">{{ activeTab }}</h1>
          <p class=" text-gray-500">Access all the integrated tools and apps ready for document management</p>
        </div>
   </div>
   
      <Divider/>
    
     <!-- ================  All Apps ========= -->
     <div class=" w-full flex gap-5 flex-wrap justify-between pr-7">
      <div v-for="item in filteredApps" :key="item.id" class=" border rounded-md border-gray-30 w-[350px] p-5 ">
        <div class=" flex gap-2 items-start  ">
        <div class=" ">
            <Icon :icon="item.logo" width="70" class="p-3 bg-[#F9FAFB] rounded-md border border-gray-100"/>
            </div>
           <div>
            <h1 class="text-xl font-semibold text-[#1D2939] "> {{ item.name }}</h1>
            <p class=" text-gray-500 text-sm">{{ item.desc }}</p>
           </div>
          </div>
          <button @click="toggleConnect(item.id, item.isCustom)" class="w-full border border-gray-300 p-2 rounded-md mt-2 font-semibold transition-colors"
              :class="{ 'bg-[#EBF3FE] text-[#3575DD] border-[#3575DD]': isConnected(item.id), 'bg-white text-gray-700': !isConnected(item.id) }">
        {{ isConnected(item.id) ? 'Connected' : 'Connect' }}
      </button>
     </div>
     <div v-if="filteredApps.length === 0" class="w-full text-center text-gray-500 py-10">
       No integrations found matching your criteria.
     </div>
    </div>
    </div>

    <!-- Add Integration Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Custom Integration</h2>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-black">
            <Icon icon="mdi:close" width="24" />
          </button>
        </div>
        <form @submit.prevent="submitIntegration">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="form.name" type="text" required class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-500" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-500"></textarea>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
            <input v-model="form.webhook_url" type="url" class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-500" placeholder="https://" />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
            <input v-model="form.api_key" type="text" class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-500" />
          </div>
          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" :disabled="submitting" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              {{ submitting ? 'Adding...' : 'Add Integration' }}
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from "primevue/usetoast";

const activeTab = ref('All Apps');
const searchValue = ref('');
const connectedIds = ref([]);
const showAddModal = ref(false);
const submitting = ref(false);
const toast = useToast();

const form = ref({
  name: '',
  description: '',
  webhook_url: '',
  api_key: ''
});

const apps = ref([
  {
    id: 'google',
    logo: 'logos:google-drive',
    name: 'Google Drive',
    desc: 'Integration with Google Drive for easy storage and backup of documents.',
    isCustom: false
  },
  {
    id: 'dropbox',
    logo: 'logos:dropbox',
    name: 'Dropbox',
    desc: 'Seamlessly sync and archive your files securely to Dropbox.',
    isCustom: false
  },
  {
    id: 'microsoft',
    logo: 'vscode-icons:file-type-word',
    name: 'Microsoft Office 365',
    desc: 'Directly view, edit, and export documents using Microsoft Graph (Word, Excel, PowerPoint).',
    isCustom: false
  },
  {
    id: 'pdf-adobe',
    logo: 'vscode-icons:file-type-pdf2',
    name: 'Adobe PDF',
    desc: 'View, annotate, and digitally sign documents with advanced PDF tools.',
    isCustom: false
  }
])

onMounted(async () => {
  loadIntegrations();
});

const loadIntegrations = async () => {
  try {
    const activeRes = await axios.get('/api/integrations/active');
    if (activeRes.data) {
      connectedIds.value = activeRes.data;
    }

    const customRes = await axios.get('/api/integrations/custom');
    if (customRes.data) {
      const customApps = customRes.data.map(app => ({
        id: `custom_${app.id}`,
        logo: app.logo,
        name: app.name,
        desc: app.description,
        isCustom: true
      }));
      // Remove previously loaded custom apps to avoid duplicates
      apps.value = apps.value.filter(app => !app.isCustom).concat(customApps);
    }
  } catch (error) {
    console.error("Failed to load integrations", error);
  }
};

const submitIntegration = async () => {
  submitting.value = true;
  try {
    await axios.post('/api/integrations/custom', form.value);
    toast.add({ severity: 'success', summary: 'Success', detail: 'Integration added successfully.', life: 3000 });
    showAddModal.value = false;
    form.value = { name: '', description: '', webhook_url: '', api_key: '' };
    loadIntegrations();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to add integration.', life: 3000 });
  } finally {
    submitting.value = false;
  }
};

const toggleConnect = (id, isCustom) => {
  if (connectedIds.value.includes(id)) {
    toast.add({
      severity: "info",
      summary: 'Already Connected',
      detail: "This integration is already active.",
      life: 3000,
    });
  } else {
    if (isCustom) {
      toast.add({ severity: "success", summary: 'Connected', detail: "Custom integration connected.", life: 3000 });
      connectedIds.value.push(id);
    } else {
      window.location.href = `/integrations/${id}/redirect`;
    }
  }
};

const isConnected = (id) => {
  return connectedIds.value.includes(id);
};

const filteredApps = computed(() => {
  let filtered = apps.value;
  
  // Filter by Search Value
  if (searchValue.value.trim() !== '') {
    const term = searchValue.value.toLowerCase();
    filtered = filtered.filter(app => 
      app.name.toLowerCase().includes(term) || 
      (app.desc && app.desc.toLowerCase().includes(term))
    );
  }
  
  // Filter by Active Tab
  if (activeTab.value === 'Connected') {
    filtered = filtered.filter(app => isConnected(app.id));
  } else if (activeTab.value === 'Disconnected') {
    filtered = filtered.filter(app => !isConnected(app.id));
  }
  
  return filtered;
});

</script>

<style>
/* Remove input focus outline */
input:focus {
  outline: none;
  box-shadow: none;
}
</style>
