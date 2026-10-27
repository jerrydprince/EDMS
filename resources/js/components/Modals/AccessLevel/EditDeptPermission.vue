<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-white rounded-2xl shadow-2xl overflow-hidden' },
    content: { class: 'px-0 pb-0' },
    header: { class: 'hidden' },
    footer: { class: 'hidden' },
  }" :style="{ width: '45rem' }" :breakpoints="{ '1199px': '75vw', '575px': '95vw' }"
    class=".custom-dialog">
    
    <!-- Custom Header -->
    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="p-3 bg-primary/10 text-primary rounded-xl border border-primary/20">
          <Icon icon="mdi:shield-check-outline" width="28" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Edit Permissions</h1>
          <p class="text-sm text-gray-500 mt-1">Configure module access for this role</p>
        </div>
      </div>
      <button @click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
        <Icon icon="mdi:close" width="24" />
      </button>
    </div>

    <!-- Search / Filter Bar -->
    <div class="px-8 pt-4 pb-2 bg-white border-b border-gray-100">
      <div class="relative">
        <Icon icon="mdi:magnify" width="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Filter permissions or modules (e.g. support, settings, audit, payment)..." 
          class="w-full pl-9 pr-8 py-2 text-xs border border-gray-200 rounded-xl focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 bg-gray-50/60 text-gray-800 transition-all placeholder:text-gray-400"
        />
        <button 
          v-if="searchQuery" 
          @click="searchQuery = ''" 
          class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs"
        >
          <Icon icon="mdi:close-circle" width="16" />
        </button>
      </div>
    </div>

    <!-- Body -->
    <div class="px-8 py-6 bg-white max-h-[60vh] overflow-y-auto custom-scrollbar">
      <div class="space-y-4">
        <div v-for="(permissionGroup, index) in filteredPermissionGroups" :key="index" 
             class="border border-gray-100 rounded-xl overflow-hidden bg-gray-50/30 transition-all duration-300 hover:border-gray-200">
          
          <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50/80 transition-colors"
               @click="openPermissions(index)">
            <div class="flex items-center gap-3">
              <div class="p-1 rounded bg-white border border-gray-200 text-gray-500 transition-transform duration-300"
                   :class="[isGroupOpen(index) ? 'rotate-90 text-primary border-primary/30' : '']">
                <Icon icon="flowbite:angle-right-outline" width="16" />
              </div>
              <span class="font-semibold text-gray-800">{{ permissionGroup.groupName }}</span>
              <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium ml-2">
                {{ permissionGroup.permissions.length }} items
              </span>
            </div>
            <div class="flex items-center" @click.stop>
              <input type="checkbox" 
                     class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary/30 transition-colors cursor-pointer"
                     :checked="isGroupSelected(permissionGroup)" 
                     .indeterminate="isGroupPartiallySelected(permissionGroup)"
                     @change="toggleGroup(permissionGroup)" />
            </div>
          </div>
          
          <div class="transition-all duration-500 ease-in-out border-t border-gray-100/50 bg-white"
               :class="[isGroupOpen(index) ? 'max-h-[800px] opacity-100 p-2' : 'max-h-0 opacity-0 overflow-hidden border-t-0 p-0']">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2">
              <div v-for="(perm, pIndex) in permissionGroup.permissions" :key="pIndex"
                   class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-100 transition-colors group">
                <div class="flex flex-col pr-2">
                  <span class="text-sm font-medium text-gray-700 capitalize group-hover:text-gray-900 transition-colors">
                    {{ perm.name }}
                  </span>
                  <span v-if="perm.description" class="text-[11px] text-gray-400 mt-0.5 line-clamp-1">
                    {{ perm.description }}
                  </span>
                </div>
                <input type="checkbox" v-model="selectedPermissions" :value="perm.id" 
                       class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/30 transition-colors cursor-pointer" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Custom Footer -->
    <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
      <button @click="closeModal" 
              class="px-6 py-2.5 rounded-xl font-medium text-gray-700 hover:bg-gray-200 bg-gray-100 transition-colors duration-200">
        Cancel
      </button>
      <button @click="submit" 
              class="px-8 py-2.5 rounded-xl font-medium text-white bg-primary hover:bg-primary/90 shadow-sm hover:shadow transition-all duration-200 transform hover:-translate-y-0.5 flex items-center gap-2">
        <Icon icon="mdi:check" width="18" />
        Save Changes
      </button>
    </div>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from "@inertiajs/vue3";

const props = defineProps(["modelValue", "permissions",'department_id', 'selectedPermission']);
const toast = useToast();
const selectedPermissions = ref([])
const emit = defineEmits(["update:modelValue"]);
const groupPermission = ref([])
const searchQuery = ref('');

