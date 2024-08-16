<template>
  <Dialog v-model:visible="visible" :closable="false" modal :pt="{
    root: { class: 'bg-slate-100 px-5 rounded-md' },
    content: { class: 'px-0  pb-6' },
    header: { class: ' m-5' },
    footer: { class: 'bg-slate-100' },
  }" :style="{ width: '35rem', padding: '10px 0px' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
    class=".custom-dialog">
    <template #header>
      <div class="flex bg-slate-100 items-start justify-between w-full">
        <div class="flex items-center gap-3">
          <div class="p-3 border rounded-md bg-white">
            <Icon icon="mingcute:pencil-2-line" width="30" />
          </div>
          <div>
            <h1 class="text-2xl font-medium">Edit Permissions</h1>
          </div>
        </div>
      </div>
    </template>
    <section class="bg-white mx-2 rounded-md p-5 max-h-[70vh] overflow-y-scroll hide_scroll">
      <div>
        <div v-for="(permissionGroup, index) in permissions" :key="index" class="mt-3 border-b pb-4">
          <div class="flex items-center w-full justify-between my-1 pl-1 pr-3">
            <div class="flex items-center gap-1" @click="openPermissions(index)">
              <div class="cursor-pointer ml-2 lg:ml-2 duration-300 transition-all"
                :class="[openPermssion[index] ? ' rotate-90 ' : ' rotate-0']">
                <Icon icon="flowbite:angle-right-outline" />
              </div>
              <span class="font-semibold">{{ getPermissionGroupName(permissionGroup) }}</span>
            </div>
            <input type="checkbox"  class="h-5 w-5" :value="isGroupSelected(permissionGroup)" :checked="isGroupSelected(permissionGroup)" @change="toggleGroup(permissionGroup)" />
          </div>
          <div class="bg-slate-50 p-3 rounded-lg overflow-hidden duration-500 transition-all" :class="[
    openPermssion[index]
      ? 'max-h-[500px] py-3 mt-3 '
      : 'h-0 mt-0 max-h-0 py-0',
  ]">
          <div v-for="(permission, pIndex) in permissionGroup" :key="pIndex">
             <div v-for="(perm, pIndex) in permission" :key="pIndex"
              class="flex my-3 text-gray-900 text-base items-center w-full justify-between">
              <span>{{ perm.name }}</span>
              <input type="checkbox" v-model="selectedPermissions" :value="perm.id" class="h-5 w-5" :checked="isPermissionChecked(perm)"
               />
            </div>
          </div>
           
          </div>
        </div>
      </div>
    </section>
    <template #footer>
      <div class="flex items-center justify-end px-7">
        <Button class="mr-4" :pt="{
    root: {
      class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
    },
  }" @click="closeModal" label="Cancel" severity="secondary" outlined />
        <Button label="Save" class="" :pt="{
    root: {
      class:
        'py-2 bg-primary px-10 border-0 text-white font-light hover:bg-primary/90 rounded-lg',
    },
  }" @click="submit" autofocus />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { router } from "@inertiajs/vue3";

const props = defineProps(["modelValue", "permissions",'role_id', 'selectedPermission']);
const toast = useToast();
const selectedPermissions = ref([])
const emit = defineEmits(["update:modelValue"]);
const groupPermission = ref([])
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
    let i = 1;
  if(found){
    return true
  }else{
    false
  }
}

const submit = () => {
router.post('/dashboard/add-permissions-to-role', {role_id: props.role_id, permissions: selectedPermissions.value}, {onSuccess: ()=>{
  emit("update:modelValue", false);

  toast.add({
    severity: "success",
    summary: "Access Level Created",
    detail: "The access level has been successfully created.",
    life: 3000,
  });
}})
  
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
  let values = Object.values(group);
let found = false;

values.forEach(item => {
  if (selectedPermissions.value.find(val => item.some(subItem => subItem.id === val))) {
    found = true;
  }
});

if (found) {
  return true;
} else {
  return false;
}
 // return selection.value[index].every((selected) => selected);
};

const isPermissionSelected = (groupIndex, permIndex) => {
  //return selection.value[groupIndex][permIndex];
};

const toggleGroup = (permission) => {
  let is_permission = isGroupSelected(permission);
  let values = Object.values(permission);

  if(!is_permission){
    values.forEach(item => {
      selectedPermissions.value.push(item[0].id)
    })
    
  }else{
    values.forEach(item => {
      let index = selectedPermissions.value.indexOf(item[0].id)
      if (index > -1) {
        selectedPermissions.value.splice(index, 1);
      }

    })
  }
  // const allSelected = isGroupSelected(index);
  // selection.value[index].forEach((_, pIndex) => {
  //   selection.value[index][pIndex] = !allSelected;
  // });
};

const openPermssion = ref(permissions.value.map(() => false));

const openPermissions = (index) => {
  openPermssion.value[index] = !openPermssion.value[index];
};
const togglePermission = (groupIndex, permIndex) => {
  selection.value[groupIndex][permIndex] =
    !selection.value[groupIndex][permIndex];
};

watch(()=>props.permissions, ()=>{
  permissions.value = props.permissions;
}, {immediate:true})

watch(()=>props.selectedPermission, ()=>{
 if(props.selectedPermission){
  let perm = props.selectedPermission;
  for(let i=0; i < perm.length; i++){
    selectedPermissions.value.push(perm[i].id)
  }
 }
}, {immediate:true})

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