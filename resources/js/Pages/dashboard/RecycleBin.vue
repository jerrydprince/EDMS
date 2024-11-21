<template>
  <main>
    <div class="md:flex items-center justify-between px-5 md:px-7 py-5">
      <div class="flex items-center gap-3">
        <div class="p-3 border rounded-full">
          <Icon icon="material-symbols:delete-outline" width="30" />
        </div>
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold text-[#1D2939]">Recycle Bin</h1>
          <p class=" text-sm md:text-base">Manage and restore deleted file in the EDMS</p>
        </div>
      </div>
      <button class="flex mt-3 lg:mt-0 items-center gap-2 p-2 rounded-md border-[2px]" @click="EmptyRecycle = true">
        <Icon icon="material-symbols:delete-outline" width="25" />
        <p class="font-semibold">Empty Recycle Bin</p>
      </button>
    </div>
    <Divider class="-mt-1" />

    <div class="mx-5 md:mx-7 bg-gray-50 border border-gray-300 lg:flex items-end justify-between p-3 md:p-5 rounded-lg">
      <div class="md:flex items-center justify-between w-full lg:w-[55%] gap-5">
        <div class="text-[#344054] font-semibold w-full md:w-[50%]">
          <label for="Action">Search</label> <br />
          <div class="flex items-center mt-2 border bg-white rounded-md w-full p-3 outline-none border-gray-300">
            <input type="text" v-model="data.search" placeholder="search file by name,number"
              class="w-full bg-white text-xs h-full outline-none" />
            <Icon icon="iconamoon:search-light" width="20" />
          </div>
        </div>

        <div class="-mb-2.5 text-[#344054] font-semibold w-full mt-3 md:mt-0 md:w-[50%]">
          <label for="Action">Date</label>
          <Datepicker range lang="en" v-model="selectedDate" class="flex items-center rounded-md w-full p-2" />
        </div>
      </div>
      <div class="mt-3 lg:mt-0 md:flex justify-start lg:justify-end items-end gap-10 w-full lg:w-[50%]">
        <div class="text-[#344054] font-semibold w-full md:w-[60%] relative">
          <label for="Action">Action Type</label> <br />
          <div
            class="mt-2 border rounded-md w-full relative p-3 bord flex items-center justify-between outline-none border-gray-300 cursor-pointer"
            @click="Dropdown = true" ref="modalRef">
            <div class="absolute w-full h-full"></div>
            <input type="text" class="w-full bg-transparent outline-none" disabled placeholder="Select action type"
              v-model="action" />
            <Icon icon="flowbite:angle-up-outline" :class="{ 'rotate-180': Dropdown }" />
          </div>
          <div v-if="Dropdown" class="absolute z-50 bg-white w-full top-20 rounded-md shadow-md">
            <div v-for="(action, index) in action_type" :key="index" @click="updateAction(action, index)"
              class="font-normal p-1 px-2 hover:bg-slate-100 flex items-center w-full justify-between">
              <p class=" ">{{ action }}</p>
              <div v-if="selectedIndex === index">
                <Icon icon="mdi:tick" />
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="w-full flex items-center gap-5 mt-3 md:mt-0">
            <button @click="filter" class="bg-primary text-white px-3 rounded-md p-2">
              Apply filter
            </button>
            <div>
              <Icon icon="tabler:refresh" class="cursor-pointer" width="30" @click="refresh"/>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="px-7 my-10" v-if="deleted_files.data.length > 0">
      <DataTable  :value="deleted_files.data" tableStyle="min-width: 50rem">
        <Column field="Timestamp" header="Timestamp" class="">
          <template #body="slotProps">
            <div>
              <p class=" ">{{ formatDate(slotProps.data.deleted_at) }}</p>
            </div>
          </template>
        </Column>
        <Column field="Actor" header="Deleted by">
          <template #body="slotProps">
            <div>
              <p>{{ slotProps.data.deleted_by?slotProps.data.deleted_by.first_name+" "+slotProps.data.deleted_by.last_name: '' }}</p>
            </div>
          </template>
        </Column>
        <Column field="deleted_item" header="Deleted item">
          <template #body="slotProps">
            <div class="flex items-center gap-3 font-semibold">
              <!-- <Icon :icon="slotProps.data.icon" width="25" /> -->
              <p class=" ">{{ getDeletedFileName(slotProps.data) }}</p>
            </div>
          </template>
        </Column>
        <Column field="action_type" header="Action Type">
          <template #body>
            <div>
              <p>{{type}}</p>
            </div>
          </template>
        </Column>
        <Column field="" header="Actions">
          <template #body="slotProps">
            <div class="flex gap-5 items-center">
              <Icon icon="basil:refresh-outline" width="25" class="cursor-pointer" @click="getFileRestore(slotProps.data.id)" />
              <Icon icon="material-symbols:delete-outline" width="25" class="cursor-pointer"
                @click="getFileDelete(slotProps.data.id)" />
            </div>
          </template>
        </Column>
      </DataTable>

      <pagination :archives="deleted_files"/>
    </div>

    <div v-else class="flex items-center h-[65vh] justify-center flex-col">
      <img src="../../assets/images/empty_bin.svg" alt="" />
      <h1 class="font-semibold text-lg">Recycle Bin Empty</h1>
      <p>Deleted files and actions would appear here</p>
    </div>
  </main>
  <DeleteFileModal v-model="DeleteFile" @deletefile="deleteFileHandler" />
  <RestoreFileModal v-model="RestoreFile" @restore="restoreHandler" />
  <EmptyRecycleModal v-model="EmptyRecycle" />
</template>

<script setup>
import { ref, defineProps, onMounted } from "vue";
import DeleteFileModal from "../../components/Modals/RecycleBin/DeleteFile.vue";
import RestoreFileModal from "../../components/Modals/RecycleBin/RestoreFile.vue";
import EmptyRecycleModal from "../../components/Modals/RecycleBin/EmptyRecycle.vue";
import { onClickOutside } from "@vueuse/core";
import "vue-datepicker-ui/lib/vuedatepickerui.css";
import Datepicker from "vue-datepicker-ui";
import moment from "moment";
import { router } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import pagination from "../dashboard/partials/pagination.vue"

const selectedDate = ref([
  new Date(),
  new Date(Date.now() + 9 * 24 * 60 * 60 * 1000),
]);
const DeleteFile = ref(false);
const RestoreFile = ref(false);
const EmptyRecycle = ref(false);
const action = ref('File deletion');
const Dropdown = ref(false);
const modalRef = ref(null);
const selectedIndex = ref(null);
const props = defineProps(['deleted_files', 'type'])
const data = {
  search:'',
  date:'',
  type:''
}
const toast = useToast()
const selectedId = ref()

onClickOutside(modalRef, (event) => {
  Dropdown.value = false;
});
const action_type = ref([
  "Deleted Files",
  "Deleted Users",
  "Deleted Task",
  "Deleted Metadata",
  "Deleted Mails",
  "Deleted Units",
  "Deleted Divisions",
  "Deleted Departments"
]);

const formatDate = (date)=>{
  if(date){
    return moment(date).format('YYYY-MM-DD hh:mm A')
  }
}

const getDeletedFileName = (item)=>{
  if(props.type == 'Deleted Files'){
    let data = item.archive_docs.filter((item)=>{
      return item.archive_keys =='title'
    });
    console.log('this is the data', data)
    if(data && data.length > 0){
      return data[0]?.archive_values
    }else{
      ''
    }
  }

  if(props.type == 'Deleted Users'){
    return item.first_name+" "+item.last_name
  }

  if(props.type == 'Deleted Task'){

  }

  if(props.type == 'Deleted Metadata'){
    return item.name
  }

  if(props.type == 'Deleted Mails'){
    return item.subject
  }

  if(props.type == 'Deleted Units'){
    return item.title
  }

  if(props.type == 'Deleted Divisions'){
    return item.title
  }

  if(props.type == 'Deleted Departments'){
    return item.title
  }
  
}

const getFileRestore = (id)=>{
  selectedId.value = id
  RestoreFile.value = true
}

const getFileDelete = (id)=>{
  selectedId.value = id
  DeleteFile.value = true
}

const restoreRecycleFile = ()=>{
  let data = {
    id: selectedId.value,
    type: props.type
  }
  router.post('/dashboard/recycle-bin-restore', data, {onSuccess: page=>{
    router.reload({only: ['deleted_files']})
    toast.add({
      severity: "success",
      summary: 'Item Restored',
      detail: "The item has been successfully restored to its original location",
      life: 3000,
    });
  }})
}

const deleteRecyle = ()=>{
  let data = {
    id: selectedId.value,
    type: props.type
  }

  router.get('/dashboard/recycle-bin-delete', data, {onSuccess:page=>{
    router.reload({only: ['deleted_files']})
    toast.add({
      severity: "success",
      summary: 'Item Deleted',
      detail: "The item has been successfully deleted",
      life: 3000,
    });
  }})
}

const deleteFileHandler = ()=>{
  deleteRecyle()
}

const restoreHandler = ()=>{
  restoreRecycleFile()
}

const updateAction = (param, index) => {
  action.value = param;
  selectedIndex.value = index;
};

const refresh = ()=>{
  selectedDate.value = [
    new Date(),
    new Date(Date.now() + 9 * 24 * 60 * 60 * 1000),
  ]
  data.search = ''
}

const filter = ()=>{
  data.date = selectedDate.value;
  data.type = action.value

  if(data.search =='' &&  data.type == '' && data.date ==''){
    return false;
  }

  router.get('/dashboard/recycle-bin', data, {onSuccess: (e)=>{
    console.log(e)
  }})

}

onMounted(()=>{
  let url = window.location.href
  let urlObj = new URL(url);
  const type = urlObj.searchParams.get('type');
  let val = type? type: 'Deleted Files';
  action.value = val
})
</script>

<style></style>