const filteredPermissionGroups = computed(() => {
  if (!searchQuery.value || !searchQuery.value.trim()) {
    return permissions.value || [];
  }
  const q = searchQuery.value.toLowerCase().trim();
  return (permissions.value || []).map(group => {
    const groupMatches = group.groupName.toLowerCase().includes(q);
    const matchingPerms = (group.permissions || []).filter(p => 
      p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q))
    );
    if (groupMatches) {
      return group;
    }
    if (matchingPerms.length > 0) {
      return {
        ...group,
        permissions: matchingPerms
      };
    }
    return null;
  }).filter(Boolean);
});

const isGroupOpen = (index) => {
  if (searchQuery.value && searchQuery.value.trim().length > 0) {
    return true;
  }
  return !!openPermssion.value[index];
};

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  emit("update:modelValue", false);
};

function getPermissionGroupName(permission){

  let names = Object.keys(permission)
  let name = names[0]
  let words = name.split(' ')
  words.pop()
  return words.join(' ')
}

function getGroupName(permission){
  console.log(permission)
}

function isPermissionChecked(permission){
    let found = props.selectedPermission.findIndex((val)=> val.name == permission.name)
  if(found !== -1){
    return true
  }else{
    return false
  }
}

const submit = () => {
  router.post('/dashboard/add-permissions-to-department', {
    department_id: props.department_id,
    departmentId: props.department_id,
    permissions: selectedPermissions.value
  }, {
    onSuccess: () => {
      emit("update:modelValue", false);
      toast.add({
        severity: "success",
        summary: "Permissions Updated",
        detail: "Department permissions have been successfully updated.",
        life: 3000,
      });
    },
    onError: (errors) => {
      toast.add({
        severity: "error",
        summary: "Error Updating Permissions",
        detail: errors?.error || "Could not save department permissions.",
        life: 4000,
      });
    }
  });
};

// const permissions = ref([
//   {
//     groupName: "System Alerts",
//     permissions: [
//       "Notification preferences",
//       "Manage receipt",
//       "Grants unrestricted access",
//       "System receipt",
//     ],
//   },
//   {
//     groupName: "User Management",
//     permissions: ["Add user", "Edit user", "Delete user"],
//   },
//   // Add more groups as needed
// ]);

const permissions = ref([])

// Reactive selection state
const selection = ref({});

// Initialize selection state
permissions.value.forEach((group, gIndex) => {
  selection.value[gIndex] = group.permissions.map(() => false);
});

const isGroupSelected = (group) => {
  if (!group || !group.permissions || group.permissions.length === 0) return false;
  // Return true if ALL permissions in the group are selected
  return group.permissions.every(perm => selectedPermissions.value.includes(perm.id));
};

const isGroupPartiallySelected = (group) => {
  if (!group || !group.permissions || group.permissions.length === 0) return false;
  const selectedCount = group.permissions.filter(perm => selectedPermissions.value.includes(perm.id)).length;
  return selectedCount > 0 && selectedCount < group.permissions.length;
};

const toggleGroup = (group) => {
  if (!group || !group.permissions) return;
  const hasAnySelected = group.permissions.some(perm => selectedPermissions.value.includes(perm.id));

  if (hasAnySelected) {
    // Deselect all
    group.permissions.forEach(perm => {
      const index = selectedPermissions.value.indexOf(perm.id);
      if (index > -1) {
        selectedPermissions.value.splice(index, 1);
      }
    });
  } else {
    // Select all
    group.permissions.forEach(perm => {
      if (!selectedPermissions.value.includes(perm.id)) {
        selectedPermissions.value.push(perm.id);
      }
    });
  }
};

const openPermssion = ref([]);

const openPermissions = (index) => {
  if (openPermssion.value[index] === undefined) {
    openPermssion.value[index] = false;
  }
  openPermssion.value[index] = !openPermssion.value[index];
};

const syncSelectedPermissions = () => {
  selectedPermissions.value = [];
  if (props.selectedPermission && props.selectedPermission.length > 0) {
    for (let i = 0; i < props.selectedPermission.length; i++) {
      const p = props.selectedPermission[i];
      if (p && p.id) {
        selectedPermissions.value.push(p.id);
      }
    }
  }
};

watch(() => props.permissions, () => {
  permissions.value = props.permissions;
}, { immediate: true });

watch(() => props.selectedPermission, () => {
  syncSelectedPermissions();
}, { immediate: true, deep: true });

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    syncSelectedPermissions();
  }
});
</script>
<style scoped>
.custom-dialog .p-dialog-header {
  display: none;
  width: 20%;
}

.checkbox {
  width: 20px;
}
</style>
