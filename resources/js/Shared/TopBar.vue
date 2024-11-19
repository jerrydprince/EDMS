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
      <div  @click="toggle_notification = !toggle_notification" class=" cursor-pointer flex">
        <Icon icon="iconamoon:notification" width="25" class="" />
        <span v-if="props.total_task_notifications > 0" class="flex items-center justify-center p-1 w-[20px] h-[20px] rounded-full bg-red-400 text-xs text-white">{{ props.total_task_notifications }}</span>
      </div>
      <div v-if="toggle_notification"  ref="modalRef"
        class="  top-16 absolute max-h-[60vh] overflow-y-auto  w-[550px] shadow-lg z-[99999999] bg-white border border-gray-300 border-solid rounded-lg right-7">
        <Notification :notifications="props.notifications" @close="closeNotificationHandler" :total_task_notifications="total_task_notifications" />
      </div>

      <!-- mini profile section starts here -->
      <div class="ml-1 cursor-pointer">
        <div class="ml-1 w-10 h-10 overflow-hidden rounded-full" @click="Profile_option = !Profile_option">
          <img v-if="user.profile_image" :src="page.props.base+'/'+user.profile_image" />
          <img v-else src="https://i.pinimg.com/originals/6b/aa/98/6baa98cc1c3f4d76e989701746e322dd.png" />
          <!-- <p class=" text-primary absolute text-xs px-1.5 py-0.5 rounded-xl bg-white right-6 top-11 capitalize text-center">{{roles[0]}}</p> -->
        </div>

        <div
          class=" bg-red  mt-4 absolute w-[300px] shadow-lg z-[99999999] bg-white border border-gray-300 border-solid rounded-lg right-7"
          v-if="Profile_option">
          <div class=" flex items-center gap-3  p-4 border-b">
            <div class="ml-1 w-10 h-10 overflow-hidden rounded-full">
              <img v-if="user.profile_image" :src="page.props.base+'/'+user.profile_image" />
              <img v-else src="https://i.pinimg.com/originals/6b/aa/98/6baa98cc1c3f4d76e989701746e322dd.png" />
            </div>
            <div>
              <h1>{{ user.last_name+' '+user.first_name }}</h1>
              <div class=" flex items-center text-xs gap-2 text-gray-500">
                <p class=" ">{{user.email}} </p>
                <Icon icon="iconoir:copy" />
              </div>

            </div>
          </div>
          <Link href="/settings">
            <div class=" border-b">
              <div class=" flex items-center gap-2 text-[#344054] p-3 rounded-md m-3 hover:bg-gray-100 font-semibold "
                @click=" Profile_option = false">
                <Icon icon="lucide:user" width="25" />
                <p>Profile Settings </p>
              </div>
            </div>
          </Link>
          <!-- <router-link to="/settings/organisation" v-if="!roles.includes('data entry')">
            <div class=" border-b">
              <div class=" flex items-center gap-2 text-[#344054] p-3 rounded-md m-3 hover:bg-gray-100 font-semibold "
                @click=" Profile_option = false">
                <Icon icon="gg:organisation" width="25" />
                <p>Organization Settings </p>
              </div>
            </div>
          </router-link> -->
          <Link href="/dashboard/support">
            <div class=" border-b">
              <div class=" flex items-center gap-2 text-[#344054] p-3 rounded-md m-3 hover:bg-gray-100 font-semibold ">
                <Icon icon="system-uicons:support" width="25" />
                <p>Help & Support </p>
              </div>
            </div>
          </Link>
        
          <div class=" border-b" @click='logOut'>
            <div class=" flex items-center gap-2 p-3 rounded-md m-3 hover:bg-gray-100 font-semibold text-[#F04438] "
              @click=" Profile_option = false">
              <Icon icon="line-md:logout" width="25" />
              <p>Logout </p>
            </div>
          </div>
        
        </div>

      </div>
       <!-- mini profile section ends here -->

      <!-- <div class="ml-1">
        <Userplaceholder />
      </div> -->
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
import { ref, onMounted, computed } from "vue";
//mport { useRoute } from "vue-router";
import logo from "../assets/logo.vue";
import { Link, usePage } from '@inertiajs/vue3'
import { onClickOutside } from "@vueuse/core";
import Userplaceholder from "../assets/Userimageholder.vue";
import Notification from "../components/Notification.vue"
import { defineProps } from 'vue';

const props = defineProps(['toggleSearchFunction', 'notifications', "total_task_notifications"]);
const { toggleSearchFunction} = props;

const toggleSearch = () => {
  toggleSearchFunction();
};
const value1 = ref("");
const navVisible = ref(false);
const Profile_option = ref(false)
const page = usePage()

const modalRef = ref(null);
const toggle_notification = ref(false)
onClickOutside(modalRef, (event) => {
  (Profile_option.value = false, toggle_notification.value = false);
});

const user = computed(()=>page.props.user)
const roles = computed(()=>page.props.user_roles)

const closeNotificationHandler = ()=>{
  toggle_notification.value = false;
  Profile_option.value = false
}

//const route = useRoute();
const main = ref([
  {
    name: "Admin",
    path: "/dashboard",
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

const logOut = () => {
  axios.get('/log-out').then((response)=>{
    if(response){
      window.location.href = window.location.href
    }
  })
}

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
