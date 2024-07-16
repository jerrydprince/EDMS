<template>
    <div>
        <main class="p-4 lg:p-7">
            <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
                <p class="text-3xl text-gray-800 font-semibold">
                    {{ props.department_name }} Department
                </p>
            </section>

            <div v-if="props.division != 0" class="mt-10">

                <div class="card">

                    <div class="relative overflow-x-auto">

                        <DataTable v-model="expandedRows" :value="props.division" dataKey="id"
                            tableStyle="min-width: 60rem">

                            <Column field="title" header="Divisions"></Column>
                            <Column header="Last updated" field="updated_at_formatted"></Column>
                            <Column field="unit_count" header="Units">
                               
                            </Column>
                            <Column field="folder_sum_file_count" header="No. of Documents">
                                <!-- <template #body="slotProps">
                                    {{ formatCurrency(slotProps.data.price) }}
                                </template> -->
                            </Column>
                            <Column field="users_count" header="No. of Users">
                                <template #body="slotProps">
                                    <div class="flex items-center gap-3">
                                        <p>{{ slotProps.data.users_count }} users</p>
                                        <img src="../../assets/Avatars.png" alt="" class="ml-2">
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
                                                    />
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
                <div @click="addDivision = true"
                    class="mt-3 m-auto w-56 flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4 cursor-pointer">
                    <Icon icon="ion:add-outline" width="25" />
                    <p>Create Division</p>
                </div>
            </EmptyState>
        </main>
    </div>
    <AddUnit v-model="addUnit" :division="singleDivision" @close="addUnit= false"/>
    <EditDivisionModal v-model="editDivision" :division="singleDivision" @close="editDivision = false"/>
    <AddDocumentTypesModal v-model="addDocType" :doctypes="props.documentTypes" :branch="currentDivision" :url="'/dashboard/add-document-type-to-division'" />
    <AddDivisionModal v-model="addDivision" :department ="props.department" @close="addDivision = false"/>
    <AddFileTypeModal v-model="addFileType"  :selectedFileType="selectedFileType" :branch="'division'" :branch_id="singleDivision?.id"/>
</template>

<script setup>
import { ref, defineProps, watch } from 'vue';
import EmptyState from "../../components/EmptyState.vue";
import AddUnit from "../../components/Modals/AddUnitsModal.vue";
import EditDivisionModal from "../../components/Modals/EditDivisionModal.vue";
import AddDocumentTypesModal from "../../components/Modals/AddDocumentTypesModal.vue";
import AddDivisionModal from "../../components/Modals/AddDivisionModal.vue";
import AddFileTypeModal from "../../components/Modals/AddFileTypeModal.vue";
import { router } from '@inertiajs/vue3';

const props = defineProps(['division', 'department_name','documentTypes','department']);
const singleDivision = ref('');
const addUnit = ref(false);
const editDivision = ref(false)
const addDocType = ref(false);
const currentDivision = ref([])
const addDivision = ref(false);
const addFileType = ref(false);
const selectedFileType = ref([]);

const menu = ref();
const menuItems = ref([
{ label: "Add Units", icon: "pi pi-plus", command: () =>{
    addUnit.value = true;
} },
{
    label: 'Add File Type',
    icon: 'pi pi-file',
    command: ()=>{
      addFileType.value = true;
    }
  },
  { label: "Units", icon: 'pi pi-eye', command: (event)=>{
    //window.location.href = '/dashboard/get-divisions?id='+singleDepartment.value.id
    window.open('/dashboard/get-units?id='+singleDivision.value.id, "_blank");
  }},
  { label: "Edit", icon: "pi pi-pencil", command: ()=>{
    editDivision.value = true
  } },
//   { 
//     label: "Duplicate", icon: "pi pi-clone",
//     command: ()=>{
//         let data = {id: singleDivision.value.id}
//         router.post('/dashboard/duplicate-division',data);
//     }
//  },
  { 
    label: "Delete", icon: "pi pi-trash",
    command: ()=>{
        let data = {id: singleDivision.value.id}
        router.post('/dashboard/delete-division', data);
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
  singleDivision.value = data
};

const addDocTypes = (val) =>{
  addDocType.value = true
  currentDivision.value = val
}

watch(singleDivision, ()=>{
  if(singleDivision.value.file_type && singleDivision.value.file_type.length > 0){
    let arr= []
    for(let filetype of singleDivision.value.file_type){
      arr.push(filetype.id);
    }
    selectedFileType.value = arr
  }
})

</script>