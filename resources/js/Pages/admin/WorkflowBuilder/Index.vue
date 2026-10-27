<template>
  <main class="py-3 px-2 sm:px-3 lg:px-4 w-full">
    <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
      <p class="text-2xl md:text-3xl text-gray-800 font-semibold">
        Workflow Builder
      </p>
      <Link href="/dashboard/workflow-builder/studio"
        class="mt-4 md:mt-0 flex gap-2 items-center text-white bg-blue-600 hover:bg-blue-700 border-transparent border-solid border rounded-lg py-2 px-3.5 cursor-pointer transition-colors shadow-sm font-medium text-xs md:text-sm">
        <Icon icon="ion:add-outline" width="20" />
        <p>Design Workflow in Studio</p>
      </Link>
    </section>
    
    <section class="mt-6">
      <div class="border border-solid border-gray-300 rounded-lg mt-2 bg-white p-3 md:p-3.5">
        
        <div v-if="workflows && workflows.length > 0">
          <DataTable :value="workflows" :paginator="true" :rows="10" tableStyle="min-width: 60rem" 
            rowGroupMode="subheader" groupRowsBy="department.title" 
            sortMode="single" sortField="department.title" :sortOrder="1"
            expandableRowGroups v-model:expandedRowGroups="expandedRowGroups">
            
            <template #groupheader="slotProps">
                <span class="font-bold text-gray-800 text-lg ml-2 flex items-center cursor-pointer">
                    <Icon :icon="expandedRowGroups.includes(slotProps.data.department ? slotProps.data.department.title : 'Global Workflows') ? 'lucide:folder-open' : 'lucide:folder'" class="inline mr-2 text-blue-600" width="20" />
                    {{ slotProps.data.department ? slotProps.data.department.title : 'Global Workflows' }}
                </span>
            </template>
            <Column field="department.title" header="Department" class="hidden"></Column>
            <Column field="title" header="Workflow Title">
              <template #body="slotProps">
                <div class="flex items-center gap-2">
                  <span class="font-semibold">{{ slotProps.data.emoji }} {{ slotProps.data.title }}</span>
                  <Icon v-if="slotProps.data.is_confidential" icon="lucide:lock" class="text-red-500" width="16" title="Confidential Workflow" />
                </div>
              </template>
            </Column>
            <Column header="Institutional Scope & Authority">
              <template #body="slotProps">
                <div v-if="slotProps.data.department_id">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200" :title="'Departmental Workflow - Strictly initiated by Director of ' + (slotProps.data.department?.title || 'Department')">
                    <Icon icon="lucide:shield-check" width="13" class="text-indigo-600" />
                    Departmental (Director Only)
                  </span>
                </div>
                <div v-else>
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <Icon icon="lucide:globe" width="13" class="text-emerald-600" />
                    Global (Institutional)
                  </span>
                </div>
              </template>
            </Column>
            <Column header="Attached Document Template">
              <template #body="slotProps">
                <div v-if="slotProps.data.document_template" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full">
                  <Icon icon="lucide:file-text" width="13" class="text-blue-600" />
                  <span>{{ slotProps.data.document_template.title }}</span>
                </div>
                <span v-else class="text-gray-400 text-xs italic">No Template Attached</span>
              </template>
            </Column>
            <Column header="Total Versions">
              <template #body="slotProps">
                {{ slotProps.data.versions ? slotProps.data.versions.length : 0 }}
              </template>
            </Column>
            <Column header="Current Active Version">
              <template #body="slotProps">
                <span v-if="getActiveVersion(slotProps.data)" class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                  v{{ getActiveVersion(slotProps.data).version_number }}
                </span>
                <span v-else class="text-gray-500 italic">None Published</span>
              </template>
            </Column>
            <Column header="Actions">
              <template #body="slotProps">
                <div class="flex items-center gap-3">
                    <Link :href="getLatestVersionId(slotProps.data) ? `/dashboard/workflow-builder/studio/${getLatestVersionId(slotProps.data)}` : `/dashboard/workflow-builder/studio?workflow_id=${slotProps.data.id}`" class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 text-xs">
                      <Icon icon="lucide:edit-3" width="15" /> Edit in Studio
                    </Link>
                    <button v-if="slotProps.data.status !== 'inactive'" @click="toggleStatus(slotProps.data)" class="text-amber-600 hover:text-amber-800 flex items-center gap-1 ml-2 text-xs font-medium" title="Deactivate Workflow">
                      <Icon icon="lucide:power-off" width="15" /> Deactivate
                    </button>
                    <button v-else @click="toggleStatus(slotProps.data)" class="text-green-600 hover:text-green-800 flex items-center gap-1 ml-2 text-xs font-medium" title="Activate Workflow">
                      <Icon icon="lucide:power" width="15" /> Activate
                    </button>
                    <button v-if="!getActiveVersion(slotProps.data)" @click="deleteWorkflow(slotProps.data)" class="text-red-600 hover:text-red-900 flex items-center gap-1 ml-2 text-xs font-medium" title="Delete Workflow">
                      <Icon icon="lucide:trash-2" width="15" /> Delete
                    </button>
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
        
        <div v-else class="text-center py-10">
          <Icon icon="lucide:git-pull-request" width="50" class="mx-auto text-gray-400 mb-3" />
          <p class="text-gray-600 text-lg">No generic workflows created yet.</p>
          <p class="text-gray-500 text-sm mt-1">Create a workflow to start building custom multi-step processes.</p>
        </div>
        
      </div>
    </section>

    <!-- Modal for Create -->
    <Dialog v-model:visible="isModalVisible" header="Create Workflow" :style="{ width: '40vw' }" :breakpoints="{ '960px': '75vw', '641px': '100vw' }">
      <form @submit.prevent="submitForm" class="flex flex-col gap-4 mt-2">
        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Workflow Title *</label>
          <input v-model="form.title" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Leave Request" required />
          <span v-if="form.errors.title" class="text-red-500 text-sm">{{ form.errors.title }}</span>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Emoji / Icon</label>
          <input v-model="form.emoji" type="text" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 📄" />
          <span v-if="form.errors.emoji" class="text-red-500 text-sm">{{ form.errors.emoji }}</span>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-medium text-gray-700">Workflow Scope & Department</label>
          <select v-model="form.department_id" class="border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
            <option :value="null">Global (Available Institutional-wide)</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              Departmental: {{ dept.title }}
            </option>
          </select>
          <span v-if="form.errors.department_id" class="text-red-500 text-sm">{{ form.errors.department_id }}</span>

          <div v-if="form.department_id" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-start gap-2.5 mt-1">
            <Icon icon="lucide:shield-alert" width="18" class="text-amber-600 shrink-0 mt-0.5" />
            <div>
              <strong>Director-Only Initiation:</strong> In accordance with civil service rules, departmental workflows are private to the department and can strictly only be triggered or initiated by the <strong>Director</strong> of that department.
            </div>
          </div>
          <div v-else class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-start gap-2.5 mt-1">
            <Icon icon="lucide:globe" width="18" class="text-blue-600 shrink-0 mt-0.5" />
            <div>
              <strong>Global Scope:</strong> This workflow is institutional and can be initiated across all directorates and offices.
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 mt-2">
          <input id="is_confidential" v-model="form.is_confidential" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" />
          <label for="is_confidential" class="font-medium text-gray-700 flex items-center gap-1">
            <Icon icon="lucide:lock" width="16" class="text-red-500" />
            Confidential Workflow
          </label>
        </div>
        <p v-if="form.is_confidential" class="text-xs text-gray-500 ml-6">
          This workflow and its documents will be hidden from standard registries and searches.
        </p>

        <div class="flex justify-end gap-3 mt-4">
          <button type="button" @click="isModalVisible = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Creating...' : 'Create Workflow' }}
          </button>
        </div>
      </form>
    </Dialog>
    <ConfirmDialog></ConfirmDialog>
  </main>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import Layout from "../../../Shared/Layout.vue";

