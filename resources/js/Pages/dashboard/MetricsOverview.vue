<template>
  <div class="mb-8">
    <div class="flex items-center gap-3 mb-5">
      <div class="p-2 border rounded-full bg-primary/10 text-primary">
        <Icon icon="mdi:chart-bar" width="24" />
      </div>
      <h1 class="font-semibold text-2xl text-[#1D2939] dark:text-white">
        Performance Metrics
      </h1>
    </div>

    <div v-if="loading" class="text-gray-500">Loading metrics...</div>
    <div v-else-if="error" class="text-red-500">{{ error }}</div>
    <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
      
      <!-- Workload -->
      <div class="border rounded-lg p-4 bg-blue-50 dark:bg-gray-800">
        <p class="text-sm text-gray-500 font-medium">Workload (Active)</p>
        <p class="text-2xl font-bold text-blue-700 dark:text-blue-400 mt-1">{{ metrics.workload }}</p>
      </div>

      <!-- Pending -->
      <div class="border rounded-lg p-4 bg-orange-50 dark:bg-gray-800">
        <p class="text-sm text-gray-500 font-medium">Pending Tasks</p>
        <p class="text-2xl font-bold text-orange-700 dark:text-orange-400 mt-1">{{ metrics.pending }}</p>
      </div>

      <!-- Due Today -->
      <div class="border rounded-lg p-4 bg-yellow-50 dark:bg-gray-800">
        <p class="text-sm text-gray-500 font-medium">Due (Today)</p>
        <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400 mt-1">{{ metrics.due }}</p>
      </div>

      <!-- Overdue / SLA Breaches -->
      <div class="border rounded-lg p-4 bg-red-50 dark:bg-gray-800">
        <p class="text-sm text-gray-500 font-medium">Overdue (SLA Breached)</p>
        <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">{{ metrics.overdue }}</p>
      </div>

      <!-- Completed -->
      <div class="border rounded-lg p-4 bg-green-50 dark:bg-gray-800">
        <p class="text-sm text-gray-500 font-medium">Completed</p>
        <p class="text-2xl font-bold text-green-700 dark:text-green-400 mt-1">{{ metrics.completed }}</p>
      </div>

      <!-- Returned -->
      <div class="border rounded-lg p-4 bg-purple-50 dark:bg-gray-800">
        <p class="text-sm text-gray-500 font-medium">Returned</p>
        <p class="text-2xl font-bold text-purple-700 dark:text-purple-400 mt-1">{{ metrics.returned }}</p>
      </div>

      <!-- Mails Received -->
      <div class="border rounded-lg p-4 bg-slate-50 dark:bg-gray-800">
        <p class="text-sm text-gray-500 font-medium">Mails Dispatched</p>
        <p class="text-2xl font-bold text-slate-700 dark:text-slate-400 mt-1">{{ metrics.dispatched }}</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Icon } from '@iconify/vue';
import axios from 'axios';

const metrics = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchMetrics = async () => {
  try {
    const response = await axios.get('/metrics');
    metrics.value = response.data;
  } catch (err) {
    error.value = "Failed to load metrics. Please try again later.";
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchMetrics();
});
</script>
