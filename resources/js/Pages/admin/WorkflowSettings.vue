<template>
  <main class="p-4 lg:p-7">
    <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
      <p class="text-3xl text-gray-800 font-semibold">
        Workflow Rules Configuration
      </p>
      <div @click="openModal('create')"
        class="mt-4 md:mt-0 flex gap-2 items-center text-white bg-blue-600 hover:bg-blue-700 border-transparent border-solid border rounded-lg py-2 px-4 cursor-pointer transition-colors">
        <Icon icon="ion:add-outline" width="25" />
        <p>Create Workflow Rule</p>
      </div>
    </section>
    
    <section class="mt-8">
      <div class="border border-solid border-gray-300 rounded-lg mt-3 bg-white p-4">
        
        <div v-if="rules && rules.length > 0">
          <DataTable :value="rules" :paginator="true" :rows="10" tableStyle="min-width: 60rem">
            <Column field="workflow_type" header="Workflow Type"></Column>
            <Column field="department.title" header="Department Scope">
              <template #body="slotProps">
                <span v-if="slotProps.data.department">{{ slotProps.data.department.title }}</span>
                <span v-else class="text-gray-500 italic">Global (All Departments)</span>
              </template>
            </Column>
            <Column field="rule_key" header="Rule Key"></Column>
            <Column field="rule_value" header="Rule Value"></Column>
            <Column field="description" header="Description"></Column>
            <Column header="Actions">
              <template #body="slotProps">
                <div class="flex items-center gap-3">
                  <div @click="openModal('edit', slotProps.data)" class="cursor-pointer text-blue-600 hover:text-blue-800">
                    <Icon icon="lucide:edit" width="20" />
                  </div>
                  <div @click="confirmDelete(slotProps.data.id)" class="cursor-pointer text-red-600 hover:text-red-800">
                    <Icon icon="lucide:trash-2" width="20" />
                  </div>
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
        
        <div v-else class="text-center py-10">
          <Icon icon="lucide:settings" width="50" class="mx-auto text-gray-400 mb-3" />
          <p class="text-gray-600 text-lg">No workflow rules configured yet.</p>
          <p class="text-gray-500 text-sm mt-1">Create a rule to customize approval chains and thresholds.</p>
        </div>
        
      </div>
    </section>

    <!-- Modal for Create/Edit -->
    <Dialog v-model:visible="isModalVisible" :header="modalMode === 'create' ? 'Create Workflow Rule' : 'Edit Workflow Rule'" :style="{ width: '50vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitForm" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Workflow Type *</label>
          <input v-model="form.workflow_type" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Leave Request" required />
          <span v-if="form.errors.workflow_type" class="text-red-500 text-sm">{{ form.errors.workflow_type }}</span>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Department Scope</label>
          <select v-model="form.department_id" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
            <option :value="null">Global (All Departments)</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.title }}</option>
          </select>
          <span v-if="form.errors.department_id" class="text-red-500 text-sm">{{ form.errors.department_id }}</span>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Rule Key *</label>
          <input v-model="form.rule_key" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. approval_threshold" required />
          <span v-if="form.errors.rule_key" class="text-red-500 text-sm">{{ form.errors.rule_key }}</span>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Rule Value *</label>
          <input v-model="form.rule_value" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="JSON or Value" required />
          <span v-if="form.errors.rule_value" class="text-red-500 text-sm">{{ form.errors.rule_value }}</span>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Description</label>
          <textarea v-model="form.description" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" rows="3" placeholder="Explain the purpose of this rule"></textarea>
          <span v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</span>
        </div>

        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Saving...' : 'Save Rule' }}
          </button>
        </div>
      </form>
    </Dialog>

  </main>
</template>

<script setup>
import Swal from 'sweetalert2';

import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';

const props = defineProps({
  rules: Array,
  departments: Array
});

const isModalVisible = ref(false);
const modalMode = ref('create');
const activeRuleId = ref(null);

const form = useForm({
  workflow_type: '',
  department_id: null,
  rule_key: '',
  rule_value: '',
  description: ''
});

const openModal = (mode, data = null) => {
  modalMode.value = mode;
  form.clearErrors();
  
  if (mode === 'edit' && data) {
    activeRuleId.value = data.id;
    form.workflow_type = data.workflow_type;
    form.department_id = data.department_id;
    form.rule_key = data.rule_key;
    form.rule_value = data.rule_value;
    form.description = data.description;
  } else {
    activeRuleId.value = null;
    form.reset();
  }
  
  isModalVisible.value = true;
};

const submitForm = () => {
  if (modalMode.value === 'create') {
    form.post('/dashboard/workflow-settings', {
      onSuccess: () => {
        isModalVisible.value = false;
        form.reset();
      }
    });
  } else {
    form.post(`/dashboard/workflow-settings/${activeRuleId.value}`, {
      onSuccess: () => {
        isModalVisible.value = false;
        form.reset();
      }
    });
  }
};

const confirmDelete = (id) => {
  Swal.fire({
    title: 'Confirm',
    text: 'Are you sure you want to delete this workflow rule? This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
    router.get(`/dashboard/delete-workflow-settings/${id}`);
  }
  });
};
</script>

<script>
import Layout from "../../Shared/Layout.vue";
export default {
  layout: Layout,
};
</script>
