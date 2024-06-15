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
        <div
          class="bg-[#EBF3FE] text-[#052B0A] font-semibold border-primary border-dashed border-[2px] px-2 py-4 mt-3 rounded-md relative cursor-pointer flex justify-center items-center gap-2"
          @click="newWorkFlowModal = true">
          <Icon icon="ic:outline-plus" width="25" />
          <h1>Create New Workflow</h1>
        </div>

        <div v-if="allWorkflows.length !== 0">
          <div v-for="(flow, index) in allWorkflows" :key="index" :class="{ ' bg-primary': selectedIndex === index }"
            class="mt-3 p-2 border rounded-md flex items-center justify-between cursor-pointer"
            @click="handleTask(flow, index)" ref="workflow_divs">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-full bg-[#EBF3FE]">
                <img :src="emojis[flow.emoji]? emojis[flow.emoji].image: ''" alt="">
              </div>
              <div>
                <h1 class="font-semibold text-[#1D2939]" :class="{ ' text-white': selectedIndex === index }">
                  {{ flow.title }}
                </h1>
                <small class="opacity-70" :class="{ ' text-white': selectedIndex === index }">{{ flow.task.length }} Tasks. Ongoing</small>
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
  <NewWorkflow v-model="newWorkFlowModal" @reloadPage="reloadPage" @newWorkflow="setNewWorkflow" />
  <!-- <AddTask v-model="addTask" /> -->
  <AssignTask v-model="assignTaskModal" :workflowId="selectedWorkflow?.id" @reloadTask="getTaskReload" @newTask="setNewTask"/>
  <ArchiveDocument v-model="ArchiveDocumentModal" />
  <AddMember v-model="addmemberModal" />
</template>

<script setup>
import { ref, watch, defineProps, onMounted, computed } from "vue";
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

import Emoji_1 from '../../../../public/images/Emojis/Emoji_1.svg'
import Emoji_2 from '../../../../public/images/Emojis/Emoji_2.svg'
import Emoji_3 from '../../../../public/images/Emojis/Emoji_3.svg'
import Emoji_4 from '../../../../public/images/Emojis/Emoji_4.svg'
import Emoji_5 from '../../../../public/images/Emojis/Emoji_5.svg'
import Emoji_6 from '../../../../public/images/Emojis/Emoji_6.svg'
import Emoji_7 from '../../../../public/images/Emojis/Emoji_7.svg'
import Emoji_8 from '../../../../public/images/Emojis/Emoji_8.svg'
import Emoji_9 from '../../../../public/images/Emojis/Emoji_9.svg'
import Emoji_10 from '../../../../public/images/Emojis/Emoji_10.svg'
import Emoji_11 from '../../../../public/images/Emojis/Emoji_11.svg'
import Emoji_12 from '../../../../public/images/Emojis/Emoji_12.svg'
import Emoji_13 from '../../../../public/images/Emojis/Emoji_13.svg'
import Emoji_14 from '../../../../public/images/Emojis/Emoji_14.svg'

const selectedIndex = ref(0);
const assignTaskModal = ref(false)
const ArchiveDocumentModal = ref(false);
const addmemberModal = ref(false);
const newWorkFlowModal = ref(false);
const addTask = ref(false);
const props = defineProps(["userworkflows"])
const selectedWorkflow = ref();
const is_newworkflow = ref(false);
const is_newTask = ref(false);
const workflow_divs = ref();

const reloadPage = () => {
  router.reload({ only: ['userworkflows'] })
  firstTask()
  selectedIndex.value = 0;
  is_newTask.value = false

  // const lastDiv = workflow_divs.value[0];
  // if (lastDiv) {
  //   lastDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
  // }
  //selectedWorkflow.value = allWorkflows[0];
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

const setNewWorkflow = ()=>{
  is_newworkflow.value = true;
}

const setNewTask = ()=>{
  is_newTask.value = true;
}

// const workflows = ref([
//   {
//     flow_name: "Archiver Workflow",
//     icon: "mdi:folder",
//     Tasks: [
//       {
//         status: "In Progress",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Under Review",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Completed",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Uncompleted",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//     ],
//   },
//   {
//     flow_name: "Bug Fixing",
//     icon: "mdi:folder",
//     Tasks: [
//       {
//         status: "In Progress",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Under Review",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Completed",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Uncompleted",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//     ],
//   },
//   {
//     flow_name: "Doc. Review Overflow",
//     icon: "mdi:folder",
//     Tasks: [
//       {
//         status: "In Progress",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Under Review",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Completed",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Uncompleted",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//     ],
//   },
//   {
//     flow_name: "Publications Workflow",
//     icon: "mdi:folder",
//     Tasks: [
//       {
//         status: "In Progress",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Under Review",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Completed",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//       {
//         status: "Uncompleted",
//         todo: [
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//           {
//             title: "Review and verify",
//             task: "Provide support and assistance to users regarding document archiving",
//           },
//         ],
//       },
//     ],
//   },
// ]);

const SelectedFlow = ref();

const menu = ref();

const getTaskReload = ()=>{
  is_newworkflow.value = false;
  router.reload({ only: ["userworkflows"] })
  
}

const handleTask = (flow, index) => {
  //SelectedFlow.value = flow;
  selectedIndex.value = index;
  selectedWorkflow.value = flow
};

const emojis = [
    {
        image:Emoji_1
    },
    {
      image:Emoji_2
    },
    {
      image:Emoji_3
    },
    {
      image:Emoji_4
    },
    {
      image:Emoji_5
    },
    {
      image:Emoji_6
    },
    {
      image:Emoji_7
    },
    {
      image:Emoji_8
    },
    {
      image:Emoji_9
    },
    {
      image:Emoji_10
    },
    {
      image:Emoji_11
    },
    {
      image:Emoji_12
    },
    {
      image:Emoji_13
    },
    {
      image:Emoji_14
    }
]


const sortTask = ()=>{
  let inp_progress = [];
  let under_review = [];
  let completed = [];
  let uncompleted = [];

  if(selectedWorkflow.value && selectedWorkflow.value.task && selectedWorkflow.value.task.length > 0){
    selectedWorkflow.value.task.forEach((val)=>{
      if(val.progress == 0){
        inp_progress.push({
          title: val.title,
          task: val.description,
          date: val.created_at,
          id: val.id
      })
      }

      if(val.progress == 1){
        under_review.push({
          title: val.title,
          task: val.description,
          date: val.created_at,
          id: val.id
      })
      }

      if(val.progress == 2){
        completed.push({
          title: val.title,
          task: val.description,
          date: val.created_at,
          id: val.id
      })
      }

      if(val.progress == 3){
        uncompleted.push({
          title: val.title,
          task: val.description,
          date: val.created_at,
          id: val.id
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
    selectedWorkflow.value = workflow
    
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

const allWorkflows = computed(()=>{
  return props.userworkflows
})


watch(selectedWorkflow, ()=>{
  sortTask()
})

onMounted(()=>{
  firstTask()
})

watch(()=>props.userworkflows, ()=>{
  if(selectedWorkflow.value){
    if(is_newworkflow){
      selectedWorkflow.value = props.userworkflows[0]
    }else{
      let index = props.userworkflows.findIndex((val)=> val.id == selectedWorkflow.value.id);
      selectedWorkflow.value = props.userworkflows[index]
    }
  }
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