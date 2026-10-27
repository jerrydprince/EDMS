<template>
  <div>
    <div class="relative pt-8 pb-6 px-7 mb-2 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 overflow-hidden">
      <!-- Decorative background blur -->
      <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
      
      <div class="lg:flex items-center justify-between relative z-10">
        <div class="flex items-center gap-5">
          <div class="bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/20 rounded-2xl p-3.5 shadow-sm">
            <Icon icon="mdi:shield-key-outline" width="32" class="text-primary" />
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Access Level Configuration</h1>
            <p class="text-gray-500 mt-1 font-medium">Manage roles, permissions, and user access levels</p>
          </div>
        </div>
        <div class="mt-4 lg:mt-0 flex items-center gap-3">
          <Link href="/dashboard/governance-hub"
            class="group flex gap-2 items-center bg-indigo-50 border border-indigo-200 text-indigo-700 font-semibold rounded-xl py-2.5 px-4 shadow-2xs hover:bg-indigo-100 transition-all duration-300">
            <Icon icon="lucide:network" width="18" class="text-indigo-600" />
            <span>Governance Hub</span>
          </Link>
          <Link href="/dashboard/settings/designations"
            class="group flex gap-2 items-center bg-white border border-gray-300 text-gray-700 font-medium rounded-xl py-2.5 px-4 shadow-2xs hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">
            <Icon icon="mdi:badge-account-outline" width="18" class="text-indigo-600" />
            <span>Designations Setup</span>
          </Link>
          <button @click="create_permission_modal = true"
            class="group flex gap-2 items-center bg-white border border-gray-300 text-gray-700 font-medium rounded-xl py-2.5 px-4 shadow-2xs hover:bg-gray-50 hover:border-gray-400 transition-all duration-300">
            <Icon icon="mdi:key-plus" width="18" class="text-primary" />
            <span>Add Permission</span>
          </button>
          <button @click="create_levels = true"
            class="group flex gap-2 items-center bg-primary text-white font-medium rounded-xl py-2.5 px-5 shadow-sm hover:shadow-md hover:bg-primary/90 transition-all duration-300 transform hover:-translate-y-0.5">
            <Icon icon="lucide:plus" width="20" class="group-hover:rotate-90 transition-transform duration-300" />
            <span>Create Access Level</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <main class="px-7 pb-12">
    <!-- Tabs for Roles vs Departments -->
    <div class="flex border-b border-gray-200 mb-6">
      <button 
        @click="activeTab = 'roles'"
        :class="['px-6 py-3 font-medium text-sm transition-colors border-b-2', activeTab === 'roles' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700']"
      >
        Access Levels (Roles)
      </button>
      <button 
        @click="activeTab = 'departments'"
        :class="['px-6 py-3 font-medium text-sm transition-colors border-b-2', activeTab === 'departments' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700']"
      >
        Departmental Permissions
      </button>
    </div>

    <!-- Roles Grid -->
    <div v-if="activeTab === 'roles'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="(role, index) in props.roles" :key="role.id"
           class="bg-white/80 backdrop-blur-lg border border-gray-200/60 shadow-sm hover:shadow-xl transition-all duration-300 rounded-2xl p-6 flex flex-col group relative overflow-hidden">
        
        <!-- Subtle gradient background element -->
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-colors duration-500"></div>

        <div class="flex justify-between items-start mb-4 relative z-10">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary border border-primary/20">
              <Icon icon="mdi:shield-account-outline" width="24" />
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800 tracking-tight capitalize">{{ role.name }}</h2>
              <div class="flex items-center gap-1 text-xs text-gray-500 mt-0.5">
                <Icon icon="mdi:clock-outline" width="12" />
                <span>{{ formatDate(role.created_at) }}</span>
              </div>
            </div>
          </div>
          
          <label class="switch scale-75 origin-right">
            <input type="checkbox" :checked="role.status === 1" @change="toggleStatus(role)" />
            <span class="slider round"></span>
          </label>
        </div>
        
        <div class="flex-grow relative z-10">
          <p class="text-sm text-gray-600 line-clamp-3 mb-4 min-h-[3rem]">
            {{ role.description || 'No description provided for this access level.' }}
          </p>
          
          <div class="bg-gray-50/80 rounded-xl p-3 flex items-center justify-between border border-gray-100 mb-5">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <Icon icon="lucide:users" width="16" />
              </div>
              <span class="text-sm font-medium text-gray-700">Assigned Users</span>
            </div>
            <span class="text-lg font-bold text-gray-900">{{ role.users_count || 0 }}</span>
          </div>
        </div>
        
        <div class="flex items-center gap-3 pt-4 border-t border-gray-100/80 relative z-10 mt-auto">
          <button @click="editPermission(role)" 
                  class="flex-1 flex items-center justify-center gap-2 py-2 px-4 bg-gray-50 hover:bg-primary/10 text-gray-700 hover:text-primary rounded-xl font-medium transition-colors text-sm border border-gray-200 hover:border-primary/30">
            <Icon icon="mingcute:pencil-2-line" width="18" />
            Permissions
          </button>
          
          <button @click="deleteLevel(role)" 
                  class="w-10 h-10 flex items-center justify-center bg-gray-50 hover:bg-red-50 text-gray-500 hover:text-red-500 rounded-xl transition-colors border border-gray-200 hover:border-red-200 shrink-0">
            <Icon icon="mingcute:delete-2-line" width="18" />
          </button>
        </div>
      </div>
    </div>

    <!-- Departments Grid -->
    <div v-if="activeTab === 'departments'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="dept in props.departments" :key="dept.id"
           class="bg-white/80 backdrop-blur-lg border border-gray-200/60 shadow-sm hover:shadow-xl transition-all duration-300 rounded-2xl p-6 flex flex-col group relative overflow-hidden">
        
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:bg-primary/10 transition-colors duration-500"></div>

        <div class="flex justify-between items-start mb-4 relative z-10">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-primary/10 text-primary border border-primary/20">
              <Icon icon="mdi:domain" width="24" />
            </div>
            <div>
              <h2 class="text-xl font-bold text-gray-800 tracking-tight capitalize">{{ dept.title }}</h2>
              <div class="flex items-center gap-1 text-xs text-gray-500 mt-0.5">
                <Icon icon="mdi:clock-outline" width="12" />
                <span>{{ formatDate(dept.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex-grow relative z-10">
          <p class="text-sm text-gray-600 line-clamp-3 mb-4 min-h-[3rem]">
            Assign specific modules or permissions directly to the {{ dept.title }} department.
          </p>
          
          <div class="bg-gray-50/80 rounded-xl p-3 flex items-center justify-between border border-gray-100 mb-5">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <Icon icon="lucide:users" width="16" />
              </div>
              <span class="text-sm font-medium text-gray-700">Assigned Users</span>
            </div>
            <span class="text-lg font-bold text-gray-900">{{ dept.user_count || 0 }}</span>
          </div>
        </div>
        
        <div class="flex items-center gap-3 pt-4 border-t border-gray-100/80 relative z-10 mt-auto">
          <button @click="editDeptPermission(dept)" 
                  class="flex-1 flex items-center justify-center gap-2 py-2 px-4 bg-gray-50 hover:bg-primary/10 text-gray-700 hover:text-primary rounded-xl font-medium transition-colors text-sm border border-gray-200 hover:border-primary/30">
            <Icon icon="mingcute:pencil-2-line" width="18" />
            Manage Permissions
          </button>
        </div>
      </div>
    </div>
  </main>
  <CreateLevel v-model="create_levels" />
  <CreatePermissionModal v-model="create_permission_modal" :existingModules="existingModuleNames" :allPermissions="props.permissions" />
  <EditPermission v-model="edit_permission" :selectedPermission="selectedLevel.permissions" :permissions="props.permissions" :role_id="selectedLevel.id"/>
  <EditDeptPermission v-model="edit_dept_permission" :selectedPermission="selectedDept ? selectedDept.permissions : []" :permissions="props.permissions" :department_id="selectedDept ? selectedDept.id : null"/>
  <DeleteLevel v-model="delete_level" :id="selectedLevel.id" />
  <Deactivate v-model="deactivate_modal" @refresh="refreshPage" :id="selectedLevel.id" />
</template>

<script setup>

import { ref, defineProps, computed } from "vue";
import CreateLevel from "../../components/Modals/AccessLevel/CreateLevel.vue";
import CreatePermissionModal from "../../components/Modals/AccessLevel/CreatePermissionModal.vue";
import EditPermission from "../../components/Modals/AccessLevel/EditPermission.vue";
import EditDeptPermission from "../../components/Modals/AccessLevel/EditDeptPermission.vue";
import DeleteLevel from "../../components/Modals/AccessLevel/DeleteLevel.vue";
import Deactivate from "../../components/Modals/AccessLevel/Deactivate.vue";
import { router, Link } from "@inertiajs/vue3";

const deactivate_modal = ref(false);
const delete_level = ref(false);
const create_levels = ref(false);
const create_permission_modal = ref(false);
const edit_permission = ref(false);
const edit_dept_permission = ref(false);
const props = defineProps(['roles', 'permissions', 'departments'])
const selectedLevel = ref([])
const selectedDept = ref(null)
const activeTab = ref('roles')

const existingModuleNames = computed(() => {
  return props.permissions?.map(p => p.groupName) || [];
});

const accessLevelData = ref([
  {
    SNo: 1,
    AccessLevel: "Super Admin",
    NoOfUsers: 3,
    CreatedDate: "2024-01-15",
    LastModified: "2024-01-15",
    Status: "Active",
    id: 1,
  },
  {
    SNo: 2,
    AccessLevel: "Administrator",
    NoOfUsers: 10,
    CreatedDate: "2024-02-10",
    LastModified: "2024-02-10",
    Status: "Active",
    id: 2,
  },
  {
    SNo: 3,
    AccessLevel: "Records Manager",
    NoOfUsers: 3,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 3,
  },
  {
    SNo: 4,
    AccessLevel: "super user",
    NoOfUsers: 10,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 4,
  },
  {
    SNo: 5,
    AccessLevel: "director",
    NoOfUsers: 50,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 5,
  },
  {
    SNo: 6,
    AccessLevel: "deputy director",
    NoOfUsers: 5,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 6,
  },
  {
    SNo: 7,
    AccessLevel: "Unit Head",
    NoOfUsers: 50,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Active",
    id: 7,
  },
  {
    SNo: 8,
    AccessLevel: "Data Entry Personnel",
    NoOfUsers: 5,
    CreatedDate: "2024-03-05",
    LastModified: "2024-03-05",
    Status: "Inactive",
    id: 8,
  },
]);

const deleteLevel = (data)=>{
  selectedLevel.value = data
  delete_level.value = true
  
}

const toggleStatus = (item) => {
  selectedLevel.value = item
 
 let status = item.status === 1 ? 0 : 1;

  if (status === 0) {
    deactivate_modal.value = true;
   
  }else{
    router.post('/dashboard/change-accessLevel-status', {status: 1, id: item.id}, {onSuccess: ()=>{
    router.reload({only: ['roles']})
  }})
  }
};

const refreshPage = ()=>{
  router.reload({only: ['roles']})
}

function editPermission(level){
  const freshRole = props.roles?.find(r => r.id === level.id) || level;
  selectedLevel.value = freshRole;
  edit_permission.value = true;
}

function editDeptPermission(dept) {
  const freshDept = props.departments?.find(d => d.id === dept.id) || dept;
  selectedDept.value = freshDept;
  edit_dept_permission.value = true;
}

const formatDate = (date) => {
  if (!date) return '';
      const d = new Date(date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
};

</script>

<style scoped>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: 0.4s;
  transition: 0.4s;
}

input:checked+.slider {
  background-color: #12b76a;
}

input:focus+.slider {
  box-shadow: 0 0 1px #12b76a;
}

input:checked+.slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
