<template>
  <main class="min-h-screen bg-slate-50/60 p-5 md:p-8">
    <!-- Header -->
    <div class="mb-7 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="px-2.5 py-0.5 text-xs font-bold font-mono uppercase bg-indigo-100 text-indigo-800 rounded border border-indigo-200">
            Organizational Structure Engine
          </span>
          <span class="px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800 rounded border border-emerald-200">
            Dynamic Governance
          </span>
        </div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">
          Role Titles & Functional Designations
        </h1>
        <p class="text-xs md:text-sm text-gray-500 mt-1">
          Configure office desks, registry positions, and functional roles across all OAGF departments and divisions.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <Link href="/dashboard/governance-hub" class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 shadow-2xs transition-all flex items-center gap-2">
          <Icon icon="lucide:network" width="16" class="text-indigo-600" />
          <span>Unified Governance Hub</span>
        </Link>
        <Link href="/dashboard/access-level" class="px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-xl border border-gray-200 shadow-2xs transition-all flex items-center gap-2">
          <Icon icon="mdi:shield-key-outline" width="16" class="text-indigo-600" />
          <span>Access Levels (Roles)</span>
        </Link>
        <button 
          @click="openCreateModal"
          class="px-5 py-2.5 bg-primary hover:bg-primary/90 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow-md transition-all flex items-center gap-2 transform hover:-translate-y-0.5"
        >
          <Icon icon="lucide:plus" width="16" />
          <span>Create Designation</span>
        </button>
      </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
      <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Designations</span>
          <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
            <Icon icon="lucide:badge-check" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-gray-900">{{ designations.total || designations.data?.length || 0 }}</p>
        <p class="text-xs text-gray-500 mt-1">Configured across all hierarchies</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Registry Desks</span>
          <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
            <Icon icon="lucide:inbox" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-emerald-900">{{ registryCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Central & Departmental intakes</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-bold uppercase tracking-wider text-purple-600">Executive Heads</span>
          <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
            <Icon icon="lucide:crown" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-purple-900">{{ headOfOfficeCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Directors, DDs & Unit Heads</p>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
        <div class="flex items-center justify-between mb-3">
          <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Global Roles</span>
          <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
            <Icon icon="lucide:globe" width="18" />
          </div>
        </div>
        <p class="text-2xl font-black text-amber-900">{{ globalCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Available to any branch</p>
      </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-2xs overflow-hidden">
      <!-- Search & Filters -->
      <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="w-full md:w-80 relative">
          <Icon icon="lucide:search" class="absolute left-3.5 top-3 text-gray-400" width="16" />
          <input 
            v-model="searchQuery" 
            placeholder="Search by title, cadre..." 
            class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-gray-300 focus:border-primary focus:ring-primary/20"
          />
        </div>

        <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
          <select v-model="selectedHierarchy" class="text-xs rounded-xl border-gray-300 py-2 px-3 focus:ring-primary/20">
            <option value="">All Hierarchy Levels</option>
            <option value="global">Global (Any Branch)</option>
            <option value="department">Department Level</option>
            <option value="division">Division Level</option>
            <option value="unit">Unit Level</option>
            <option value="section">Section Level</option>
          </select>

          <select v-model="selectedDepartment" class="text-xs rounded-xl border-gray-300 py-2 px-3 focus:ring-primary/20">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.title }}</option>
          </select>

          <button 
            v-if="searchQuery || selectedHierarchy || selectedDepartment" 
            @click="clearFilters"
            class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2 py-1"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600">
          <thead class="bg-gray-50/80 text-gray-500 font-bold uppercase tracking-wider border-b border-gray-200">
            <tr>
              <th class="py-3 px-5">Designation Title</th>
              <th class="py-3 px-4">Cadre / Field</th>
              <th class="py-3 px-4">Hierarchy Level</th>
              <th class="py-3 px-4">Department Scope</th>
              <th class="py-3 px-4 text-center">Grade Level</th>
              <th class="py-3 px-4 text-center">Desk Flags</th>
              <th class="py-3 px-4 text-center">Status</th>
              <th class="py-3 px-5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="item in filteredDesignationsList" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="py-3.5 px-5 font-bold text-gray-900">
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full" :class="item.is_active ? 'bg-emerald-500' : 'bg-gray-300'"></div>
                  <span>{{ item.title }}</span>
                </div>
                <p v-if="item.description" class="text-[11px] font-normal text-gray-400 mt-0.5 line-clamp-1">
                  {{ item.description }}
                </p>
              </td>

              <td class="py-3.5 px-4 text-gray-600 font-medium">
                {{ item.cadre || 'General Administrative' }}
              </td>

              <td class="py-3.5 px-4">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="getHierarchyBadgeClass(item.hierarchy_level)">
                  {{ item.hierarchy_level }}
                </span>
              </td>

              <td class="py-3.5 px-4 text-gray-700">
                <span v-if="item.department">{{ item.department.title }}</span>
                <span v-else class="text-gray-400 italic">Global (All Depts)</span>
              </td>

              <td class="py-3.5 px-4 text-center font-mono font-bold text-gray-800">
                {{ item.grade_level ? `GL ${item.grade_level}` : '-' }}
              </td>

              <td class="py-3.5 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <span v-if="item.is_registry" class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded" title="Registry Intake & Dispatch Desk">
                    Registry
                  </span>
                  <span v-if="item.is_head_of_office" class="px-2 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-bold rounded" title="Head of Office / Approver">
                    Head
                  </span>
                  <span v-if="!item.is_registry && !item.is_head_of_office" class="text-gray-300 text-[11px]">-</span>
                </div>
              </td>

              <td class="py-3.5 px-4 text-center">
                <button 
                  @click="toggleStatus(item)" 
                  class="px-2.5 py-1 rounded-full text-[10px] font-bold transition-all"
                  :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-400 border border-gray-200'"
                >
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </button>
              </td>

              <td class="py-3.5 px-5 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="openEditModal(item)" 
                    class="p-1.5 text-gray-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors"
                    title="Edit Designation"
                  >
                    <Icon icon="lucide:pencil" width="15" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredDesignationsList.length === 0">
              <td colspan="8" class="py-12 text-center text-gray-400">
                <Icon icon="lucide:folder-search" width="36" class="mx-auto mb-2 text-gray-300" />
                <p class="text-sm font-medium">No designations found matching the criteria</p>
                <p class="text-xs text-gray-400 mt-1">Try adjusting your search query or filters</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="designations.links && designations.links.length > 3" class="p-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
        <div>
          Showing page {{ designations.current_page }} of {{ designations.last_page }} ({{ designations.total }} records)
        </div>
        <div class="flex items-center gap-1">
          <Link 
            v-for="(link, i) in designations.links" 
            :key="i" 
            :href="link.url || '#'" 
            v-html="link.label"
            class="px-3 py-1.5 rounded-lg border text-xs font-bold transition-all"
            :class="link.active ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
            :disabled="!link.url"
          />
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <Dialog v-model:visible="showModal" modal :closable="false" :style="{ width: '38rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }">
      <div class="p-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
              <Icon icon="lucide:badge-plus" width="22" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900">{{ isEditing ? 'Edit Designation' : 'Create New Designation' }}</h3>
              <p class="text-xs text-gray-500">Configure functional role, hierarchy scope, and desk properties</p>
            </div>
          </div>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
            <Icon icon="lucide:x" width="18" />
          </button>
        </div>

        <form @submit.prevent="submitDesignation" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Designation Title <span class="text-rose-500">*</span></label>
            <input 
              v-model="form.title" 
              required 
              placeholder="e.g. Registry Officer, Checking Officer, Vote Book Officer"
              class="w-full text-xs rounded-xl border-gray-300 py-2.5 px-3.5 focus:border-primary focus:ring-primary/20"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cadre / Field</label>
              <input 
                v-model="form.cadre" 
                placeholder="e.g. Accountant, Executive Officer"
                class="w-full text-xs rounded-xl border-gray-300 py-2.5 px-3.5 focus:border-primary focus:ring-primary/20"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Default Grade Level (0-17)</label>
              <input 
                v-model.number="form.grade_level" 
                type="number" 
                min="0" 
                max="17" 
                placeholder="e.g. 9"
                class="w-full text-xs rounded-xl border-gray-300 py-2.5 px-3.5 focus:border-primary focus:ring-primary/20"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Target Department <span class="text-rose-500">*</span></label>
              <select v-model="form.department_id" required class="w-full text-xs rounded-xl border-gray-300 py-2.5 px-3.5 focus:ring-primary/20">
                <option :value="null">-- Select Department --</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.title }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Hierarchy Level <span class="text-rose-500">*</span></label>
              <select v-model="form.hierarchy_level" required class="w-full text-xs rounded-xl border-gray-300 py-2.5 px-3.5 focus:ring-primary/20">
                <option value="department">Department-Wide Desk</option>
                <option value="division">Division Level</option>
                <option value="unit">Unit Level</option>
                <option value="section">Section Level</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Desk Function / Description</label>
            <textarea 
              v-model="form.description" 
              rows="2" 
              placeholder="Summary of statutory workflow duties performed at this desk..."
              class="w-full text-xs rounded-xl border-gray-300 py-2 px-3.5 focus:border-primary focus:ring-primary/20"
            ></textarea>
          </div>

          <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
            <span class="text-xs font-bold text-gray-700 block mb-1">Special Desk Capabilities:</span>
            <div class="flex items-center gap-6">
              <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                <input type="checkbox" v-model="form.is_registry" class="rounded text-primary focus:ring-primary/20 w-4 h-4" />
                <span>Registry / Correspondence Desk</span>
              </label>
              <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                <input type="checkbox" v-model="form.is_head_of_office" class="rounded text-primary focus:ring-primary/20 w-4 h-4" />
                <span>Head of Office / Approving Authority</span>
              </label>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button 
              type="button" 
              @click="showModal = false" 
              class="px-5 py-2.5 rounded-xl text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="isSubmitting || !form.title"
              class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary/90 disabled:opacity-50 transition-all flex items-center gap-1.5 shadow-xs"
            >
              <Icon icon="lucide:check" width="16" />
              <span>{{ isEditing ? 'Update Designation' : 'Save Designation' }}</span>
            </button>
          </div>
        </form>
      </div>
    </Dialog>
  </main>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
  designations: {
    type: Object,
    default: () => ({ data: [], total: 0 })
  },
  departments: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});

