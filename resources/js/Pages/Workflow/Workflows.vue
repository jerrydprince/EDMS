<template>
  <main class="p-7">
    <div class="bg-white border border-gray-200 rounded-xl lg:flex items-start p-7 w-full justify-between">
      <div class="flex items-start gap-5">
        <div class="p-3 border rounded-full">
          <Icon icon="lucide:workflow" width="30" />
        </div>
        <div>
          <h1 class="text-4xl">Workflow Dashboard</h1>
          <div class="text-sm flex items-center gap-5 mt-4">
            <div class="flex items-center gap-2 bg-primary/10 p-1 px-2 rounded-2xl">
              <Icon icon="mynaui:key" />
              <p v-if="roles && roles.length > 0" class="capitalize">{{roles[0]}}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="flex items-center justify-between mt-5 lg:mt-0 text-sm" v-if="!isRole('regular users')">
        <div class="bg-gray-100 flex items-center rounded-md p-1">
          <button v-if="!roles.includes('super users')" class="flex gap-1 items-center text-gray-400 rounded-lg py-2 px-4" @click="assignmentButton = 1"
            :class="{ activeButton: assignmentButton === 1 }">
            <p>Assigned to Me</p>
          </button>
          <button v-if="!roles.includes('super users')" class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4" @click="assignmentButton = 2"
            :class="{ activeButton: assignmentButton === 2  }">
            <p>Assigned by Me</p>
          </button>
        </div>
      </div>
    </div>
    <div class="flex items-center justify-end px-7 mt-5">
      <div class="flex items-center gap-2 text-white bg-primary px-3 py-2 rounded-md" v-if="assignmentButton === 2"
        @click="AssignModal = true">
        <Icon icon="ion:add-outline" width="25" />
        <button>Assign Task</button>
      </div>
    </div>
    <div class="bg-white mt-10 border border-gray-200 rounded-xl p-7 w-full">
      <div class="lg:flex items-center justify-between">
        <div class="flex gap-3 items-center justify-between text-sm">
          <div class="bg-gray-100 flex items-center rounded-md p-1">
            <button class="rounded-lg py-1 px-4" @click="activeButton('')"
              :class="{ activeButton: buttonSwitch === 1 }">
              <p>All Tasks</p>
            </button>
            <div>
              <button class="flex gap-2 items-center text-gray-400 rounded-lg py-1 px-4"
                @click="activeButton('Pending')" :class="{ activeButton: buttonSwitch === 2 }">
                <p>Pending</p>
              </button>
            </div>

            <div>
              <button class="flex gap-2 items-center text-gray-400 rounded-lg py-1 px-4"
                @click="activeButton('In Progress')" :class="{ activeButton: buttonSwitch === 3 }">
                <p>In Progress</p>
              </button>
            </div>

            <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
              @click="activeButton('Under Review')" :class="{ activeButton: buttonSwitch === 4 }">
              <p>Under Review</p>
            </button>
            <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
              @click="activeButton('Completed')" :class="{ activeButton: buttonSwitch === 5 }">
              <p>Completed</p>
            </button>
            <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
              @click="activeButton('Uncompleted')" :class="{ activeButton: buttonSwitch === 6 }">
              <p>Uncompleted</p>
            </button>
          </div>
        </div>

        <div class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
          <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
          <Input type="text" v-model="search" @input="getSearchResult" placeholder="Search Tasks"
            class="w-96 border-0 outline-none text-[#98A2B3]" />
        </div>
      </div>

      <!-- Assigned by me table -->
      <div class="overflow-x-scroll lg:overflow-hidden mt-10" v-if="assignmentButton == 2 && !roles.includes('regular users') || roles.includes('super users')">
        <table class="min-w-max w-full border">
          <thead class="bg-gray-100">
            <tr class="text-left">
              <th class="pl-5 py-5">Task Title</th>
              <th>Date Assigned</th>
              <th>Deadline</th>
              <th>Assigned By</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in filteredTask" :key="index" class="hover:bg-gray-50 cursor-pointer border-b">
              <td class="align-middle pl-5">
                <div class="flex items-center gap-2">
                  <div>
                    <p v-if="item.title && item.title.length > 0">
                      {{
              item.title.length > 20
                ? `${item.title.substring(0, 20)}...`
                : item.title
            }}
                    </p>
                  </div>
                </div>
              </td>

              <td class="align-middle">
                <div class="">
                  <p>{{ formatDate(item.created_at) }}</p>
                </div>
              </td>
              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <p>{{ formatDate(item.due_date) }}</p>
                </div>
              </td>
              <td class="align-middle">
                <div class="flex items-center gap-2" v-if="item.assigned_by">
                  <p>{{ item.assigned_by.first_name+" "+item.assigned_by.last_name }}</p>
                </div>
                <div class="flex items-center gap-2" v-if="item.assigned_by_division">
                  <p>{{item.assigned_by_division.title }}</p>
                </div>
                <div class="flex items-center gap-2" v-if="item.assigned_by_department">
                  <p>{{ item.assigned_by_department.department }}</p>
                </div>
              </td>
              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <p class="bg-[#FEFBF6] px-5 py-1 rounded-3xl" :style="{
              color:
                item.priority === '1'
                  ? '#FFBC53'
                  : item.priority === '2'
                    ? '#FF3737'
                    : item.priority === '3'
                      ? '#F67E5A'
                      : 'black',
            }">
                    {{ item.priority == 1? "Low": (item.priority == 2? 'Medium' : 'High') }}
                  </p>
                </div>
              </td>
              <td class="align-middle">
                <p class="bg-slate-50 rounded-3xl w-fit px-3 py-1" :style="{
              color:
                item.status === 'Completed'
                  ? '#0D6E1A'
                  : item.status === 'Under Review'
                    ? '#FB6514'
                    : item.status === 'In Progress'
                      ? '#F79009'
                      : item.status === 'Uncompleted'
                        ? '#F04438'
                        : '#F04438',
            }">
                  {{ item.status }}
                </p>
              </td>
              <td class="align-top py-3">
                <div class="flex items-center gap-3">
                  <Link :href="'/dashboard/view-task/'+item.id" method="post" :data="{is_assigned: item.is_assigned}">
                    <button class="flex items-center gap-2 border-[2px]  py-3 px-3 border-gray-300 rounded-md">
                      <Icon icon="ph:eye" />
                      <p class="text-sm">View Task</p>
                    </button>
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Assigned to me table  -->
      <div class="overflow-x-scroll lg:overflow-hidden mt-10" v-if="assignmentButton == 1 && !roles.includes('super users') || roles.includes('regular users')">
        <table class="min-w-max w-full border">
          <thead class="bg-gray-100">
            <tr class="text-left">
              <th class="pl-5 py-5">Task Title</th>
              <th>Date Assigned</th>
              <th>Deadline</th>
              <th>Assigned By</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in filteredAssignedTask" :key="index" class="hover:bg-gray-50 cursor-pointer border-b">
              <td class="align-middle pl-5">
                <div class="flex items-center gap-2">
                  <div>
                    <p v-if="item.title && item.title.length > 0">
                      {{
              item.title.length > 20
                ? `${item.title.substring(0, 20)}...`
                : item.title
            }}
                    </p>
                  </div>
                </div>
              </td>

              <td class="align-middle">
                <div class="">
                  <p>{{ formatDate(item.created_at) }}</p>
                </div>
              </td>
              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <p>{{ formatDate(item.due_date) }}</p>
                </div>
              </td>
              <td class="align-middle">
                <div class="flex items-center gap-2" v-if="item.assigned_by">
                  <p>{{ item.assigned_by.first_name+" "+item.assigned_by.last_name }}</p>
                </div>
                <div class="flex items-center gap-2" v-if="item.assigned_by_division">
                  <p>{{ item.assigned_by_division.title }}</p>
                </div>
                <div class="flex items-center gap-2" v-if="item.assigned_by_department">
                  <p>{{ item.assigned_by_department.title }}</p>
                </div>
              </td>
              <td class="align-middle">
                <div class="flex items-center gap-2">
                  <p class="bg-[#FEFBF6] px-5 py-1 rounded-3xl" :style="{
              color:
                item.priority === '1'
                  ? '#FFBC53'
                  : item.priority === '2'
                    ? '#FF3737'
                    : item.priority === '3'
                      ? '#F67E5A'
                      : 'black',
            }">
                    {{ item.priority == 1? "Low": (item.priority == 2? 'Medium' : 'High') }}
                  </p>
                </div>
              </td>
              <td class="align-middle">
                <p class="bg-slate-50 rounded-3xl w-fit px-3 py-1" :style="{
              color:
                item.status === 'Completed'
                  ? '#0D6E1A'
                  : item.status === 'Under Review'
                    ? '#FB6514'
                    : item.status === 'In Progress'
                      ? '#F79009'
                      : item.status === 'Uncompleted'
                        ? '#F04438'
                        : '#F04438',
            }">
                  {{ item.status }}
                </p>
              </td>
              <td class="align-top py-3">
                <div class="flex items-center gap-3">
                 
                  <Link :href="'/dashboard/view-task/'+item.id" method="post" :data="{is_assigned: item.is_assigned}">
                    <button class="flex items-center gap-2 border-[2px]  py-3 px-3 border-gray-300 rounded-md">
                      <Icon icon="ph:eye" />
                      <p class="text-sm">View Task</p>
                    </button>
                  </Link>
                  
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-5">
        <!-- <paginator
          :totalItems="totalItems"
          :itemsPerPage="itemsPerPage"
          v-model="currentPage"
        /> -->
      </div>
    </div>
  </main>
  <AssignTask v-model="AssignModal" />
