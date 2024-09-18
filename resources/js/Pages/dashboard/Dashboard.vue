<template>
  <main class="p-4 lg:p-7 relative">
    <section class=" flex-col lg:flex-row items-start lg:items-center justify-between cursor-pointer">
      <div class=" md:flex items-center gap-5">
        <p class="text-2xl lg:text-3xl text-gray-800 font-semibold">
          Welcome Back
        </p>
        <p class=" bg-gray-50 text-primary text-xs  md:text-sm font-semibold rounded-3xl py-2 px-5 w-fit mt-2 md:mt-0">
          Communication Division</p>
      </div>
      <p class=" text-sm mt-2 text-gray-600">Monday, OcDeadline: 2024-06-01</p>
    </section>
    <section class="w-full text-gray-800 bg-primary_bg border border-solid border-gray-300 rounded-xl mt-7 p-3 lg:p-6">
      <div class="flex items-center gap-5">
        <Actions />
        <p class="text-xl lg:text-2xl font-medium">Quick Actions</p>
      </div>
      <div
        class="grid grid-cols-[270px_270px_270px_270px] lg:grid-cols-4 overflow-x-scroll lg:overflow-hidden mt-4 gap-5 lg:gap-10">
        <div class="relative h-[270px]  w-full">
          <Link :href="'/dashboard/workflow'">
          <QuickActions1 class="absolute top-0  cursor-pointer" />
          </Link>
          <div class="z-[99] absolute bottom-0 h-[45%] pb-10 pt-7  rounded-xl">
            <p class="text-xl font-semibold mb-2">Workflow</p>
            <p class="text-gray-500 text-sm">
              Manage and track the progress of tasks and processes.
            </p>
          </div>
        </div>
        <div class="relative h-[270px]  rounded-xl w-full">
          <Link :href="'/dashboard/mails'">
          <QuickActions2 class="absolute top-0 left-[6%]" />
          </Link>
          <div class="z-[99]  absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl">
            <p class="text-xl font-semibold mb-2">Mail</p>
            <p class="text-gray-500 text-sm">
              Send, receive, and manage emails
            </p>
          </div>
        </div>
        <div class="relative h-[270px]  rounded-xl w-full">
          <Link :href="'/dashboard/pms'">
          <QuickActions3 class="absolute top-0 left-[6%]" />
          </Link>
          <div class="z-[99]  absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl">
            <p class="text-xl font-semibold mb-2">PMS</p>
            <p class="text-gray-500 text-sm">
              Monitor and evaluate employee performance
            </p>
          </div>
        </div>
        <div class="relative h-[270px]  rounded-xl w-full">
          <QuickActions4 class="top-0 left-0 w-full object-cover" />
          <div class="z-[99]  absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl">
            <p class="text-xl font-semibold mb-2">Customize Interface.</p>
            <p class="text-gray-500 text-sm">
              Tailor the interface to your preferences.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="w-full justify-between lg:flex items-start gap-10 mt-16">
      <div class="bg-slate-50  w-full lg:w-[50%]    rounded-lg p-2 ">
        <div class="flex items-center justify-between w-full py-3 md:p-3">
          <div class=" flex items-center gap-3 ">
            <div class=" p-2 rounded-md border border-solid  bg-white md:text-[25px]">
              <Icon icon="lucide:workflow" />
            </div>
            <h1 class=" font-semibold text-sm md:text-lg">Assigned Tasks Status</h1>
          </div>
        </div>
        <div class=" overflow-x-auto ">
          <div class=" bg-white rounded-xl p-5 max-h-[500px] h-[400px] overflow-y-auto hide_scroll min-w-max  w-full ">
            <div v-if="props.created_task.data && props.created_task.data.length > 0">
              <div v-for="(item, index) in props.created_task.data" :key="index" class=" mb-3 flex items-center justify-between w-full">
                <div class=" flex gap-4 items-start">
                  <div class="  ">
                    {{ index + 1 }}
                  </div>
                  <div>
                    <h1 class=" text-sm md:text-base font-semibold">{{ item.title }}</h1>
                    <div class=" md:flex items-center gap-2 mt-3">
                      <p class="  bg-slate-50 text-gray-500 text-sm w-fit px-3 p-1.5 rounded-2xl">{{ item.due_date }}</p>
                      <div class=" flex items-center gap-2 mt-3 md:mt-0">
                        <p class=" bg-slate-50 text-gray-500 text-sm w-fit px-3 p-1.5 rounded-2xl" :style="{
              'color':
                item.status === 'completed'
                  ? '#0D6E1A'
                  : item.status === 'in progress'
                    ? '#FB6514'
                    : item.status === 'under review'
                      ? '#F79009'
                      : item.status === 'uncompleted'|| item.status == 'pending' ? '#F04438' : 'black',
            }">• {{ item.status }}</p>
                        <p class=" bg-slate-50 text-gray-500 text-sm w-fit px-3 p-1.5 rounded-2xl" :style="{
              'color':
                item.priority === '3'
                  ? '#FF3737'
                  : item.priority === '2'
                    ? '#FFBC53'
                    : item.priority === '1'
                      ? '#F67E5A'
                      : 'black',
            }">• {{ item.priority==3? "High": (item.priority==2)?'Medium' : 'Low' }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class=" flex  items-center gap-2">
                  <button>
                    <Link :href="'/dashboard/view-task/'+item.id">
                      <Icon icon="ph:eye" width="25" @click="viewCertificateModal = true" />
                    </Link>
                  </button>
                </div>
              </div>
            </div>
            <div class=" flex flex-col h-full items-center justify-center " v-else>
              <img src="../../assets/images/empty-task.svg" alt="" class=" w-28">
              <h1 class=" font-semibold text-gray-900  text-lg">No Task Update</h1>
              <p class=" text-sm text-gray-600 w-[60%] text-center">View latest update to task you’ve assigned at a
                glance.</p>
              <button class=" px-5 py-2 border border-solid border-gray-300 rounded-lg mt-2 text-sm">Open
                Workflow</button>
            </div>
          </div>
        </div>
      </div>


      <div class="bg-slate-50  w-full lg:w-[50%] mt-5 lg:mt-0    rounded-lg p-2 ">
        <div class="flex items-center justify-between w-full py-3 md:p-3">
          <div class=" flex items-center gap-3 ">
            <div class=" p-2 rounded-md border border-solid  bg-white md:text-[25px]">
              <Icon icon="material-symbols-light:mail-outline" />
            </div>
            <h1 class=" font-semibold text-sm md:text-lg">Recent Mail</h1>
          </div>
          <div class=" flex items-center  gap-2 md:gap-5">
            <p class=" bg-slate-100 text-red-500 text-xs w-fit px-3 p-1.5 rounded-2xl ">• 1 New Message</p>

            <div class=" p-2 rounded-md border border-solid  bg-white md:text-[25px]">
              <Icon icon="akar-icons:link-out" />
            </div>
          </div>
        </div>
        <div class=" ">
          <div class="  bg-white rounded-xl p-5 max-h-[500px] h-[400px] overflow-y-auto   hide_scroll  ">
            <div v-if="props.mails && props.mails.length > 0">
              <div v-for="(item, index) in props.mails" :key="index"
                class=" mb-5 flex gap-5 items-start justify-between w-full">
                <div class=" flex items-center gap-5">
                  <img src="../../assets/images/linda.svg" alt="" class="w-12" />
                  <div>
                    <p class=" text-gray-500 text-sm">{{ item.sender }}</p>
                    <p class=" text-sm text-gray-800 font-semibold">{{ item.subject }}</p>
                    <p class=" text-xs text-gray-500" v-html="renderMessage(item.body)"></p>
                  </div>
                </div>
                <div class=" flex items-center gap-2">
                  <p class=" text-red-500  text-sm" v-if="item.new">New!</p>
                  <p class=" text-sm text-gray-600">{{ item.time }}</p>
                </div>
              </div>
            </div>
            <div class=" flex flex-col h-full items-center justify-center " v-else>
              <img src="../../assets/images/empty-mail.svg" alt="" class=" w-28">
              <h1 class=" font-semibold text-gray-900  mt-2 text-lg">No Recent Mails</h1>
              <p class=" text-sm text-gray-600 w-[55%] text-center">View latest inbox at a glance on your dashboard</p>
              <button class=" px-5 py-2 border border-solid border-gray-300 rounded-lg mt-2 text-sm">Open
                Workflow</button>
            </div>
          </div>
        </div>
      </div>


    </section>
    <div class="fixed right-10 bottom-20 cursor-pointer z-[9999999]">
      <div class="flex flex-col items-center justify-center">
        <img src="../../assets/images/popup.svg" alt="" />
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, defineProps } from "vue";
import Actions from "../../assets/Dashboard/Actions.vue";
import QuickActions1 from "../../assets/Dashboard/workflow.vue";
import QuickActions1_1 from "../../assets/Dashboard/QuickActions1_1.vue";
import QuickActions2 from "../../assets/Dashboard/mail.vue";
import QuickActions3 from "../../assets/Dashboard/pms.vue";
import QuickActions4 from "../../assets/Dashboard/interface.vue";
import CreateDepartmentDialog from "../../components/Modals/CreateDepartmentDialog.vue";

