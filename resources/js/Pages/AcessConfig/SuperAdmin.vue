<template>
  <main class="p-10">
    <div v-for="(item, index) in admin" :key="index">
      <div class="mb-10">
        <h1 class="mb-5 text-lg font-light">{{ item.title }}</h1>
        <div class="border border-gray-300 rounded-md px-5 pt-5">
          <div v-for="(submenu, index) in item.Option" :key="index">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h1 class="text-[#475467] text-lg">{{ submenu.head }}</h1>
                <p class="font-light text-base">{{ submenu.desc }}</p>
              </div>
              <label class="switch">
                <input type="checkbox"  v-model="toggle" :value="submenu.id" />
                <span class="slider round"></span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref } from "vue";
const toggle = ref([])

const admin = [
  {
    title: " User Management",
    Option: [
      {
        id: 1000,
        head: "User Account Management",
        desc: "Create, modify, and manage user accounts and associated details.",
      },
      {
        id: 1002,
        head: "Assign Roles and Permissions",
        desc: "Assign different roles and corresponding permissions to users.",
      },
    ],
  },
  {
    title: " Notifications",
    Option: [
      {
        id: 1003,
        head: "System Alerts",
        desc: "Enable or disable the receipt of system-wide alerts and updates.",
      },
      {
        id: 1004,
        head: "Configure Notifications",
        desc: "Manage your notification preferences and settings.",
      },
    ],
  },
  {
    title: "Document Management",
    Option: [
      {
        id: 1005,
        head: "Full Document Access",
        desc: "Grants unrestricted access to all documents and folders",
        toggled: false,
      },
      {
        id: 1006,
        head: "Configure Workflows",
        desc: "Set up and manage workflows for efficient document processing.",
        toggled: false,
      },

      {
        id: 1007,
        head: "Manage Document Access Permissions",
        desc: "Control who can view and edit specific documents.",
        toggled: false,
      },
    ],
  },
  {
    title: "System Configuration",
    Option: [
      {
        id: 1008,
        head: "Backup and Recovery",
        desc: "Perform tasks related to system backup and disaster recovery.",
        toggled: false,
      },
      {
        id: 1009,
        head: "Access System Logs",
        desc: "View logs containing information about system activities",
        toggled: false,
      },
    ],
  },
  {
    title: "Audit and Logging",
    Option: [
      {
        id: 1010,
        head: "Monitor User Activity",
        desc: "Keep track of user activities through detailed logs.",
        toggled: false,
      },
      {
        id: 1011,
        head: "Assign Roles and Permissions",
        desc: "Assign different roles and corresponding permissions to users.",
        toggled: false,
      },
    ],
  },
  {
    title: "Performance Optimization",
    Option: [
      {
        id: 1012,
        head: "System Performance",
        desc: "Track and optimize various metrics for improved system performance.",
        toggled: false,
      },
    ],
  },
];
</script>

<style scoped>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #0D6E1A;
}

input:focus + .slider {
  box-shadow: 0 0 1px #0D6E1A;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>