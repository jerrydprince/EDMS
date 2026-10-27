<template>
  <main class="p-4 lg:p-7 mt-6 lg:mt-5">
    <div class="mb-4">
      <Link href="/dashboard" class="text-blue-600 hover:underline flex items-center gap-1">
        <Icon icon="lucide:arrow-left" width="16" /> Back to Dashboard
      </Link>
    </div>
    
    <section class="flex flex-col lg:flex-row items-start lg:items-center justify-between">
      <div>
        <p class="text-2xl lg:text-3xl text-gray-800 font-semibold">
          Working Calendar & SLA Exclusions
        </p>
        <p class="text-gray-500 mt-1">Configure business operating hours and holidays for accurate SLA tracking.</p>
      </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
      
      <!-- Working Days -->
      <section class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <Icon icon="lucide:calendar-clock" width="22" class="text-blue-600"/>
            Operating Hours
          </h2>
          <button @click="saveWorkingDays" :disabled="isSavingDays" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
            {{ isSavingDays ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>

        <div class="flex flex-col gap-3">
          <div v-for="day in daysForm.days" :key="day.id" class="flex flex-col sm:flex-row sm:items-center justify-between border border-gray-100 p-3 rounded-lg bg-gray-50 gap-4">
            <div class="flex items-center gap-3 w-40">
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="day.is_working_day" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
              <span class="font-medium text-gray-700">{{ day.day_name }}</span>
            </div>
            
            <div class="flex items-center gap-2" :class="{'opacity-50 pointer-events-none': !day.is_working_day}">
              <input type="time" v-model="day.start_time" step="1" class="border border-gray-300 rounded-md p-1.5 text-sm w-28 focus:ring-blue-500 focus:border-blue-500" required>
              <span class="text-gray-400">to</span>
              <input type="time" v-model="day.end_time" step="1" class="border border-gray-300 rounded-md p-1.5 text-sm w-28 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
          </div>
        </div>
      </section>

      <!-- Holidays -->
      <section class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
        <div class="flex justify-between items-center mb-5">
          <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <Icon icon="lucide:calendar-off" width="22" class="text-orange-600"/>
            Holiday Exclusions
          </h2>
          <button @click="isHolidayModalOpen = true" class="text-sm bg-orange-50 text-orange-600 px-4 py-2 rounded-lg hover:bg-orange-100 border border-orange-200 transition-colors">
            + Add Holiday
          </button>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
              <tr>
                <th scope="col" class="px-4 py-3">Holiday Name</th>
                <th scope="col" class="px-4 py-3">Date</th>
                <th scope="col" class="px-4 py-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="holiday in holidays" :key="holiday.id" class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-900">{{ holiday.name }}</td>
                <td class="px-4 py-3">{{ formatDate(holiday.holiday_date) }}</td>
                <td class="px-4 py-3 text-right">
                  <button @click="deleteHoliday(holiday.id)" class="text-red-500 hover:text-red-700" title="Remove Holiday">
                    <Icon icon="lucide:trash-2" width="18" />
                  </button>
                </td>
              </tr>
              <tr v-if="holidays.length === 0">
                <td colspan="3" class="px-4 py-8 text-center text-gray-400">No holidays configured.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      
    </div>

    <!-- Add Holiday Modal -->
    <Dialog v-model:visible="isHolidayModalOpen" header="Add Holiday Exclusion" :style="{ width: '30vw' }" :breakpoints="{ '960px': '50vw', '641px': '95vw' }">
      <form @submit.prevent="submitHolidayForm" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Holiday Name *</label>
          <input v-model="holidayForm.name" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Independence Day" required />
        </div>
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Date *</label>
          <input v-model="holidayForm.holiday_date" type="date" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required />
        </div>
        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isHolidayModalOpen = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="holidayForm.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Save Holiday
          </button>
        </div>
      </form>
    </Dialog>
    
  </main>
</template>

<script setup>
import Swal from 'sweetalert2';

import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';
import Layout from "../../Shared/Layout.vue";
import { format } from 'date-fns';

const __default__ = {
  layout: Layout,
}

const props = defineProps({
  workingDays: Array,
  holidays: Array
});

// Working Days logic
const daysForm = useForm({
  days: JSON.parse(JSON.stringify(props.workingDays)) // Deep copy
});
const isSavingDays = ref(false);

const saveWorkingDays = () => {
  isSavingDays.value = true;
  daysForm.post('/dashboard/working-calendar/days', {
    preserveScroll: true,
    onSuccess: () => {
      // Optional: show toast
    },
    onFinish: () => {
      isSavingDays.value = false;
    }
  });
};

// Holidays logic
const isHolidayModalOpen = ref(false);
const holidayForm = useForm({
  name: '',
  holiday_date: ''
});

const submitHolidayForm = () => {
  holidayForm.post('/dashboard/working-calendar/holidays', {
    preserveScroll: true,
    onSuccess: () => {
      isHolidayModalOpen.value = false;
      holidayForm.reset();
    }
  });
};

const deleteHoliday = (id) => {
  Swal.fire({
    title: 'Confirm',
    text: 'Are you sure you want to remove this holiday?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
    router.delete(`/dashboard/working-calendar/holidays/${id}`, { preserveScroll: true });
  }
  });
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return format(new Date(dateString), 'PPP');
};
</script>
