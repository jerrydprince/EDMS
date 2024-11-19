<template>
  <main>
    <div class=" px-7 py-5 ">
      <div class=" flex items-center gap-3">
        <div class=" p-3 border rounded-full ">
          <Icon icon="uil:layers" width="30" />
        </div>
        <div>
          <h1 class=" text-3xl font-semibold text-[#1D2939]">Audit Log</h1>
          <p class=" text-[#667085] font-semibold text-base">Monitor any changes/ activities carried out in the EDMS</p>
        </div>
      </div>
    </div>
    <Divider class=" -mt-1" />

    <div
      class="grid mx-7 grid-cols-[300px_300px_300px_300px_300px] bg-gray-50 border p-7 rounded-lg border-gray-300  lg:grid-cols-5 items-end overflow-x-scroll lg:overflow-hidden mt-4 gap-5 lg:gap-10">
      <div class=" text-[#344054] font-semibold w-full">
        <label for="Action">Event</label> <br>
        <select v-model="search.event" id="" class=" mt-2 border rounded-md w-full p-2 outline-none border-gray-300">
          <option value="">Select Option</option>
          <option value="login">Login</option>
          <option value="logout">Logout</option>
          <option value="Document archived">Document Archived</option>
          <option value="Document updated">Document Updated</option>
          <option value="Document viewed">Document Viewed</option>
          <option value="Document deleted">Document Deleted</option>
        </select>
      </div>
      <div class=" text-[#344054] font-semibold w-full">
        <label for="Action">User</label> <br>
        <div class=" flex items-center mt-2 border bg-white rounded-md w-full p-2 outline-none border-gray-300">
          <input type="text" v-model="search.user" placeholder="search user" class="w-full bg-white  h-full outline-none">
          <Icon icon="iconamoon:search-light" width="20" />
        </div>
      </div>
      <div class=" text-[#344054] font-semibold w-full">
        <label for="Action">Source</label> <br>
        <select v-model="search.device" id="" class=" mt-2 border rounded-md w-full p-2 outline-none border-gray-300">
          <option value="">Select Source</option>
          <option value="Web">Web</option>
          <option value="Mobile">Mobile</option>
          <option value="Desktop">Desktop</option>
        </select>
      </div>
      <div class=" text-[#344054] font-semibold w-full">
        <label for="Action">Date</label> <br>
        <div class=" flex items-center mt-2 border rounded-md w-full p-2 outline-none border-gray-300">
          <input v-model="search.date" type="date" placeholder="Select Date " class=" w-full bg-transparent">
        </div>
      </div>
      <div class=" w-full">
        <div class="  w-full flex items-center gap-5">
          <button class=" bg-primary text-white px-3 rounded-md p-2" @click="searchAudit">Apply filter</button>
          <div>
            <Icon icon="tabler:refresh" class="cursor-pointer" @click="refresh" width="30" />
          </div>
        </div>
      </div>
    </div>

    <div class=" m-7 ">
      <DataTable :value="props.activities.data" tableStyle="min-width: 50rem">
        <Column field="Timestamp" header="Timestamp" class="">
          <template #body="slotProps">
            <div>
              {{ formatDate(slotProps.data.created_at) }}
            </div>
          </template>
        </Column>
        <Column field="Actor" header="Actor">
          <template #body="slotProps">
            <div>
              {{ slotProps.data.properties?.user }}
            </div>
          </template>
        </Column>
        <Column field="Event" header="Event">
          <template #body="slotProps">
            <div>
              {{ slotProps.data.properties?.event }}
            </div>
          </template>
        </Column>
        <Column field="source" header="source">
          <template #body="slotProps">
            <div class="">
              <p>{{ slotProps.data.properties?.device }}</p>
              <p class="text-[#667085]">{{ slotProps.data.properties?.ip }}</p>
            </div>
          </template>
        </Column>
        <Column field="doc_name" header="Document Name">
          <template #body="slotProps">
            <div>
              {{ getDocumentTitle(slotProps.data.properties) }}
            </div>
          </template>
        </Column>
        <Column field="" header="Actions">
          <template #body="slotProps">
            <div class=" flex gap-5 items-center">
              <Icon icon="ion:eye-outline" width="25" class=" cursor-pointer" @click="viewLog(slotProps.data)" />
              <!-- <Icon icon="material-symbols:delete-outline" width="25" class=" cursor-pointer"
                @click="deleteAuditModal = true" /> -->
            </div>
          </template>
        </Column>
      </DataTable>
      <pagination :archives="props.activities"/>
    </div>
  </main>
  <DeleteAudit v-model="deleteAuditModal" />
  <ViewLog v-model="viewLogModal" :audit="selectedAudit" />
</template>

<script setup>
import { ref, defineProps, onMounted, watch } from 'vue';
import DeleteAudit from '../../components/Modals/Audit/DeleteAudit.vue'
import ViewLog from '../../components/Modals/Audit/viewLog.vue';
import moment from 'moment';
import pagination from "../dashboard/partials/pagination.vue"
import { filter } from 'lodash';
import { router } from '@inertiajs/vue3';

const props = defineProps(['activities', 'search']);

const viewLogModal = ref(false)
const deleteAuditModal = ref(false)
const selectedAudit = ref()

const formatDate = (date)=>{
  if(date){
    return moment(date).format('YYYY-MM-DD hh:mm A')
  }
}

const search = {
  event: '',
  user: '',
  device: '',
  date:''
}

const searchAudit = ()=>{
  router.get('/dashboard/audit-log',{search:search}, {onSuccess: page => {
   // console.log(page)
  }})
}

const viewLog = (data)=>{
  viewLogModal.value = true
  selectedAudit.value = data
}

const refresh = ()=>{
  search.event = ''
  search.user = ''
  search.device = ''
  search.date = ''

  router.get('/dashboard/audit-log',{},{})
}


const getDocumentTitle = (data)=>{
  let doctype = data?.document_type?.archive_docs
 
  if(doctype){
    let filtered = doctype.filter(item=> item.archive_keys == 'title');
   if(filtered && filtered.length > 0){
    
    return filtered.map(item => item.archive_values).join(', ');
   }else{
    return ''
   }
  }else{
    return ''
  }
}

const setSearchParameters = ()=>{
  if(props.search && props.search.length > 0){
    
    search.event = props.search.event
    search.user = props.search.user
    search.device = props.search.device
    search.date = props.search.date
  }
}

watch(()=>props.search, ()=>{
    search.event = props.search.event?props.search.event: ''
    search.user = props.search.user?props.search.user: ''
    search.device = props.search.device? props.search.device:''
    search.date = props.search.date? props.search.date: ''
},
{immediate:true})

onMounted(()=>{
  setSearchParameters()
})
</script>

<style></style>