const __default__ = {
  layout: Layout,
}

const props = defineProps({
  workflows: Array,
  departments: Array
});

const isModalVisible = ref(false);

const form = useForm({
  title: '',
  emoji: '📄',
  department_id: null,
  is_confidential: false
});

// For expanding/collapsing row groups
// Initialize with 'Administration' expanded if it exists, or empty
const defaultExpanded = props.workflows.map(w => w.department ? w.department.title : 'Global Workflows');
const expandedRowGroups = ref([...new Set(defaultExpanded)]);
const confirm = useConfirm();

const toggleStatus = (workflow) => {
  form.post(`/dashboard/workflow-builder/${workflow.id}/toggle-status`, {
    preserveScroll: true
  });
};

const deleteWorkflow = (workflow) => {
  confirm.require({
    message: `Are you sure you want to completely delete "${workflow.title}"? This cannot be undone.`,
    header: 'Confirm Deletion',
    icon: 'pi pi-exclamation-triangle',
    accept: () => {
      form.delete(`/dashboard/workflow-builder/${workflow.id}`, {
        preserveScroll: true
      });
    }
  });
};

const openModal = () => {
  form.reset();
  form.clearErrors();
  isModalVisible.value = true;
};

const submitForm = () => {
  form.post('/dashboard/workflow-builder', {
    onSuccess: () => {
      isModalVisible.value = false;
      form.reset();
    }
  });
};

const getActiveVersion = (workflow) => {
  if (!workflow.versions || workflow.versions.length === 0) return null;
  return workflow.versions.find(v => v.is_published);
};

const getLatestVersionId = (workflow) => {
  if (!workflow.versions || workflow.versions.length === 0) return null;
  return workflow.versions[0].id;
};
</script>