</template>

<script setup>
import { ref, computed, defineProps, watch, inject, onMounted } from "vue";
//import paginator from "@/reusables/paginations.vue";
import AssignTask from "../../components/Modals/Workflows/AssignTask.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import debounce from "lodash.debounce"
import { create } from "lodash";

const AssignModal = ref(false)
const assignmentButton = ref(1);
const buttonToggle = ref(1);
const currentPage = ref(1);
const itemsPerPage = 10;
const tasks = ref([])
const page = usePage()
const search = ref('')
const props = defineProps(['created_task', 'assigned_task'])

const roles = computed(()=> page.props.roles)

const notifications = inject('notifications')
const created_task = ref([])
const assigned_task = ref([])

const buttonSwitch = ref(1);
const clickedButton = ref("");
const activeButton = (event) => {
  if (event === "") {
    buttonSwitch.value = 1;
    taskByStatus("")
  }
  if (event === "Pending") {
    buttonSwitch.value = 2;
    taskByStatus('pending');
  }
  if (event === "In Progress") {
    buttonSwitch.value = 3;
    taskByStatus('in progress')
  }
  if (event === "Under Review") {
    buttonSwitch.value = 4;
    taskByStatus('under review')
  }
  if (event === "Completed") {
    buttonSwitch.value = 5;
    taskByStatus('completed')
  }
  if (event === "Uncompleted") {
    buttonSwitch.value = 6;
    taskByStatus('uncompleted')
  }
  clickedButton.value = event;
};