const toast = useToast();
const searchQuery = ref(props.filters.search || '');
const selectedHierarchy = ref(props.filters.hierarchy_level || '');
const selectedDepartment = ref(props.filters.department_id || '');

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const isSubmitting = ref(false);

const form = useForm({
  title: '',
  cadre: '',
  grade_level: null,
  hierarchy_level: 'global',
  department_id: null,
  division_id: null,
  unit_id: null,
  description: '',
  is_registry: false,
  is_head_of_office: false
});

// Counts
const registryCount = computed(() => {
  return (props.designations.data || []).filter(d => d.is_registry).length;
});

const headOfOfficeCount = computed(() => {
  return (props.designations.data || []).filter(d => d.is_head_of_office).length;
});

const globalCount = computed(() => {
  return (props.designations.data || []).filter(d => d.hierarchy_level === 'global' || !d.department_id).length;
});

const filteredDesignationsList = computed(() => {
  let list = props.designations.data || [];
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(item => 
      item.title.toLowerCase().includes(q) || 
      (item.cadre && item.cadre.toLowerCase().includes(q))
    );
  }
  if (selectedHierarchy.value) {
    list = list.filter(item => item.hierarchy_level === selectedHierarchy.value);
  }
  if (selectedDepartment.value) {
    list = list.filter(item => item.department_id == selectedDepartment.value);
  }
  return list;
});

