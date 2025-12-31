<template>

   
    <main class="p-4 lg:p-7 relative">
       <section
        class="flex flex-col lg:flex-row items-start lg:items-center justify-between cursor-pointer"
      >
        <p class="text-2xl lg:text-3xl text-gray-800 font-semibold">
          Welcome Back ! 👋🏼
        </p>
        <!-- <div
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg text-sm lg:text-base py-2 px-3 lg:px-4 cursor-pointer mt-6 lg:mt-0"
        >
          <Icon icon="ion:add-outline" width="25" />
          <p>Invite Members</p>
        </div> -->
      </section> 
       <section
        class="w-full text-gray-800 bg-primary_bg border border-solid border-gray-300 rounded-xl mt-7 p-3 lg:p-6"
      >
        <div class="flex items-center gap-5">
          <Actions />
          <p class="text-xl lg:text-2xl font-medium">Quick Actions</p>
        </div>
        <div
          class="grid grid-cols-[270px_270px_270px_270px] lg:grid-cols-4 overflow-x-scroll lg:overflow-hidden mt-4 gap-5 lg:gap-10"
        >
          <div
            class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full"
          >
            <QuickActions1 class="absolute top-0 left-[6%]" />
            <QuickActions1_1 class="absolute top-20 z-[999] left-[12%]" />
            <div
              class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl"
            >
              <p class="text-xl font-semibold mb-2">Metadata Configuration</p>
              <p class="text-gray-500 text-sm">
                Configure metadata settings for document categorization
              </p>
            </div>
          </div>
          <div
            class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full"
          >
            <QuickActions2 class="absolute top-0 left-[6%]" />
            <div
              class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl"
            >
              <p class="text-xl font-semibold mb-2">Security Settings.</p>
              <p class="text-gray-500 text-sm">
                Manage security roles, compliance, and data privacy.
              </p>
            </div>
          </div>
          <div
            class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full"
          >
            <QuickActions3 class="absolute top-0 left-[6%]" />
            <div
              class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl"
            >
              <p class="text-xl font-semibold mb-2">Manage Users.</p>
              <p class="text-gray-500 text-sm">
                Administer user accounts, roles, and permissions.
              </p>
            </div>
          </div>
          <div
            class="relative h-[270px] border border-solid border-gray-200 rounded-xl w-full"
          >
            <QuickActions4 class="top-0 left-0 w-full object-cover" />
            <div
              class="z-[99] bg-white absolute bottom-0 h-[45%] pb-10 pt-7 px-5 rounded-xl"
            >
              <p class="text-xl font-semibold mb-2">Customize Interface.</p>
              <p class="text-gray-500 text-sm">
                Tailor the interface to match the organization's brand.
              </p>
            </div>
          </div>
        </div>
      </section> 
       <section class="mt-8">
         <div
          class="flex flex-col lg:flex-row items-start lg:items-center justify-between cursor-pointer p-2"
        >
          <p class="text-xl lg:text-2xl text-gray-800 font-semibold">
            Organisational Branches
          </p>
          <div
            @click="departmentalModal = true"
            class="flex gap-2 text-sm lg:text-base items-center mt-6 lg:mt-0 text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3 lg:px-4 cursor-pointer"
          >
            <Icon icon="ion:add-outline" width="25" />
            <p>Create Department</p>
          </div>
        </div> 
        <div
          class="w-full text-gray-800 border border-solid border-gray-300 rounded-xl mt-1 lg:p-6 p-3"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 lg:gap-6 text-xs lg:text-base">
              <p class="py-2 px-2 lg:px-3 bg-gray-50 rounded-lg font-semibold">
                All Departments
              </p>
              <p class="hover:bg-gray-50 rounded-lg py-2 px-2 lg:px-3">Pinned</p>
            </div>
            <div
              class="flex items-center text-xs lg:text-base gap-2 cursor-pointer"
            >
              <p>View All</p>
              <Icon icon="carbon:arrow-right" width="20" />
            </div>
          </div>
          <div class="flex flex-col lg:flex-row gap-4 mt-6">
            
            <div
              v-for="(item, index) in 4"
              :key="index"
              class="border border-gray-300 rounded-xl w-full max-h-[25rem] overflow-y-scroll hide_scroll"
            >
              <div
                class="flex pt-4 pb-2 items-center justify-between px-4 sticky top-0 bg-white z-[99] border-b"
              >
                <div class="flex items-center gap-3">
                  <div class="bg-gray-50 rounded-lg py-3 px-3">
                    <Icon icon="mdi:company" width="25" />
                  </div>
                  <div>
                    <p>Information Tech</p>
                    <p class="text-gray-500 text-xs">Last Updated a day ago</p>
                  </div>
                </div>
                <div class="ml-5 h-full border border-gray-300 rounded-lg">
                  <div
                    @click="toggle"
                    aria-haspopup="true"
                    aria-controls="overlay_menu"
                    class="h-full"
                  >
                    <Icon icon="majesticons:more-menu-vertical" width="30" />
                    <Menu
                      ref="menu"
                      id="overlay_menu"
                      :model="items"
                      :popup="true"
                    />
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 py-3 rounded-b-xl">
                <Tree
                  :value="nodes"
                  :pt="{
                    root: { class: 'bg-transparent p-0' },
                    node: { class: 'p-0' },
                    toggler: ({ props, context, state }) => ({
                      class: `ml-0`,
                    }),
                  }"
                >
                  <template #togglericon="slotProps">
                    <div
                      v-if="slotProps.node.type !== 'timeline'"
                      class="border border-solid border-gray-200 rounded-lg px-1 pb-1 pt-1 mx-2"
                    >
                      <Icon
                        v-if="slotProps.expanded"
                        icon="iconoir:nav-arrow-down"
                        width="20"
                      />
                      <Icon v-else icon="iconoir:nav-arrow-right" width="20" />
                    </div>
                    <div v-else class="flex text-[#DFDFDF]">
                      <p class="">|</p>
                      <p class="-mt-[6px] -ml-[2px]">__</p>
                    </div>
                  </template>
                  <template #default="slotProps">
                    <p>{{ slotProps.node.label }}</p>
                    <p class="text-gray-500 text-xs">
                      Last Updated {{ slotProps.node.timestamp }} ago
                    </p>
                  </template>
                  <template #timeline="slotProps">
                    <p class="text-sm">{{ slotProps.node.label }}</p>
                    <p class="text-gray-500 text-xs">
                      Last Updated {{ slotProps.node.timestamp }} ago
                    </p>
                  </template>
                </Tree>
              </div>
            </div> 
          </div>
        </div>
      </section> 
      <CreateDepartmentDialog
        v-model="departmentalModal"
        :step="step"
        @update:step="updateModalStep"
      />
      <div
        class="fixed right-10 bottom-20 cursor-pointer z-[9999999]"
        
      >
        <div class="flex flex-col items-center justify-center">
          <img src="../../assets/images/popup.svg" alt="" />
        </div>
      </div>
    </main>
  </template>
  
  <script setup>
  import { ref, onMounted } from "vue";
  import Actions from "../../assets/Dashboard/Actions.vue";
  import QuickActions1 from "../../assets/Dashboard/QuickActions1.vue";
  import QuickActions1_1 from "../../assets/Dashboard/QuickActions1_1.vue";
  import QuickActions2 from "../../assets/Dashboard/QuickActions2.vue";
  import QuickActions3 from "../../assets/Dashboard/QuickActions3.vue";
  import QuickActions4 from "../../assets/Dashboard/QuickActions4.vue";
  import CreateDepartmentDialog from "../../components/Modals/CreateDepartmentDialog.vue";

  const menu = ref();
  const departmentalModal = ref(false);
  const step = ref(1);
  const items = ref([
    { label: "Edit", icon: "pi pi-pencil" },
    { label: "Delete", icon: "pi pi-trash" },
  ]);
  
  const nodes = ref([
    {
      key: "0",
      label: "Cybersecurity",
      timestamp: "3 days",
      children: [
        {
          key: "0-1",
          label: "Public Relations",
          data: "Ship Folder",
          timestamp: "1 day",
          type: "timeline",
        },
        {
          key: "0-2",
          label: "Community Development",
          data: "Ship Folder",
          timestamp: "5 days",
          type: "timeline",
        },
        {
          key: "0-3",
          label: "Secretariat",
          data: "Ship Folder",
          timestamp: "2 weeks",
          type: "timeline",
        },
        {
          key: "0-4",
          label: "Management",
          data: "Ship Folder",
          timestamp: "1 month",
          type: "timeline",
        },
      ],
    },
    {
      key: "1",
      label: "Business Administration",
      timestamp: "2 days",
      children: [
        {
          key: "1-1",
          label: "Public Relations",
          data: "Ship Folder",
          timestamp: "2 days",
          type: "timeline",
        },
        {
          key: "1-2",
          label: "Community Development",
          data: "Ship Folder",
          timestamp: "5 days",
          type: "timeline",
        },
        {
          key: "1-3",
          label: "Secretariat",
          data: "Ship Folder",
          timestamp: "2 minutes",
          type: "timeline",
        },
        {
          key: "1-4",
          label: "Management",
          data: "Ship Folder",
          timestamp: "1 month",
          type: "timeline",
        },
      ],
    },
    {
      key: "2",
      label: "Proposals",
      timestamp: "5 days",
      children: [
        {
          key: "2-1",
          label: "Public Relations",
          data: "Ship Folder",
          timestamp: "1 day",
          type: "timeline",
        },
        {
          key: "2-2",
          label: "Community Development",
          data: "Ship Folder",
          timestamp: "5 days",
          type: "timeline",
        },
        {
          key: "2-3",
          label: "Secretariat",
          data: "Ship Folder",
          timestamp: "2 weeks",
          type: "timeline",
        },
        {
          key: "2-4",
          label: "Management",
          data: "Ship Folder",
          timestamp: "1 month",
          type: "timeline",
        },
      ],
    },
    {
      key: "3",
      label: "Management",
      timestamp: "3 Months",
      children: [
        {
          key: "3-1",
          label: "Public Relations",
          data: "Ship Folder",
          timestamp: "1 day",
          type: "timeline",
        },
        {
          key: "3-2",
          label: "Community Development",
          data: "Ship Folder",
          timestamp: "5 days",
          type: "timeline",
        },
        {
          key: "3-3",
          label: "Secretariat",
          data: "Ship Folder",
          timestamp: "2 weeks",
          type: "timeline",
        },
        {
          key: "3-4",
          label: "Management",
          data: "Ship Folder",
          timestamp: "1 month",
          type: "timeline",
        },
      ],
    },
    {
      key: "4",
      label: "Budgeting",
      timestamp: "3 Seconds",
      children: [
        {
          key: "4-1",
          label: "Public Relations",
          data: "Ship Folder",
          timestamp: "1 day",
          type: "timeline",
        },
        {
          key: "4-2",
          label: "Community Development",
          data: "Ship Folder",
          timestamp: "5 days",
          type: "timeline",
        },
        {
          key: "4-3",
          label: "Secretariat",
          data: "Ship Folder",
          timestamp: "2 weeks",
          type: "timeline",
        },
        {
          key: "4-4",
          label: "Management",
          data: "Ship Folder",
          timestamp: "1 month",
          type: "timeline",
        },
      ],
    },
    {
      key: "5",
      label: "Communications",
      timestamp: "3 Weeks",
      children: [
        {
          key: "5-1",
          label: "Public Relations",
          data: "Ship Folder",
          timestamp: "1 day",
          type: "timeline",
        },
        {
          key: "5-2",
          label: "Community Development",
          data: "Ship Folder",
          timestamp: "5 days",
          type: "timeline",
        },
        {
          key: "5-3",
          label: "Secretariat",
          data: "Ship Folder",
          timestamp: "2 weeks",
          type: "timeline",
        },
        {
          key: "5-4",
          label: "Management",
          data: "Ship Folder",
          timestamp: "1 month",
          type: "timeline",
        },
      ],
    },
  ]);
  
  const updateModalStep = (value) => {
    step.value = value;
  };
  
  const toggle = (event) => {
    menu.value.toggle(event);
  };

  </script>
  