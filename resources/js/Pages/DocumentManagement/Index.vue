<template>
  <Head title="Document Management" />
  
  <div class="min-h-screen bg-gray-50 pb-12">
    <!-- Header Section -->
    <header class="bg-white border-b border-gray-200">
      <div class="w-full px-6 lg:px-12 py-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
              <Icon icon="octicon:git-branch-24" class="text-primary" width="32" />
              Document Management
            </h1>
            <p class="mt-2 text-sm text-gray-500">
              Manage your departments, divisions, and units across the organization. Select a department to view its document structures.
            </p>
          </div>
          <div>
            <!-- Decorative or action items can go here -->
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="w-full px-6 lg:px-12 mt-8">
      
      <!-- Search Bar -->
      <div class="mb-6 relative max-w-2xl">
        <Icon icon="solar:magnifer-linear" class="absolute top-1/2 -translate-y-1/2 text-gray-400" style="left: 1rem;" width="20" />
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Search departments, divisions, or units..." 
          class="w-full pr-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none"
          style="padding-left: 3rem;"
        />
      </div>

      <!-- Explorer List -->
      <div v-if="filteredBranches && filteredBranches.length > 0" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex flex-col divide-y divide-gray-100">
          <div v-for="department in filteredBranches" :key="department.id" class="flex flex-col">
             
            <!-- Department Row -->
            <div class="group flex items-center justify-between p-4 hover:bg-blue-50/50 transition-colors cursor-pointer" @click.prevent="toggleDepartment(department.id)">
              <div class="flex items-center gap-4">
                <button class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-primary transition-colors">
                  <Icon :class="expandedDepartments[department.id] ? 'rotate-90' : ''" icon="fluent:chevron-right-24-filled" width="16" class="transition-transform duration-200" />
                </button>
                <div class="text-primary flex items-center justify-center">
                  <Icon :icon="expandedDepartments[department.id] ? 'solar:folder-open-bold-duotone' : 'solar:folder-with-files-bold-duotone'" width="24" />
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 group-hover:text-primary transition-colors">
                    {{ department.title }}
                  </h3>
                  <p v-if="department.description" class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                    {{ department.description }}
                  </p>
                </div>
              </div>

              <!-- Stats & Access -->
              <div class="flex items-center gap-4">
                <div v-if="department.division && department.division.length > 0" class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                  <Icon icon="octicon:organization-16" width="14" class="text-gray-400" />
                  <span class="font-medium">{{ department.division.length }}</span> Divisions
                </div>
                <Link :href="isDepartmentRole(department)" @click.stop class="ml-4 flex items-center gap-2 text-sm font-medium text-primary bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors">
                  Open
                  <Icon icon="fluent:arrow-right-24-filled" width="16" />
                </Link>
              </div>
            </div>

            <!-- Sub-Structures (Divisions & Units) -->
            <div v-show="expandedDepartments[department.id]" class="bg-gray-50/50 border-t border-gray-100">
              <div v-if="department.division && department.division.length > 0" class="flex flex-col divide-y divide-gray-100/50">
                <div v-for="division in department.division" :key="division.id" class="flex flex-col">
                  
                  <!-- Division Row -->
                  <Link :href="isDivisionRole(division, department)" class="group/div flex items-center justify-between pr-6 py-4 hover:bg-blue-50/30 transition-colors block cursor-pointer" style="padding-left: 3.5rem;">
                    <div class="flex items-center gap-3">
                      <Icon icon="solar:folder-bold-duotone" width="20" class="text-blue-400" />
                      <span class="text-sm font-medium text-gray-800">{{ division.title }}</span>
                    </div>
                    <Icon icon="solar:alt-arrow-right-linear" width="18" class="text-gray-400 opacity-0 group-hover/div:opacity-100 transition-opacity" />
                  </Link>
                  
                  <!-- Units within Division -->
                  <div v-if="division.unit && division.unit.length > 0" class="flex flex-col bg-white/50 py-1 border-t border-gray-100/30" style="padding-left: 4rem;">
                    <!-- Unit Row -->
                    <Link v-for="unit in division.unit" :key="unit.id" :href="isUnitRole(unit, department)" class="group/unit flex items-center justify-between pr-6 py-3 hover:bg-blue-50/50 transition-colors block border-t border-gray-100/30 cursor-pointer" style="padding-left: 5rem;">
                      <div class="flex items-center gap-3">
                        <Icon icon="solar:document-bold-duotone" width="18" class="text-green-500" />
                        <span class="text-[13px] text-gray-600">{{ unit.title }}</span>
                      </div>
                      <Icon icon="solar:alt-arrow-right-linear" width="18" class="text-gray-400 opacity-0 group-hover/unit:opacity-100 transition-opacity" />
                    </Link>
                  </div>

                </div>
              </div>
              <div v-else class="pl-12 py-4 text-sm text-gray-500 italic">
                No divisions found in this department.
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="branches && branches.length > 0" class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-200 border-dashed">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 mb-4">
          <Icon icon="solar:magnifer-bug-bold-duotone" width="40" />
        </div>
        <h3 class="text-xl font-semibold text-gray-900">No Results Found</h3>
        <p class="text-gray-500 mt-2 text-center max-w-md">
          We couldn't find any departments, divisions, or units matching "{{ searchQuery }}".
        </p>
      </div>

      <!-- No Access State -->
      <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border border-gray-200 border-dashed">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 mb-4">
          <Icon icon="solar:folder-error-bold-duotone" width="40" />
        </div>
        <h3 class="text-xl font-semibold text-gray-900">No Departments Found</h3>
        <p class="text-gray-500 mt-2 text-center max-w-md">
          You currently do not have access to any departments or none have been assigned to your role.
        </p>
      </div>

    </main>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineProps, ref, computed } from 'vue';
import { Icon } from '@iconify/vue';
import Layout from '@/Shared/Layout.vue';

defineOptions({
  layout: Layout,
});

const props = defineProps({
  branches: Array,
  role: Array,
});

const searchQuery = ref('');

const filteredBranches = computed(() => {
  if (!searchQuery.value) return props.branches;
  
  const query = searchQuery.value.toLowerCase();
  
  return props.branches.filter(dept => {
    // Check department title
    if (dept.title && dept.title.toLowerCase().includes(query)) return true;
    
    // Check divisions and units
    if (dept.division && dept.division.length > 0) {
      return dept.division.some(div => {
        if (div.title && div.title.toLowerCase().includes(query)) return true;
        
        if (div.unit && div.unit.length > 0) {
          return div.unit.some(unit => unit.title && unit.title.toLowerCase().includes(query));
        }
        return false;
      });
    }
    return false;
  });
});

const isDepartmentRole = (item) => {
  return `/dashboard/branches/department/${item.id}/0/0`;
};

const expandedDepartments = ref({});
const toggleDepartment = (id) => {
  expandedDepartments.value[id] = !expandedDepartments.value[id];
};

const isDivisionRole = (item, department) => {
  return `/dashboard/branches/division/${department.id}/${item.id}/0`;
};

const isUnitRole = (item, department) => {
  let division = department.division && department.division.length > 0 ? department.division[0].id : 0;
  return `/dashboard/branches/unit/${department.id}/${division}/${item.id}`;
};
</script>

<style scoped>
/* Premium hover effects and glassmorphism elements can go here if needed */
</style>