const getHierarchyBadgeClass = (level) => {
  switch (level) {
    case 'global': return 'bg-amber-100 text-amber-800 border border-amber-200';
    case 'department': return 'bg-indigo-100 text-indigo-800 border border-indigo-200';
    case 'division': return 'bg-blue-100 text-blue-800 border border-blue-200';
    case 'unit': return 'bg-cyan-100 text-cyan-800 border border-cyan-200';
    case 'section': return 'bg-slate-100 text-slate-700 border border-slate-200';
    default: return 'bg-gray-100 text-gray-700';
  }
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.reset();
  form.hierarchy_level = 'department';
  showModal.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  editingId.value = item.id;
  form.title = item.title;
  form.cadre = item.cadre;
  form.grade_level = item.grade_level;
  form.hierarchy_level = item.hierarchy_level || 'global';
  form.department_id = item.department_id;
  form.division_id = item.division_id;
  form.unit_id = item.unit_id;
  form.description = item.description;
  form.is_registry = !!item.is_registry;
  form.is_head_of_office = !!item.is_head_of_office;
  showModal.value = true;
};

const submitDesignation = () => {
  isSubmitting.value = true;
  const endpoint = isEditing.value 
    ? `/dashboard/settings/designations/${editingId.value}`
    : '/dashboard/settings/designations';

  form.post(endpoint, {
    onSuccess: () => {
      showModal.value = false;
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: isEditing.value ? 'Designation updated successfully' : 'New designation registered',
        life: 3000
      });
      router.reload({ only: ['designations'] });
    },
    onError: (err) => {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: Object.values(err)[0] || 'Validation failed',
        life: 4000
      });
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
};

const toggleStatus = (item) => {
  router.post(`/dashboard/settings/designations/${item.id}/toggle-status`, {}, {
    onSuccess: () => {
      toast.add({
        severity: 'info',
        summary: 'Status Changed',
        detail: `Designation is now ${!item.is_active ? 'active' : 'inactive'}`,
        life: 2500
      });
      router.reload({ only: ['designations'] });
    }
  });
};

const clearFilters = () => {
  searchQuery.value = '';
  selectedHierarchy.value = '';
  selectedDepartment.value = '';
};

// Filter auto-refetch
watch([selectedHierarchy, selectedDepartment], () => {
  router.get('/dashboard/settings/designations', {
    hierarchy_level: selectedHierarchy.value,
    department_id: selectedDepartment.value,
    search: searchQuery.value
  }, {
    preserveState: true,
    replace: true
  });
});
</script>
