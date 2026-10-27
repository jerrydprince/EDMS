<template>
  <div class="px-8 py-10 min-h-screen bg-gray-50 dark:bg-gray-900 font-sans">
    <div class="max-w-6xl mx-auto">
      <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
            <Icon icon="lucide:settings-2" class="text-primary" />
            System Configurations
          </h2>
          <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm max-w-xl leading-relaxed">
            Manage your environment configurations directly from the interface. These settings control core integrations like Outgoing SMTP and Incoming IMAP mail protocols.
          </p>
        </div>
        
        <button @click="saveConfigurations" :disabled="saving" class="bg-primary hover:bg-green-600 text-white px-6 py-2.5 rounded-xl font-medium transition-all shadow-sm hover:shadow-md flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
          <Icon v-if="saving" icon="svg-spinners:180-ring" width="20" />
          <Icon v-else icon="lucide:save" width="20" />
          <span>{{ saving ? 'Saving Changes...' : 'Save Configurations' }}</span>
        </button>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <Icon icon="svg-spinners:pulse-3" class="text-primary text-4xl mb-4" />
        <p class="text-gray-500 font-medium">Loading configurations...</p>
      </div>
      
      <div v-else class="space-y-8">
        
        <!-- Outgoing Mail Settings (SMTP) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all hover:shadow-md">
          <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center gap-3">
            <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
              <Icon icon="lucide:send" class="text-blue-600 dark:text-blue-400" width="22" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Outgoing Mail (SMTP)</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">Settings for sending emails from the system.</p>
            </div>
          </div>
          
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div v-for="(config, index) in smtpConfigs" :key="index" class="space-y-1.5 group">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">
                {{ formatKeyName(config.key) }}
              </label>
              <div class="relative">
                <input 
                  v-model="config.value"
                  :type="config.key.includes('password') ? 'password' : 'text'"
                  class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 rounded-xl px-4 py-2.5 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm"
                  :placeholder="`Enter ${formatKeyName(config.key)}`"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity">
                  <Icon icon="lucide:edit-3" class="text-gray-400 w-4 h-4" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Incoming Mail Settings (IMAP) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all hover:shadow-md">
          <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center gap-3">
            <div class="bg-purple-100 dark:bg-purple-900/30 p-2 rounded-lg">
              <Icon icon="lucide:inbox" class="text-purple-600 dark:text-purple-400" width="22" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Incoming Mail (IMAP)</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400">Settings for receiving external emails automatically.</p>
            </div>
          </div>
          
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div v-for="(config, index) in imapConfigs" :key="index" class="space-y-1.5 group">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ml-1">
                {{ formatKeyName(config.key) }}
              </label>
              <div class="relative">
                <input 
                  v-model="config.value"
                  :type="config.key.includes('password') ? 'password' : 'text'"
                  class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 rounded-xl px-4 py-2.5 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm"
                  :placeholder="`Enter ${formatKeyName(config.key)}`"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Custom / Advanced Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden transition-all hover:shadow-md">
          <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="bg-orange-100 dark:bg-orange-900/30 p-2 rounded-lg">
                <Icon icon="lucide:sliders" class="text-orange-600 dark:text-orange-400" width="22" />
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Advanced Parameters</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Custom configuration overrides for the system environment.</p>
              </div>
            </div>
            <button @click="addConfig" class="text-sm font-medium text-primary bg-primary/10 hover:bg-primary/20 px-4 py-2 rounded-lg transition-colors flex items-center gap-1.5">
              <Icon icon="lucide:plus" width="16" /> Add Parameter
            </button>
          </div>
          
          <div class="p-6">
            <div v-if="otherConfigs.length === 0" class="text-center py-8 text-gray-400 text-sm">
              No custom parameters defined yet.
            </div>
            
            <div v-else class="space-y-4">
              <div v-for="(config, index) in otherConfigs" :key="index" class="flex items-center gap-4 bg-gray-50 dark:bg-gray-900/50 p-2 pl-4 rounded-xl border border-gray-100 dark:border-gray-800">
                <div class="w-1/3">
                  <span class="text-sm font-medium font-mono text-gray-600 dark:text-gray-400">{{ config.key }}</span>
                </div>
                <div class="w-2/3 flex items-center gap-2">
                  <input 
                    v-model="config.value"
                    type="text"
                    class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm"
                  />
                  <button type="button" @click="removeConfig(config)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete parameter">
                    <Icon icon="lucide:trash-2" width="18" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>

    <!-- Custom Modal for Adding New Parameter -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
      <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Add New Parameter</h3>
          <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <Icon icon="lucide:x" width="20" />
          </button>
        </div>
        <div class="p-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Configuration Key
          </label>
          <input 
            v-model="newConfigKey"
            type="text"
            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 rounded-xl px-4 py-2.5 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all sm:text-sm"
            placeholder="e.g., api.timeout"
            @keyup.enter="confirmAddConfig"
            ref="newConfigInput"
          />
          <p class="text-xs text-gray-500 mt-2">Use dot notation for grouping, e.g. "mail.timeout"</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end gap-3">
          <button @click="showAddModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            Cancel
          </button>
          <button @click="confirmAddConfig" class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-green-600 rounded-lg transition-colors shadow-sm">
            Add Parameter
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Swal from 'sweetalert2';

