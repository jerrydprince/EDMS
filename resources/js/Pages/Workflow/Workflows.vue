<template>
  <main class="h-screen flex w-full">
    <section
      class="w-[30%] border-r border-solid border-gray-200 sticky top-0 h-screen py-5 overflow-y-scroll hide_scroll"
    >
      <div class="px-3">
        <h1 class="text-3xl">Workflows</h1>
        <p class="text-[#344054]">Choose between workflows</p>
      </div>
      <Divider />
      <div class="flex items-center justify-between text-sm">
        <div
          class="bg-gray-100 justify-between flex items-center w-full mx-3 rounded-md p-1"
        >
          <button
            class="bg-white shadow-md border-gray-300 rounded-lg py-1 px-3"
          >
            <p>All <small>(12) </small></p>
          </button>
          <button
            class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-3"
          >
            <p>Opened <small>(12) </small></p>
          </button>
          <button
            class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-3"
          >
            <p>Closed <small>(12) </small></p>
          </button>
        </div>
      </div>
      <div class="px-3">
        <div v-if="workflows.length !== 0">
        <div
          v-for="(flow, index) in workflows"
          :key="index"
          :class="{ ' bg-primary': selectedIndex === index }"
          class="mt-3 p-2 border rounded-md flex items-center justify-between cursor-pointer"
          @click="handleTask(flow, index)"
        >
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-full bg-[#EBF3FE]">
              <Icon :icon="flow.icon" width="25" />
            </div>
            <div>
              <h1
                class="font-semibold text-[#1D2939]"
                :class="{ ' text-white': selectedIndex === index }"
              >
                {{ flow.flow_name }}
              </h1>
              <small
                class="opacity-70"
                :class="{ ' text-white': selectedIndex === index }"
                >20 Tasks. Ongoing</small
              >
            </div>
          </div>
          <div>
            <Icon
              icon="mi:options-vertical"
              width="25"
              :class="{ ' text-white': selectedIndex === index }"
            />
          </div>
        </div>
      </div>
      <div v-else class=" flex flex-col items-center justify-center px-4 mt-10 mb-5 ">
       
        <img src="../../assets/images/No_workflow.svg" alt=""  class=" w-full"/>
        <div class=" text-center px-7 mt-4">
        <h1 class="font-semibold text-base">No Workflow Found</h1>
        <p class=" text-center text-base opacity-80">click on the button below <Icon icon="formkit:arrowdown"  class="inline-flex "/>  to create new workflow</p>
      </div>
      </div>
        <div
          class="bg-[#EBF3FE] text-[#052B0A] font-semibold border-primary border-dashed border-[2px] px-2 py-4 mt-3 rounded-md relative cursor-pointer flex justify-center items-center gap-2"
          @click="newWorkFlowModal = true"
        >
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
                <div
                  v-for="(member, index) in members"
                  :key="index"
                  class="-ml-3 w-10 h-10 rounded-full"
                >
                  <img
                    :src="member.img"
                    alt=""
                    class="h-full w-full object-cover"
                  />
                </div>

                <div
                  class="-ml-3 bg-[#E2ECFE] text-primary font-medium border-[2px] border-white py-1.5 px-2 rounded-full"
                >
                  <p>+5</p>
                </div>
                <div
                  class="border-dashed ml-2 border p-2.5 rounded-full cursor-pointer"
                  @click="addmemberModal = true"
                >
                  <Icon icon="ic:baseline-plus" class="opacity-50" />
                </div>
              </div>
              <button
                class="border border-gray-300 text-[#344054] rounded p-2 px-5 font-medium"
                @click="ArchiveDocumentModal = true"
              >
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
          @click="newWorkFlowModal = true"
        >
          <Icon icon="ic:outline-plus" width="25" />
          <h1>Create New Workflow</h1>
        </div>
      </div>
    </section>
  </main>
  <NewWorkflow v-model="newWorkFlowModal" />
  <AddTask v-model="addTask" />
  <ArchiveDocument v-model="ArchiveDocumentModal" />
  <AddMember v-model="addmemberModal" />
</template>

<script setup>
import { ref, watch } from "vue";
import image1 from "../../assets/images/members/1.svg";
import image2 from "../../assets/images/members/2.svg";
import image3 from "../../assets/images/members/3.svg";
import image4 from "../../assets/images/members/4.svg";
import image5 from "../../assets/images/members/5.svg";
import BoardContainer from "../../components/BoardContainer.vue"
import NewWorkflow from "../../components/Modals/Workflows/NewWorkflow.vue";
import AddTask from "../../components/Modals/Workflows/AddTask.vue";
import ArchiveDocument from "../../components/Modals/Workflows/ArchiveDocument.vue";
import AddMember from "../../components/Modals/Workflows/AddMember.vue";
const selectedIndex = ref(0);
const ArchiveDocumentModal = ref(false);
const addmemberModal = ref(false);
const newWorkFlowModal = ref(false);
const addTask = ref(false);


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

const SelectedFlow = ref(workflows.value[0]);
console.log(SelectedFlow.value)
const menu = ref();

const handleTask = (flow, index) => {
  SelectedFlow.value = flow;
  selectedIndex.value = index;
};


</script>

<style>
.grid-item {
  display: inline-block;
  vertical-align: top;
  width: calc(
    25% - 1.25rem
  ); /* Adjust width based on grid column count and gap */
}
</style>