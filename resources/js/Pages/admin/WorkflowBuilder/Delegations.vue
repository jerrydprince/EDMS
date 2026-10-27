<template>
  <main class="p-4 lg:p-7">
    <div class="mb-4">
      <Link href="/dashboard/workflow-builder" class="text-blue-600 hover:underline flex items-center gap-1">
        <Icon icon="lucide:arrow-left" width="16" /> Back to Workflow Builder
      </Link>
    </div>

    <section class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8">
      <div>
        <p class="text-3xl text-gray-800 font-semibold">Workflow Delegations</p>
        <p class="text-gray-500 mt-1">Manage task delegations for yourself or others (Admins).</p>
      </div>
      <button @click="isModalVisible = true" class="mt-4 md:mt-0 flex gap-2 items-center text-white bg-blue-600 hover:bg-blue-700 rounded-md py-1.5 px-4 transition-colors">
        <Icon icon="lucide:plus" width="18" /> Add Delegation
      </button>
    </section>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200">
            <th class="p-4 font-semibold text-gray-700">Delegator</th>
            <th class="p-4 font-semibold text-gray-700">Delegatee</th>
            <th class="p-4 font-semibold text-gray-700">Start Date</th>
            <th class="p-4 font-semibold text-gray-700">End Date</th>
            <th class="p-4 font-semibold text-gray-700">Status</th>
            <th class="p-4 font-semibold text-gray-700 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="del in delegations" :key="del.id" class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <td class="p-4">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
                  {{ del.delegator ? del.delegator.first_name.charAt(0) : '?' }}
                </div>
                <div>
                  <p class="font-semibold text-gray-800">{{ del.delegator ? del.delegator.first_name + ' ' + del.delegator.last_name : 'Unknown' }}</p>
                </div>
              </div>
            </td>
            <td class="p-4">
              <p class="text-gray-800">{{ del.delegatee ? del.delegatee.first_name + ' ' + del.delegatee.last_name : 'Unknown' }}</p>
            </td>
            <td class="p-4 text-gray-600">{{ del.start_date ? new Date(del.start_date).toLocaleDateString() : 'N/A' }}</td>
            <td class="p-4 text-gray-600">{{ del.end_date ? new Date(del.end_date).toLocaleDateString() : 'N/A' }}</td>
            <td class="p-4">
              <span v-if="del.is_active" class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Active</span>
              <span v-else class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full">Inactive</span>
            </td>
            <td class="p-4 text-right">
              <button @click="deleteDelegation(del.id)" class="text-red-500 hover:text-red-700 p-1">
                <Icon icon="lucide:trash-2" width="18" />
              </button>
            </td>
          </tr>
          <tr v-if="delegations.length === 0">
            <td colspan="6" class="p-8 text-center text-gray-500">
              <Icon icon="lucide:users" width="48" class="mx-auto text-gray-300 mb-3" />
              <p>No delegations found.</p>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create Modal -->
    <Dialog v-model:visible="isModalVisible" header="Add Delegation" modal :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitForm" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-2" v-if="isAdmin">
          <label class="font-medium text-gray-700">Delegator (User going away)</label>
          <select v-model="form.delegator_user_id" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
            <option :value="null">Myself</option>
            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
          </select>
          <p class="text-xs text-gray-500">Leave blank to delegate your own tasks.</p>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Delegatee (User taking over) *</label>
          <select v-model="form.delegatee_user_id" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            <option :value="null" disabled>Select User</option>
            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="flex flex-col gap-2">
            <label class="font-medium text-gray-700">Start Date</label>
            <input v-model="form.start_date" type="date" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" />
          </div>
          <div class="flex flex-col gap-2">
            <label class="font-medium text-gray-700">End Date</label>
            <input v-model="form.end_date" type="date" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" />
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            Save Delegation
          </button>
        </div>
      </form>
    </Dialog>
  </main>
</template>

<script>
import Layout from "../../../Shared/Layout.vue";
export default {
  layout: Layout
}
</script>

<script setup>
import Swal from 'sweetalert2';

import { ref, computed } from 'vue';
import { useForm, Link, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import Dialog from 'primevue/dialog';

const props = defineProps({
  delegations: Array,
  users: Array
});

const page = usePage();
const isAdmin = computed(() => {
  const roles = page.props.roles || [];
  return roles.includes('super admin') || roles.includes('super user');
});

const isModalVisible = ref(false);

const form = useForm({
  delegator_user_id: null,
  delegatee_user_id: null,
  start_date: '',
  end_date: ''
});

const submitForm = () => {
  form.post('/dashboard/workflow-delegations', {
    onSuccess: () => {
      isModalVisible.value = false;
      form.reset();
    }
  });
};

const deleteDelegation = (id) => {
  Swal.fire({
    title: 'Confirm',
    text: 'Are you sure you want to remove this delegation?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
    router.delete(`/dashboard/workflow-delegations/${id}`);
  }
  });
};
</script>
