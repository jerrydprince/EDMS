<template>
  <main class="h-[50px] lg:h-[67px]  py-1 lg:py-3 border-b border-solid border-gray-200 flex items-center lg:justify-end">
    <div class="hidden xl:flex text-xl font-semibold items-center justify-end gap-4 px-7">
      <span class="text-gray-400 relative mr-1" @click="toggleSearch">
        <Icon icon="iconamoon:search-light" width="20"
          class="pi pi-search absolute top-2/4 -mt-[10.2px] left-3 text-surface-400 dark:text-surface-600" />
        <InputText v-model="value1" placeholder="Search for documents, folders & branches"
          class="pl-10 w-[28rem] h-10 bg-transparent font-light" size="small" />
      </span>
      <div class="flex gap-3 items-center bg-gray-200 h-full py-1 px-2 rounded-full mr-1">
        <Icon icon="heroicons:sun-20-solid" width="30" class="bg-white rounded-full p-1" />
        <Icon class="text-gray-500" icon="ion:moon-sharp" width="20" />
      </div>
      <Icon icon="iconamoon:notification" width="25" />
      <div class="ml-1">
        <Userplaceholder />
      </div>
    </div>
    <div class="flex xl:hidden text-xl font-semibold items-center justify-between px-4 w-full">
      <div class="flex items-center gap-4">
        <Icon icon="quill:hamburger" width="30" @click="navVisible = true" />
        <div class="text-lg font-semibold flex items-center gap-4">
          <p>Ansel LTD</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <Icon icon="mynaui:search" width="25" />
        <Icon icon="iconamoon:notification" width="20" />
        <div class="ml-1 w-6 h-6 rounded-full">
          <img src="https://i.pinimg.com/originals/6b/aa/98/6baa98cc1c3f4d76e989701746e322dd.png" />
        </div>
      </div>
    </div>
  </main>
  <Sidebar v-model:visible="navVisible" >
    <section class="hide_scroll sticky top-0 h-screen pr-5 overflow-y-scroll">
      <div class="text-xl font-semibold flex items-center gap-4 border-b border-dashed border-gray-200 pb-3">
        <logo />
        <p>Ansel LTD</p>
      </div>
      <div class="border-b border-dashed pb-4">
        <p class="text-gray-500 pl-4 mt-4 mb-2">Main</p>
        <section>
          <div v-for="(item, index) in main" :key="index" class="mb-1">
            <Link :href="item.path" :class="item.path === item.path
                ? 'border bg-gray-25 text-primary'
                : 'hover:border-0'
              "
              class="px-4 py-3 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
              <Icon :icon="item.icon" width="25" />
              <p class="text-lg">{{ item.name }}</p>
            </Link>
          </div>
        </section>
      </div>
      <div class="border-b border-dashed pb-4">
        <p class="text-gray-500 pl-4 mt-4 mb-2">Admin</p>
        <section>
          <div v-for="(item, index) in admin" :key="index" class="mb-1">
            <Link :href="item.path" :class="item.path === item.path
                ? 'border bg-gray-25 text-primary'
                : 'hover:border-0'
              "
              class="px-4 py-3 flex gap-3 items-center text-gray-700 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
              <Icon :icon="item.icon" width="25" />
              <p class="text-lg">{{ item.name }}</p>
            </Link>
          </div>
        </section>
      </div>
      <div class="pb-4 mb-12">
        <div class="text-gray-500 pl-4 mt-4 mb-2 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Icon icon="ic:round-greater-than" width="20" />
            <p>Branches</p>
          </div>
          <Icon icon="line-md:grid-3-filled" width="20" />
        </div>
        <section>
          <div v-for="(item, index) in branches" :key="index" class="mb-1">
            <Link :href="item.path" :class="item.path === item.path ? 'border bg-gray-25 text-primary' : ''
              "
              class="pl-4 pr-1 py-3 flex items-center justify-between text-gray-700 hover:border-0 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
              <div class="flex items-center gap-3">
                <Icon :icon="item.icon" width="15" />
                <p class="text-lg">{{ item.name }}</p>
              </div>
              <Icon icon="ic:round-greater-than" width="20" />
            </Link>
          </div>
          <div class="mb-1">
            <div
              class="pl-4 pr-1 py-3 flex items-center justify-between text-gray-700 hover:border-0 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
              <div class="flex items-center gap-3">
                <Icon icon="mdi:company" width="20" />
                <p class="text-lg">Add Department</p>
              </div>
              <Icon icon="material-symbols:add-box-outline" width="25" />
            </div>
          </div>
        </section>
      </div>
      <div class="border-t border-dashed pt-2 pb-4">
        <section>
          <div class="mb-1">
            <Link to="/"
              class="px-4 py-3 flex gap-3 items-center text-gray-700 border-0 hover:border-0 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
              <Icon icon="system-uicons:support" width="30" />
              <p class="text-lg">Support</p>
            </Link>
          </div>
          <div class="mb-1">
            <Link to="/"
              class="px-4 py-3 flex gap-3 items-center text-gray-700 border-0 hover:border-0 border-solid border-gray-200 rounded-xl hover:bg-gray-25 hover:text-primary font-normal transition-all">
              <Icon icon="uiw:setting-o" width="25" />
              <p class="text-lg">Settings</p>
            </Link>
          </div>
        </section>
      </div>
    </section>
  </Sidebar>
</template>

<script setup>
import { ref } from "vue";
//mport { useRoute } from "vue-router";
import logo from "../assets/logo.vue";
import { Link } from '@inertiajs/vue3'
import Userplaceholder from "../assets/Userimageholder.vue";
import { defineProps } from 'vue';

const { toggleSearchFunction } = defineProps(['toggleSearchFunction']);

const toggleSearch = () => {
  toggleSearchFunction();
};
const value1 = ref("");
const navVisible = ref(false);


//const route = useRoute();
const main = ref([
  {
    name: "Admin",
    path: "/admin",
    icon: "lucide:home",
  },
  {
    name: "Analytics",
    path: "/analytics",
    icon: "carbon:analytics",
  },
  {
    name: "Inbox",
    path: "/inbox",
    icon: "octicon:inbox-24",
  },
]);

const admin = ref([
  {
    name: "Manage Users",
    path: "/manage-users",
    icon: "mynaui:users",
  },
  {
    name: "Branch Setup",
    path: "/branch-setup",
    icon: "solar:database-outline",
  },
  {
    name: "Access Config",
    path: "/access-config",
    icon: "mdi:cloud-key-outline",
  },
  {
    name: "Metadata Setup",
    path: "/metadata-setup",
    icon: "uiw:setting-o",
  },
  {
    name: "Audit Log",
    path: "/audit-log",
    icon: "material-symbols-light:layers-outline",
  },
  {
    name: "Recycle Bin",
    path: "/recycle-bin",
    icon: "ep:delete",
  },
]);

const branches = ref([
  {
    name: "Communication",
    path: "/communication",
    icon: "clarity:circle-line",
  },
  {
    name: "Tech Titans",
    path: "/tech-titans",
    icon: "clarity:circle-line",
  },
  {
    name: "Information",
    path: "/information",
    icon: "clarity:circle-line",
  },
  {
    name: "Management",
    path: "/management",
    icon: "clarity:circle-line",
  },
]);
</script>