import {Link} from "@inertiajs/vue3"
const menu = ref();
const departmentalModal = ref(false);
const props = defineProps(['created_task', 'mails']);
const renderMessage = (message) => {
  const maxLength = 100;
  if (message.length > maxLength) {
    return `${message.substring(0, maxLength)}...`;
  } else {
    return message;
  }
};
const step = ref(1);
const items = ref([
  { label: "Edit", icon: "pi pi-pencil" },
  { label: "Delete", icon: "pi pi-trash" },
]);

const Tasks = ref([
  {
    title: 'Append Signature',
    status: 'Completed',
    date: 'Deadline: 2024-06-01',
    icon: "hugeicons:teaching",
    severity: 'Low'
  },
  {
    title: 'Sign papers and send over',
    status: 'Uncompleted',
    severity: 'High',
    date: 'Deadline: 2024-06-01',
    icon: "akar-icons:file"
  },
  {
    title: 'Sign papers and send over',
    status: 'Under Review',
    date: 'Deadline: 2024-06-01',
    severity: 'Medium',
    icon: "hugeicons:teaching"
  },
  {
    title: 'Sign papers and send over',
    status: 'In Progress',
    severity: 'Low',
    date: 'Deadline: 2024-06-01',
    icon: "akar-icons:file"
  },

])