const isRole = (role)=>{
  if(roles.value.includes(role)){
    return true
  }else{
    return false
  }
}

const taskByStatus = (status)=>{
  let type = assignmentButton.value ==1? 'assigned_to_me': 'assigned_by_me';
  axios.get('/dashboard/task-by-status',{params:{status: status, type: type}}).then((response)=>{
    tasks.value = response.data.data;
  })
}

const formatDate =(inputDate)=> {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();

    day = day < 10 ? '0' + day : day;

    var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);

    return formattedDate;
}

const filteredItems = computed(() => {
  if (clickedButton.value === '') {
    return items.value;
  }
  return items.value.filter(item =>
    item.status.toLowerCase() === clickedButton.value.toLowerCase()
  );
});

const filteredTask = computed(()=>{
  if( buttonSwitch.value == 1){
    return created_task.value?.data
  }else
  if(buttonSwitch.value == 2){
    let data = created_task.value.data.filter((item)=> item.status==='pending');
    return data
  }else
  if(buttonSwitch.value == 3){
    let data = created_task.value.data.filter((item)=>item.status=='in progress');
    return data
  }else
  if(buttonSwitch.value == 4){
    let data = created_task.value.data.filter((item)=>item.status=='under review');
    return data
  }else
  if(buttonSwitch.value == 5){
    let data = created_task.value.data.filter((item)=>item.status=='completed');
    return data
  }else
  if(buttonSwitch.value == 6){
    let data = created_task.value.data.filter((item)=>item.status=='uncompleted');
    return data
  }
  // else
  // {
  //   return props.created_task?.data
  // }
})

const filteredAssignedTask = computed(()=>{
  // if(buttonSwitch.value !== 1){
  //   return tasks.value
  // }else{
  //   return props.assigned_task?.data
  // }
   if( buttonSwitch.value == 1){
    return assigned_task.value?.data
  }else
  if(buttonSwitch.value == 2){
    let data = assigned_task.value.data.filter((item)=> item.status==='pending');
    return data
  }else
  if(buttonSwitch.value == 3){
    let data = assigned_task.value.data.filter((item)=>item.status=='in progress');
    return data
  }else
  if(buttonSwitch.value == 4){
    let data = assigned_task.value.data.filter((item)=>item.status=='under review');
    return data
  }else
  if(buttonSwitch.value == 5){
    let data = assigned_task.value.data.filter((item)=>item.status=='completed');
    return data
  }else
  if(buttonSwitch.value == 6){
    let data = assigned_task.value.data.filter((item)=>item.status=='uncompleted');
    return data
  }
})
const pageStart = ref(1);
const pageEnd = ref(1);
const displayedItems = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;

  pageStart.value = startIndex;
  pageEnd.value = endIndex;

  return filteredItems.value.slice(startIndex, endIndex);
});

watch(()=>props.created_task, ()=>{

},{deep: true})


watch(assignmentButton, (newVal)=>{
  buttonSwitch.value = 1;
  // if(newVal == 1){
  //   router.reload({only: ['created_at']})
  // }else{
  //   router.reload({only:['assigned_task']})
  // }
})

watch(notifications, (newVal)=>{
  if(newVal.Today && newVal.Today[0].data.task){
      router.reload({ only: ['assigned_task'] })
    }
})

const getSearchResult = debounce((event)=>{
  let search_value = event.target.value;
  axios.get('/dashboard/search-workflow',{params:{search:search_value}}).then((response)=>{
    created_task.value = response.data.created_task
    assigned_task.value = response.data.assigned_task
  })
}, 500)

watch(()=>props.assigned_task, ()=>{
  assigned_task.value = props.assigned_task
},{immediate:true})

watch(()=>props.created_task, ()=>{
  created_task.value = props.created_task
},{immediate:true})

onMounted(()=>{
  if(roles.value.includes('super users')){
    assignmentButton.value = 2;
  }
})

</script>

<style scoped>
.activeButton {
  background-color: white;
  border-radius: 5px;
  color: black;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>