<template>
  <div class="rounded-2xl border border-gray-100 bg-white p-6 h-full shadow-[0_2px_20px_-5px_rgba(0,0,0,0.05)]">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl">
          <Icon icon="solar:chart-square-bold-duotone" width="24" />
        </div>
        <h1 class="text-lg font-bold text-gray-800 tracking-tight">
          Departmental Flow
        </h1>
      </div>
      <button class="text-gray-400 hover:text-primary transition-colors">
        <Icon icon="solar:menu-dots-circle-bold-duotone" width="24" />
      </button>
    </div>
    
    <div class="space-y-5 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
      <div v-if="chartData.length === 0" class="text-center py-10 text-gray-400">
        <Icon icon="solar:inbox-line-duotone" width="48" class="mx-auto mb-3 opacity-50" />
        <p>No department data available</p>
      </div>
      
      <div v-for="(item, index) in chartData" :key="index" class="group">
        <div class="flex justify-between text-sm mb-2">
          <span class="font-semibold text-gray-700 truncate pr-4">{{ item.department }}</span>
          <span class="font-medium text-gray-500 whitespace-nowrap">{{ item.completed }} / {{ item.pending + item.completed }}</span>
        </div>
        
        <div class="w-full bg-gray-100 rounded-full h-2.5 mb-1 overflow-hidden relative flex">
          <div class="bg-gradient-to-r from-green-400 to-green-500 h-2.5 rounded-l-full transition-all duration-700" 
               :style="{ width: getPercentage(item.completed, item.pending) + '%' }"></div>
          <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 h-2.5 rounded-r-full transition-all duration-700" 
               :style="{ width: getPercentage(item.pending, item.completed) + '%' }"></div>
        </div>
        
        <div class="flex justify-between text-[11px] text-gray-400 font-medium">
          <span class="text-green-600">{{ item.completed }} Completed</span>
          <span class="text-yellow-600">{{ item.pending }} Pending</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  chartData: {
    type: Array,
    required: true,
    default: () => []
  }
});

const getPercentage = (value, otherValue) => {
  const total = value + otherValue;
  if (total === 0) return 0;
  return (value / total) * 100;
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
