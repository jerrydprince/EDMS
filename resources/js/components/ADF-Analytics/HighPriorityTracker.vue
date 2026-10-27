<template>
  <div class="rounded-2xl border border-gray-100 bg-white p-6 h-full shadow-[0_2px_20px_-5px_rgba(0,0,0,0.05)] relative overflow-hidden">
    <!-- Decorative background glow -->
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-100/50 rounded-full blur-3xl pointer-events-none"></div>

    <div class="flex items-center justify-between mb-6 relative z-10">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-red-50 text-red-500 rounded-xl">
          <Icon icon="solar:danger-triangle-bold-duotone" width="24" />
        </div>
        <h1 class="text-lg font-bold text-gray-800 tracking-tight">
          High Priority Tracker
        </h1>
      </div>
      <span class="bg-red-50 text-red-600 text-xs font-bold px-3 py-1 rounded-full border border-red-100 shadow-sm flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
        Action Required
      </span>
    </div>
    
    <div v-if="tasks.length === 0" class="text-center text-gray-400 py-12 relative z-10">
      <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <Icon icon="solar:check-circle-bold-duotone" width="32" class="text-green-500" />
      </div>
      <p class="font-medium text-gray-500">All caught up!</p>
      <p class="text-sm mt-1">No pending high-priority tasks found.</p>
    </div>

    <div v-else class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar relative z-10">
      <div v-for="(task, index) in tasks" :key="index" 
           class="p-4 border border-gray-100 rounded-xl hover:border-red-200 hover:shadow-md hover:bg-red-50/10 transition-all duration-300 group cursor-pointer bg-white">
        
        <div class="flex justify-between items-start mb-3">
          <h2 class="font-semibold text-gray-800 pr-4 group-hover:text-red-600 transition-colors line-clamp-2">
            {{ task.title || (task.workflow_version ? task.workflow_version.name : 'Untitled Task') }}
          </h2>
          <span class="bg-orange-50 text-orange-600 border border-orange-100 text-[10px] font-bold px-2 py-0.5 rounded-md whitespace-nowrap uppercase tracking-wider">
            {{ task.status }}
          </span>
        </div>
        
        <div class="flex flex-col gap-2 mt-auto">
          <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 p-2 rounded-lg group-hover:bg-white transition-colors border border-transparent group-hover:border-gray-50">
            <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
              {{ (task.assigned_by?.name || 'S').charAt(0).toUpperCase() }}
            </div>
            <span class="truncate">Assigned by <span class="font-semibold text-gray-700">{{ task.assigned_by?.name || 'System' }}</span></span>
          </div>
          
          <div class="flex items-center justify-between text-sm text-gray-500 px-1" v-if="task.due_date">
            <div class="flex items-center gap-1.5 text-red-500 font-medium bg-red-50 px-2 py-1 rounded-md">
              <Icon icon="solar:calendar-date-bold-duotone" />
              <span class="text-xs">Due {{ new Date(task.due_date).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }}</span>
            </div>
            <button class="text-primary hover:text-primary-dark font-medium text-xs opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
              View details <Icon icon="solar:arrow-right-line-duotone" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  tasks: {
    type: Array,
    required: true,
    default: () => []
  }
});
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
  background: #fca5a5;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #ef4444;
}
</style>