import { ref, onMounted, computed } from 'vue';
import { useToast } from "primevue/usetoast";
import axios from 'axios';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const configurations = ref([]);
const showAddModal = ref(false);
const newConfigKey = ref('');

// Default required keys if the database is empty
const defaultKeys = [
  'mail.host',
  'mail.port',
  'mail.username',
  'mail.password',
  'mail.encryption',
  'imap.default_account',
  'imap.host',
  'imap.port',
  'imap.encryption',
  'imap.validate_cert',
  'imap.username',
  'imap.password',
  'imap.authentication'
];

const fetchConfigurations = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/settings/get-system-configurations?group=mail');
    let fetchedConfigs = response.data;
    
    let configMap = {};
    fetchedConfigs.forEach(c => {
      configMap[c.key] = c.value;
    });

    configurations.value = defaultKeys.map(key => {
      return {
        key: key,
        value: configMap[key] || '',
        group: 'mail'
      };
    });

    fetchedConfigs.forEach(c => {
      if (!defaultKeys.includes(c.key)) {
        configurations.value.push({
          key: c.key,
          value: c.value,
          group: 'mail'
        });
      }
    });
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load configurations.', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const smtpConfigs = computed(() => configurations.value.filter(c => c.key.startsWith('mail.')));
const imapConfigs = computed(() => configurations.value.filter(c => c.key.startsWith('imap.')));
const otherConfigs = computed(() => configurations.value.filter(c => !c.key.startsWith('mail.') && !c.key.startsWith('imap.')));

const formatKeyName = (key) => {
  // e.g., 'mail.host' -> 'Host'
  // 'imap.default_account' -> 'Default Account'
  let parts = key.split('.');
  let lastPart = parts[parts.length - 1];
  return lastPart.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const addConfig = () => {
  newConfigKey.value = '';
  showAddModal.value = true;
};

const confirmAddConfig = () => {
  const newKey = newConfigKey.value;
  if (newKey && newKey.trim() !== '') {
    // Check for duplicates
    if(configurations.value.some(c => c.key === newKey.trim())) {
      toast.add({ severity: 'warn', summary: 'Warning', detail: 'Key already exists.', life: 3000 });
      return;
    }
    configurations.value.push({
      key: newKey.trim(),
      value: '',
      group: 'mail'
    });
    showAddModal.value = false;
  }
};

const removeConfig = (config) => {
  Swal.fire({
    title: 'Confirm',
    text: `Remove the parameter "${config.key}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
    const index = configurations.value.findIndex(c => c.key === config.key);
    if (index !== -1) {
      configurations.value.splice(index, 1);
    }
  }
  });
};

const saveConfigurations = async () => {
  try {
    saving.value = true;
    await axios.post('/settings/system-configurations', {
      configs: configurations.value
    });
    toast.add({ severity: 'success', summary: 'Success', detail: 'Configurations saved successfully!', life: 3000 });
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save configurations.', life: 3000 });
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchConfigurations();
});
</script>
