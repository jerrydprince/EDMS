<template>
  <main class="h-full flex items-center justify-center p-6">
    <div class="text-center max-w-md bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
      <div class="mb-6 flex justify-center text-red-500">
        <Icon :icon="icon" width="64" />
      </div>
      <h1 class="font-bold text-4xl text-[#1D2939] dark:text-white mb-2">
        {{ title }}
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mb-8">
        {{ description }}
      </p>
      
      <div class="flex justify-center gap-4">
        <Button 
          label="Return to Dashboard" 
          icon="pi pi-home" 
          class="p-button-primary"
          @click="goHome"
        />
        <Button 
          v-if="status === 403"
          label="Go Back" 
          icon="pi pi-arrow-left" 
          class="p-button-outlined"
          @click="goBack"
        />
      </div>
    </div>
  </main>
</template>

<script>
import Layout from '../Shared/Layout.vue';

export default {
  layout: Layout,
}
</script>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Button from 'primevue/button';

const props = defineProps({
  status: Number,
});

const title = computed(() => {
  return {
    503: 'Service Unavailable',
    500: 'Server Error',
    404: 'Page Not Found',
    403: 'Access Denied',
  }[props.status] || 'Error';
});

const description = computed(() => {
  return {
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
    500: 'Whoops, something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page or resource.',
  }[props.status] || 'An unexpected error occurred.';
});

const icon = computed(() => {
  return {
    503: 'mdi:hammer-wrench',
    500: 'mdi:alert-circle',
    404: 'mdi:magnify-remove-outline',
    403: 'mdi:shield-lock-outline',
  }[props.status] || 'mdi:alert';
});

const goHome = () => {
  router.get('/dashboard');
};

const goBack = () => {
  window.history.back();
};
</script>
