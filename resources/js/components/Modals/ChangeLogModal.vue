<template>
  <Dialog v-model:visible="visible" modal header="Change Log" :style="{ width: '40rem', padding: '10px 0px' }" :pt="{ content: { class: 'px-6 bg-white pb-6 pt-4' } }">
    <template #header>
      <div class="flex items-center gap-3 -mb-6 px-6">
        <div class="rounded-full py-4 px-4 border border-border_primary border-solid">
          <Icon icon="pi pi-chart-pie" width="25" />
        </div>
        <div>
          <p class="mb-1 font-medium text-lg">Change Log</p>
          <p class="text-gray-500 text-xs">History of modifications</p>
        </div>
      </div>
    </template>
    
    <div class="mt-8 relative border-l border-gray-200 ml-3">                  
        <div v-if="loading" class="mb-4 ml-6">
            <p class="text-sm text-gray-500">Loading activities...</p>
        </div>
        <div v-else-if="!logs || logs.length === 0" class="mb-4 ml-6">
            <p class="text-sm text-gray-500">No activity logs found for this branch.</p>
        </div>
        
        <div v-else class="mb-8 ml-6" v-for="(log, idx) in logs" :key="idx">            
            <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white">
                <svg class="w-2.5 h-2.5 text-blue-800" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                </svg>
            </span>
            <h3 class="flex items-center mb-1 text-md font-semibold text-gray-900">
              {{ log.description }}
            </h3>
            <time class="block mb-2 text-sm font-normal leading-none text-gray-400">
              {{ new Date(log.created_at).toLocaleString() }}
            </time>
            <p class="mb-4 text-sm font-normal text-gray-500" v-if="log.causer">
              Performed by: {{ log.causer.first_name }} {{ log.causer.last_name }}
            </p>
        </div>
    </div>

    <template #footer>
      <Button class="mr-4" @click="closeModal" label="Close" severity="secondary" outlined />
    </template>
  </Dialog>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';
import axios from 'axios';

const props = defineProps(['modelValue', 'data', 'type']);
const emit = defineEmits(['update:modelValue']);
const visible = ref(props.modelValue);
const logs = ref([]);
const loading = ref(false);

watch(() => props.modelValue, (val) => {
    visible.value = val;
    if(val && props.data) {
        fetchLogs();
    }
});

watch(visible, (val) => {
    emit('update:modelValue', val);
});

const fetchLogs = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/dashboard/branch-setup/changelog/${props.type}/${props.data.id}`);
        logs.value = response.data;
    } catch(err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

const closeModal = () => {
    visible.value = false;
}
</script>
