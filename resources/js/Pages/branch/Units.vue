<template>
    <div>
        <main class="p-4 lg:p-7">
            <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
                <p class="text-3xl text-gray-800 font-semibold">
                    {{ props.division_name }} Division
                </p>
            </section>

            <div v-if="props.division != 0" class="mt-10">

                <div class="card">

                    <div class="relative overflow-x-auto">

                        <DataTable v-model="expandedRows" :value="props.units" dataKey="id"
                            tableStyle="min-width: 60rem">

                            <Column field="title" header="Units"></Column>
                            <Column header="Last updated" field="updated_at_formatted"></Column>
                            <Column field="archive_count" header="No. of Documents">
                                <template #body="slotProps">
                                    {{ slotProps.data.archive_count }}
                                </template>
                            </Column>
                            <Column field="users_count" header="No. of Users">
                                <template #body="slotProps">
                                    <div class="flex items-center gap-3">
                                        <p>{{ slotProps.data.users_count }} users</p>
                                        <div class="flex -space-x-2 overflow-hidden ml-2" v-if="slotProps.data.users && slotProps.data.users.length > 0">
                                            <img v-for="usr in slotProps.data.users.slice(0, 3)" :key="usr.id"
                                                 :src="usr.profile_image ? ($page.props.base + '/' + usr.profile_image) : ($page.props.base + '/images/user.svg')"
                                                 alt=""
                                                 class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover bg-white" />
                                            <div v-if="slotProps.data.users_count > 3"
                                                 class="inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 text-xs font-medium text-gray-600">
                                              +{{ slotProps.data.users_count - 3 }}
                                            </div>
                                          </div>
                                    </div>
                                </template>
                            </Column>
                            <Column field="" header="">
                                <template #body="{ data }">
                                    <div class="flex items-center gap-3">
                                        <div class="flex">
                                            <div @click="addDocTypes(data)"
                                                class="flex gap-2 items-center pr-4 font-normal h-full border border-gray-300 rounded-lg px-2 py-1">
                                                <div>
                                                <Icon icon="ion:add-outline" width="25" />
                                                </div>
                                                <p class="font-medium">Add document type</p>
                                            </div>
                                        </div>
                                        <div class="flex">
                                            <div @click="toggle($event, data)" aria-haspopup="true"
                                                aria-controls="overlay_menu"
                                                class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                                                <div class="h-full">
                                                    <Icon icon="majesticons:more-menu-vertical" width="30" />
                                                </div>
                                                <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true"
                                                    @click="selectMenu" />
                                            </div>

                                        </div>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                        <!-- <Toast /> -->

                    </div>

                </div>
            </div>
            <EmptyState v-else>
                <div @click="departmentalModal = true"
                    class="mt-3 m-auto w-56 flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4 cursor-pointer">
                    <Icon icon="ion:add-outline" width="25" />
                    <p>Create Department</p>
                </div>
            </EmptyState>
        </main>
    </div>
    <EditUnitModal v-model="editUnit" :unit="singleUnit" @close="editUnit=false"/>
    <AddDocumentTypesModal v-model="addDocType" :doctypes="props.documentTypes" :branch="currentUnit" :url="'/dashboard/add-document-type-to-unit'" />
    <AddFileTypeModal v-model="addFileType"  :selectedFileType="selectedFileType" :branch="'unit'" :branch_id="singleUnit?.id"/>
</template>

<script setup>
import { ref, defineProps, watch } from 'vue';
import EmptyState from "../../components/EmptyState.vue";
import EditUnitModal from "../../components/Modals/EditUnitModal.vue";
import AddDocumentTypesModal from "../../components/Modals/AddDocumentTypesModal.vue";
import AddFileTypeModal from "../../components/Modals/AddFileTypeModal.vue";
import { router } from '@inertiajs/vue3';

const props = defineProps(['units', 'division_name','documentTypes']);
const editUnit = ref(false)
const currentUnit = ref([]);
const addDocType = ref(false);
const selectedFileType = ref([]);

const menu = ref();
const singleUnit = ref(null)
const addFileType = ref(false);
const menuItems = ref([
  
  { label: "Edit", icon: "pi pi-pencil", command: ()=>{
    editUnit.value = true;
  } },
  {
    label: 'Add File Type',
    icon: 'pi pi-file',
    command: ()=>{
      addFileType.value = true;
    }
  },
//   { 
//     label: "Duplicate", icon: "pi pi-clone",
//     command: ()=>{
//         let data = {id:singleUnit.value.id}
//         router.post('/dashboard/duplicate-unit',data)
//     } 
//     },
  { 
    label: "Delete", icon: "pi pi-trash",
    command: ()=>{
        let data = {id:singleUnit.value.id}
        router.post('/dashboard/delete-unit', data)
    }
 },
  { label: "Change Log", icon: "pi pi-chart-pie" },
]);

const expandedRows = ref([]);

const formatCurrency = (value) => {
  return value;
};

const toggle = (event,data) => {
  menu.value.toggle(event);
  singleUnit.value = data
};

const addDocTypes = (val) =>{
  addDocType.value = true
  currentUnit.value = val
}

watch(singleUnit, ()=>{
  if(singleUnit.value.file_type && singleUnit.value.file_type.length > 0){
    let arr= []
    for(let filetype of singleUnit.value.file_type){
      arr.push(filetype.id);
    }
    selectedFileType.value = arr
  }
})

</script>
