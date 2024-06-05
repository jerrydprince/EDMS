<template>
  <main class="h-screen flex w-full">
    <section
      class="w-[30%] border-r border-solid border-gray-200 sticky top-0 h-screen py-5 overflow-y-scroll hide_scroll">
      <div class="px-3">
        <h1 class="text-3xl">Workflows</h1>
        <p class="text-[#344054]">Choose between workflows</p>
      </div>
      <Divider />
      <div class="flex items-center justify-between text-sm">
        <div class="bg-gray-100 justify-between flex items-center w-full mx-3 rounded-md p-1">
          <button class="bg-white shadow-md border-gray-300 rounded-lg py-1 px-3">
            <p>All <small>(12) </small></p>
          </button>
          <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-3">
            <p>Opened <small>(12) </small></p>
          </button>
          <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-3">
            <p>Closed <small>(12) </small></p>
          </button>
        </div>
      </div>
      <div class="px-3">
        <div v-if="workflows.length !== 0">
          <div v-for="(flow, index) in props.userworkflows" :key="index" :class="{ ' bg-primary': selectedIndex === index }"
            class="mt-3 p-2 border rounded-md flex items-center justify-between cursor-pointer"
            @click="handleTask(flow, index)">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-full bg-[#EBF3FE]">
                <Icon :icon="emojis[flow.emoji]?.icon" width="25" />
              </div>
              <div>
                <h1 class="font-semibold text-[#1D2939]" :class="{ ' text-white': selectedIndex === index }">
                  {{ flow.title }}
                </h1>
                <small class="opacity-70" :class="{ ' text-white': selectedIndex === index }">20 Tasks. Ongoing</small>
              </div>
            </div>
            <div>
              <Icon icon="mi:options-vertical" width="25" :class="{ ' text-white': selectedIndex === index }" />
            </div>
          </div>
        </div>
        <div v-else class=" flex flex-col items-center justify-center px-4 mt-10 mb-5 ">

          <img src="../../assets/images/No_workflow.svg" alt="" class=" w-full" />
          <div class=" text-center px-7 mt-4">
            <h1 class="font-semibold text-base">No Workflow Found</h1>
            <p class=" text-center text-base opacity-80">click on the button below
              <Icon icon="formkit:arrowdown" class="inline-flex " /> to create new workflow
            </p>
          </div>
        </div>
        <div
          class="bg-[#EBF3FE] text-[#052B0A] font-semibold border-primary border-dashed border-[2px] px-2 py-4 mt-3 rounded-md relative cursor-pointer flex justify-center items-center gap-2"
          @click="newWorkFlowModal = true">
          <Icon icon="ic:outline-plus" width="25" />
          <h1>Create New Workflow</h1>
        </div>
      </div>
    </section>
    <section class="hide_scroll w-full overflow-y-scroll">
      <div v-if="SelectedFlow">
        <div class="flex items-center justify-between w-full px-7 pt-5">
          <div class="flex items-center gap-3">
            <div class="border rounded-full p-3">
              <Icon :icon="SelectedFlow.icon" width="30" />
            </div>
            <div>
              <h1 class="text-3xl text-[#1D2939]">
                {{ SelectedFlow.flow_name }}
              </h1>
              <div class="flex items-center gap-2">
                <div class="border rounded-md w-80 h-1">
                  <div class="h-full w-[13%] bg-primary"></div>
                </div>
                <p class="opacity-70">13%</p>
              </div>
            </div>
          </div>
          <div>
            <div class="flex items-center gap-10">
              <div class="flex items-center">
                <div v-for="(member, index) in members" :key="index" class="-ml-3 w-10 h-10 rounded-full">
                  <img :src="member.img" alt="" class="h-full w-full object-cover" />
                </div>

                <div
                  class="-ml-3 bg-[#E2ECFE] text-primary font-medium border-[2px] border-white py-1.5 px-2 rounded-full">
                  <p>+5</p>
                </div>
                <div class="border-dashed ml-2 border p-2.5 rounded-full cursor-pointer" @click="addmemberModal = true">
                  <Icon icon="ic:baseline-plus" class="opacity-50" />
                </div>
              </div>
              <button class="border border-gray-300 text-[#344054] rounded p-2 px-5 font-medium"
                @click="assignTaskModal = true">
                Assign Task
              </button>
            </div>
          </div>
        </div>
        <Divider />
        <div>
          <BoardContainer :flowTasks="SelectedFlow.Tasks" />
        </div>
      </div>
      <div v-else class="flex items-center h-[90vh] justify-center flex-col">
        <img src="../../assets/images/no_task.svg" alt="" />
        <h1 class="font-semibold text-lg">No Workflow Found</h1>
        <p>Let's get you started – create your first workflow now!</p>
        <div
          class=" text-[#052B0A] font-semibold border-gray-300  border-[1px] px-2 py-3 mt-3 rounded-md relative cursor-pointer flex justify-center items-center gap-2"
          @click="newWorkFlowModal = true">
          <Icon icon="ic:outline-plus" width="25" />
          <h1>Create New Workflow</h1>
        </div>
      </div>
    </section>
  </main>
  <NewWorkflow v-model="newWorkFlowModal" @reloadPage="reloadPage" />
  <!-- <AddTask v-model="addTask" /> -->
  <AssignTask v-model="assignTaskModal" :workflowId="selectedWorkflow?.id" />
  <ArchiveDocument v-model="ArchiveDocumentModal" />
  <AddMember v-model="addmemberModal" />
</template>

<script setup>
import { ref, watch, defineProps, onMounted } from "vue";
import image1 from "../../assets/images/members/1.svg";
import image2 from "../../assets/images/members/2.svg";
import image3 from "../../assets/images/members/3.svg";
import image4 from "../../assets/images/members/4.svg";
import image5 from "../../assets/images/members/5.svg";
import BoardContainer from "../../components/BoardContainer.vue"
import NewWorkflow from "../../components/Modals/Workflows/NewWorkflow.vue";
// import AddTask from "../../components/Modals/Workflows/AddTask.vue";
import AssignTask from "../../components/Modals/Workflows/AssignTask.vue"
import ArchiveDocument from "../../components/Modals/Workflows/ArchiveDocument.vue";
import AddMember from "../../components/Modals/Workflows/AddMember.vue";
import { router } from '@inertiajs/vue3';

const selectedIndex = ref(0);
const assignTaskModal = ref(false)
const ArchiveDocumentModal = ref(false);
const addmemberModal = ref(false);
const newWorkFlowModal = ref(false);
const addTask = ref(false);
const props = defineProps(['userworkflows'])
const selectedWorkflow = ref();

const reloadPage = () => {
  router.reload({ only: ['userworkflows'] })
}


const members = ref([
  {
    img: image1,
  },
  {
    img: image2,
  },
  {
    img: image3,
  },
  {
    img: image4,
  },
  {
    img: image5,
  },
]);
const workflows = ref([
  {
    flow_name: "Archiver Workflow",
    icon: "mdi:folder",
    Tasks: [
      {
        status: "In Progress",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Under Review",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Completed",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Uncompleted",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
    ],
  },
  {
    flow_name: "Bug Fixing",
    icon: "mdi:folder",
    Tasks: [
      {
        status: "In Progress",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Under Review",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Completed",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Uncompleted",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
    ],
  },
  {
    flow_name: "Doc. Review Overflow",
    icon: "mdi:folder",
    Tasks: [
      {
        status: "In Progress",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Under Review",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Completed",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Uncompleted",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
    ],
  },
  {
    flow_name: "Publications Workflow",
    icon: "mdi:folder",
    Tasks: [
      {
        status: "In Progress",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Under Review",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Completed",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
      {
        status: "Uncompleted",
        todo: [
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
          {
            title: "Review and verify",
            task: "Provide support and assistance to users regarding document archiving",
          },
        ],
      },
    ],
  },
]);

const SelectedFlow = ref();

const menu = ref();

const handleTask = (flow, index) => {
  console.log(flow)
  //SelectedFlow.value = flow;
  selectedIndex.value = index;
  selectedWorkflow.value = flow
};

const emojis = [
  {
    icon: `<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect width="32" height="32" rx="16" fill="#F9FAFB"/>
<path d="M24 17.2964C24 16.9907 22.7133 17.0742 22.6111 16.852C22.5089 16.6298 24.0369 14.8147 23.8147 14.4076C23.5924 14.0004 21.5542 14.9262 21.4258 14.7222C21.2978 14.5187 21.5556 13.1667 21.3333 13C21.1111 12.8333 19 15.1849 18.7036 14.9076C18.4076 14.6298 19.3889 10.2409 19.1862 10.1529C18.9836 10.0649 17.6667 10.7596 17.5004 10.7227C17.3342 10.6853 16.3031 8 16 8C15.6969 8 14.6658 10.6853 14.4996 10.7222C14.3333 10.7591 13.0164 10.0644 12.8138 10.1524C12.6111 10.2404 13.5924 14.6293 13.296 14.9071C13 15.1849 10.8889 12.8333 10.6667 13C10.4444 13.1667 10.7022 14.5187 10.5742 14.7222C10.4458 14.9258 8.40756 14 8.18533 14.4076C7.96311 14.8151 9.49156 16.6298 9.38889 16.852C9.28622 17.0742 8 16.9907 8 17.2964C8 17.6022 11.7502 19.6231 12 19.8613C12.2498 20.0991 10.7049 21.1067 10.9258 21.2964C11.1818 21.5169 12.632 20.9276 15.5556 20.8711V23.5556C15.5556 23.8013 15.7547 24 16 24C16.2458 24 16.4444 23.8013 16.4444 23.5556V20.8711C19.3676 20.9276 20.8182 21.5169 21.0742 21.2964C21.2947 21.1062 19.7502 20.0991 20 19.8613C20.2498 19.6231 24 17.6018 24 17.2964Z" fill="#DD2E44"/>
</svg>
`
  },
  {
    icon: "noto:eight-pointed-star"
  },
  {
    icon: "noto:eight-pointed-star"
  },
  {
    icon: "noto:eight-pointed-star"
  },
  {
    icon: "noto:eight-pointed-star"
  },
  {
    icon: "noto:eight-pointed-star"
  },

]

const sortTask = ()=>{
  let inp_progress = [];
  let under_review = [];
  let completed = [];
  let uncompleted = [];

  if(selectedWorkflow.value.task && selectedWorkflow.value.task.length > 0){
    selectedWorkflow.value.task.forEach((val)=>{
      if(val.progress == 0){
        inp_progress.push({
          title: val.title,
          task: val.description
      })
      }

      if(val.progress == 1){
        under_review.push({
          title: val.title,
          task: val.description
      })
      }

      if(val.progress == 2){
        completed.push({
          title: val.title,
          task: val.description
      })
      }

      if(val.progress == 3){
        uncompleted.push({
          title: val.title,
          task: val.description
      })
      }
    })
  }

  SelectedFlow.value = {
    Tasks:[
    {
      status: 'In Progress',
      todo: inp_progress
    },
    {
      status: 'Under Review',
      todo: under_review
    },
    {
      status: 'Completed',
      todo: completed
    },
    {
      status: 'Uncompleted',
      todo: uncompleted
    }
    ]
  }
 
}

const firstTask = ()=>{
  if(props.userworkflows && props.userworkflows.length > 0){
    let inp_progress = [];
    let under_review = [];
    let completed = [];
    let uncompleted = [];

    let workflow = props.userworkflows[0];
    let task = workflow.task;
    console.log(task)
    task.forEach((tsk)=>{
      if(tsk.progress == 0){
        inp_progress.push({
          title: tsk.title,
          task: tsk.description
      })
      }

      if(tsk.progress == 1){
        under_review.push({
          title: tsk.title,
          task: tsk.description
      })
      }

      if(tsk.progress == 2){
        completed.push({
          title: tsk.title,
          task: tsk.description
      })
    
      }

      if(task.progress == 3){
        uncompleted.push({
          title: tsk.title,
          task: tsk.description
      })
     
      }
    })

      SelectedFlow.value = {
        Tasks:[
        {
          status: 'In Progress',
          todo: inp_progress
        },
        {
          status: 'Under Review',
          todo: under_review
        },
        {
          status: 'Completed',
          todo: completed
        },
        {
          status: 'Uncompleted',
          todo: uncompleted
        }
        ]
      }

  }
}


watch(selectedWorkflow, ()=>{
  sortTask()
})

onMounted(()=>{
  firstTask()
  console.log(SelectedFlow.value)
})

</script>

<style>
.grid-item {
  display: inline-block;
  vertical-align: top;
  width: calc(25% - 1.25rem);
  /* Adjust width based on grid column count and gap */
}
</style>