const Mails = ref([
  {
    id: 1,
    sender: "Emily Johnson",
    email: "emily@ansel.com",
    subject: "Document Review ",
    time: '7:28pm',
    new: true,
    message:
      "<p> I hope this email finds you well. My name is [Your Name], and I'm reaching out to discuss a potential collaboration opportunity on a new project. Attached to this email, you'll find a proposal document titled \"Project Proposal Draft\" outlining the details of the project we discussed earlier. Please review the proposal at your earliest convenience and let me know your thoughts. If you have any questions or suggestions, feel free to reach out to me directly. <br> Looking forward to your feedback. </p> </br> <h1>Best regards, <br> [Your Name]</h1>",
    status: "All",
    date: "Jan 20",
    Attached: "Payment Contract",
    icon: "vscode-icons:file-type-word",
  },
  {
    id: 2,
    sender: "Emily Johnson",
    email: "emily@ansel.com",
    subject: "Document Review ",
    new: false,
    time: '7:28pm',
    message:
      "<p> I hope this email finds you well. My name is [Your Name], and I'm reaching out to discuss a potential collaboration opportunity on a new project. Attached to this email, you'll find a proposal document titled \"Project Proposal Draft\" outlining the details of the project we discussed earlier. Please review the proposal at your earliest convenience and let me know your thoughts. If you have any questions or suggestions, feel free to reach out to me directly. <br> Looking forward to your feedback. </p> </br> <h1>Best regards, <br> [Your Name]</h1>",
    status: "All",
    date: "Jan 20",
  },
  {
    id: 3,
    sender: "Emily Johnson",
    new: false,
    email: "emily@ansel.com",
    time: '7:28pm',
    subject: "Document Review ",
    message:
      "<p> I hope this email finds you well. My name is [Your Name], and I'm reaching out to discuss a potential collaboration opportunity on a new project. Attached to this email, you'll find a proposal document titled \"Project Proposal Draft\" outlining the details of the project we discussed earlier. Please review the proposal at your earliest convenience and let me know your thoughts. If you have any questions or suggestions, feel free to reach out to me directly. <br> Looking forward to your feedback. </p> </br> <h1>Best regards, <br> [Your Name]</h1>",
    status: "All",
    date: "Jan 20",
  },
  {
    id: 4,
    sender: "Emily Johnson",
    new: false,
    email: "emily@ansel.com",
    time: '7:28pm',
    subject: "Document Review ",
    message:
      "<p> I hope this email finds you well. My name is [Your Name], and I'm reaching out to discuss a potential collaboration opportunity on a new project. Attached to this email, you'll find a proposal document titled \"Project Proposal Draft\" outlining the details of the project we discussed earlier. Please review the proposal at your earliest convenience and let me know your thoughts. If you have any questions or suggestions, feel free to reach out to me directly. <br> Looking forward to your feedback. </p> </br> <h1>Best regards, <br> [Your Name]</h1>",
    status: "Archive",
    date: "Jan 20",
    Attached: "Payment Contract",
    icon: "vscode-icons:file-type-word",
  },
  {
    id: 5,
    sender: "Emily Johnson",
    new: false,
    email: "emily@ansel.com",
    time: '7:28pm',
    subject: "Document Review ",
    message:
      "<p> I hope this email finds you well. My name is [Your Name], and I'm reaching out to discuss a potential collaboration opportunity on a new project. Attached to this email, you'll find a proposal document titled \"Project Proposal Draft\" outlining the details of the project we discussed earlier. Please review the proposal at your earliest convenience and let me know your thoughts. If you have any questions or suggestions, feel free to reach out to me directly. <br> Looking forward to your feedback. </p> </br> <h1>Best regards, <br> [Your Name]</h1>",
    status: "Archive",
    date: "Jan 20",
  },
])


const updateModalStep = (value) => {
  step.value = value;
};

const toggle = (event) => {
  menu.value.toggle(event);
};
</script>