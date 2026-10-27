<template>
  <div class="mt-2 mb-8">
    <div class="flex items-center gap-3 mb-6">
      <div class="h-8 w-2 bg-primary rounded-full"></div>
      <h1 class="text-2xl font-bold text-gray-800 tracking-tight">
        Executive Summary
      </h1>
    </div>
    
    <div class="grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
      <div v-for="(item, index) in data" :key="index" 
           class="relative overflow-hidden bg-white rounded-2xl p-6 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.05)] border border-gray-100 hover:shadow-[0_10px_30px_-5px_rgba(0,0,0,0.1)] hover:-translate-y-1 transition-all duration-300 group">
        
        <div class="absolute -right-6 -top-6 p-4 opacity-5 group-hover:opacity-10 group-hover:scale-110 transition-all duration-500 text-primary">
           <Icon :icon="getIcon(index)" width="120" />
        </div>

        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-4">
            <div class="p-2.5 bg-primary/10 rounded-xl text-primary">
              <Icon :icon="getIcon(index)" width="22" />
            </div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ item.title }}</h2>
          </div>
          
          <div class="flex items-end gap-3">
            <p class="text-4xl font-extrabold text-gray-900 tracking-tight">{{ item.count }}</p>
          </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-primary/40 to-primary/10 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-left"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  }
});

const getIcon = (index) => {
  const icons = [
    'solar:document-text-bold-duotone',
    'solar:users-group-two-rounded-bold-duotone',
    'solar:checklist-minimalistic-bold-duotone',
    'solar:buildings-bold-duotone',
    'solar:city-bold-duotone',
    'solar:widget-5-bold-duotone'
  ];
  return icons[index % icons.length];
};
</script>

<style>
</style>
