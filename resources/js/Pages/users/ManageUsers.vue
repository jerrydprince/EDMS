<template>
  <main class="">
    <div class="flex items-center justify-between pt-5 px-7">
      <div class="flex items-center gap-5">
        <div class="border rounded-full p-3">
          <Icon icon="mynaui:users" width="30" />
        </div>
        <div>
          <h1 class="text-3xl text-[#1D2939]">Team Management</h1>
          <p class="text-gray-500">
            Manage your user account permissions here.
          </p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <div class="flex">
          <p class="text-[#2F68C5]" v-if="props.notifications">Account Requests <span class=" bg-red-600 text-white h-[22px] w-[22px] inline-block text-center rounded-full align-middle items-center text-sm">{{ props.notifications.length }}</span></p>
        </div>
        <button class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
          <Icon icon="material-symbols:download" width="20" />
          <p>Download CSV</p>
        </button>
        <Link href="/admin/add-user">
        <button class="flex gap-2 items-center text-white bg-[#2F68C5] rounded-lg py-2 px-4">
          <Icon icon="material-symbols:add" width="20" />
          <p>Add User</p>
        </button>
        </Link>
      </div>
    </div>
    <Divider class=" " />

    <section class="lg:flex items-center justify-between px-7">
      <div class="flex items-center justify-between lg:w-[40%] text-sm">
        <div class="bg-gray-100 flex items-center rounded-md p-1">
          <button class="rounded-lg py-1 px-4" @click="setActive(1)" :class="{ activeButton: buttonToggle === 1 }">
            <p>All</p>
          </button>
          <div>
            <button class="flex gap-2 items-center text-gray-400 rounded-lg py-1 px-4"
              :class="{ activeButton: buttonToggle === 2 }" @click="toggle" aria-haspopup="true"
              aria-controls="overlay_menu">
              <p>Account</p>
              <Icon icon="flowbite:angle-down-outline" class="" />
            </button>
            <TieredMenu ref="menu" id="overlay_tmenu" :model="items" popup style="width: 200px">
              <template #item="{ item, props }">
                <a v-ripple class="flex align-items-center gap-2 px-[15px] py-[8px]" v-bind="props.action"
                  @click="handleFilterOption(item.label)">
                  <div v-if="item.icon">
                    <Icon :icon="item.icon" />
                  </div>
                  <span class="">{{ item.label }}</span>
                </a>
              </template>
            </TieredMenu>
          </div>

          <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4" @click="setActive(3)"
            :class="{ activeButton: buttonToggle === 3 }">
            <p>Sort Branch</p>
          </button>
        </div>
      </div>

      <div class="flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
        <i class="pi pi-search text-gray-300" style="font-size: 1rem"></i>
        <Input type="text" v-model="value" placeholder="Search Users" class="w-96 border-0 outline-none text-[#98A2B3]" />
      </div>
    </section>

    <Divider class=" " />

    <div v-if="buttonToggle === 1">
      <div v-for="(item, index) in data" :key="index" class="mb-5">
        <div class="flex items-start justify-between pl-7 pr-14">
          <div class="w-[25%]">
            <h1 class="text-xl font-semibold text-[#475467]">
              {{ item.name }}
            </h1>
            <p class="text-[#667085] mt-2">{{ item.description }}</p>
          </div>
          <div class=" ">
            <DataTable v-model:selection="selectedProduct" :rows="4" :value="item.table.slice(0, 4)" dataKey="id" :pt="{
              table: {
                class: ' border border-border_primary border-solid mb-6 mt-2',
              },
            }" tableStyle="min-width: 50rem; border-radius: 100px;" style="width: ">
              <Column selectionMode="multiple" headerStyle="width: 1rem; color: #667085;"></Column>
              <Column field="name" header="Name" :pt="{
                bodyCell: { class: 'text-gray-700' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 300px">
                <template #body="slotProps">
                  <div class="font-medium">
                    <p>{{ slotProps.data.name }}</p>

                    <p class="text-[#667085]">{{ slotProps.data.email }}</p>
                  </div>
                </template>
              </Column>
              <Column field="date" header="Date Added" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
              </Column>
              <Column field="active" header="Last Active" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
              </Column>
              <Column field="" header="Actions" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;">
                <template #body="slotProps">
                  <div class="flex gap-3 items-center">
                    <div class="p-1 font-semibold border rounded-md border-gray-300">
                      <Icon icon="mingcute:pencil-2-line" width="25" />
                    </div>
                    <div @click="showDeleteDialog(slotProps.data.id)" class=" cursor-pointer">
                      <Icon icon="mingcute:delete-2-line" width="25" />
                    </div>
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
        <div class="flex justify-end px-20">
          <Link href="/user-details">
          <button class="flex items-center gap-2 text-[#2F68C5]">
            <p>View All</p>
            <Icon icon="flowbite:angle-right-outline" />
          </button>
          </Link>
        </div>
      </div>
    </div>

    <div v-if="buttonToggle === 2">
      <div v-for="(item, index) in filteredItems" :key="index" class="mb-5">
        <div class="flex items-start justify-between pl-7 pr-14">
          <div class="w-[25%]">
            <h1 class="text-xl font-semibold text-[#475467]">
              {{ item.name }}
            </h1>
            <p class="text-[#667085] mt-2">{{ item.description }}</p>
          </div>
          <div class=" ">
            <DataTable v-model:selection="selectedProduct" :rows="4" :value="item.table.slice(0, 4)" dataKey="id" :pt="{
              table: {
                class: ' border border-border_primary border-solid mb-6 mt-2',
              },
            }" tableStyle="min-width: 50rem; border-radius: 100px;" style="width: ">
              <Column selectionMode="multiple" headerStyle="width: 1rem; color: #667085;"></Column>
              <Column field="name" header="Name" :pt="{
                bodyCell: { class: 'text-gray-700' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 300px">
                <template #body="slotProps">
                  <div class="font-medium">
                    <p>{{ slotProps.data.name }}</p>

                    <p class="text-[#667085]">{{ slotProps.data.email }}</p>
                  </div>
                </template>
              </Column>
              <Column field="date" header="Date Added" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
              </Column>
              <Column field="active" header="Last Active" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
              </Column>
              <Column field="" header="Actions" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;">
                <template #body="">
                  <div class="flex gap-3 items-center">
                    <div class="p-1 font-semibold border rounded-md border-gray-300">
                      <Icon icon="mingcute:pencil-2-line" width="25" />
                    </div>
                    <div @click="showDeleteDialog(slotProps.data.id)" class=" cursor-pointer">
                      <Icon icon="mingcute:delete-2-line" width="25" />
                    </div>
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
        <div class="flex justify-end px-20">
          <Link href="/user-details">
          <button class="flex items-center gap-2 text-[#2F68C5]">
            <p>View All</p>
            <Icon icon="flowbite:angle-right-outline" />
          </button>
          </Link>
        </div>
      </div>
    </div>

    <div v-if="buttonToggle === 3">
      <div v-for="(item, index) in filteredItems" :key="index" class="mb-5">
        <div class="  pl-7 pr-14">
          <div class="w-[50%]">
            <h1 class="text-xl font-semibold text-[#475467]">
              {{ item.name }}
            </h1>
            <p class="text-[#667085] mt-2">{{ item.description }}</p>
          </div>
          <div class=" mt-5">
            <DataTable v-model:selection="selectedProduct" :rows="4" :value="item.table.slice(0, 4)" dataKey="id" :pt="{
              table: {
                class: ' border border-border_primary border-solid mb-6 mt-2',
              },
            }" tableStyle="min-width: 70rem; border-radius: 100px;" style="width: ">
              <Column selectionMode="multiple" headerStyle="width: 1rem; color: #667085;"></Column>
              <Column field="name" header="Name" :pt="{
                bodyCell: { class: 'text-gray-700' },
              }" headerStyle="color: #667085; font-weight: 500;">
                <template #body="slotProps">
                  <div class="font-medium">
                    <p>{{ slotProps.data.name }}</p>

                    <p class="text-[#667085]">{{ slotProps.data.email }}</p>
                  </div>
                </template>
              </Column>
              <Column field="date" header="Date Added" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 150px">
              </Column>
              <Column field="active" header="Last Active" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;">
              </Column>
              <Column field="" header="Actions" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;">
                <template #body="slotProps">
                  <div class="flex gap-3 items-center">
                    <div class="p-1 font-semibold border rounded-md border-gray-300">
                      <Icon icon="mingcute:pencil-2-line" width="25" />
                    </div>
                    <div @click="showDeleteDialog(slotProps.data.id)" class=" cursor-pointer">
                      <Icon icon="mingcute:delete-2-line" width="25" />
                    </div>
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </div>
        <div class="flex justify-end px-40">
          <Link href="/user-details">
          <button class="flex items-center gap-2 text-[#2F68C5]">
            <p>View All</p>
            <Icon icon="flowbite:angle-right-outline" />
          </button>
          </Link>
        </div>
      </div>
    </div>
  </main>
  <DeleteUsers v-if="deleteModal" @confirmed="handleDelete" @cancelled="cancelDelete" />
</template>

<script setup>
import { ref, watch, defineProps } from "vue";
import DeleteUsers from "../../components/Modals/DeleteUsers.vue";
import { Link } from '@inertiajs/vue3'

const deleteModal = ref(false)
const buttonToggle = ref(1);
const selectedProduct = ref();
const searchQuery = ref('');
const itemIdToDelete = ref(null);
const props = defineProps(['notifications']);

const setActive = (buttonNumber) => {
  buttonToggle.value = buttonNumber;
  if (buttonToggle.value > 2 || buttonToggle.value < 2) {
    searchQuery.value = ''
  }
};
const data = [
  {
    name: "Super Admin",
    description:
      "This access level holds unparalleled authority, overseeing the entire system without restrictions",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },
  {
    name: "Administrator",
    description:
      "Focuses on document management. Configures workflows, archiving policies, and document-specific settings.",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },

  {
    name: "Records Manager",
    description:
      "Specialized in records management, ensuring compliance with government regulations.",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },
  {
    name: "Unit Head",
    description:
      "Manages activities of a specific unit within a division, accesses unit files and shares documents with the division head and other unit heads.",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },
  {
    name: "Super Users",
    description:
      "Manages cross-department activities, oversees all files/documents, delegates tasks, tracks file activities",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },

  {
    name: "Directors",
    description:
      "Manages departmental functions, shares files/documents with other directors, accesses department files",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },
  {
    name: "Division Heads",
    description:
      "Manages a specific division's activities, accesses division files and shares documents with the director and other division heads.",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },
  {
    name: "Data Entry Personnel",
    description:
      "Limited access for entering data into the system, including tasks like scanning and indexing documents.",
    table: [
      {
        id: "1001",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1002",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1003",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1004",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1005",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
      {
        id: "1006",
        email: "dammyhills@ansel.com",
        name: "Dammy Hills",
        active: "Mar 2023",
        date: "16 Nov 23",
      },
    ],
  },
];

const showDeleteDialog = (itemId) => {
  itemIdToDelete.value = itemId;
  deleteModal.value = !deleteModal.value
};

const handleDelete = () => {
  data.forEach(category => {
    const indexToDelete = category.table.findIndex(item => item.id === itemIdToDelete.value);
    if (indexToDelete !== -1) {
      category.table.splice(indexToDelete, 1);
    }
  });
  itemIdToDelete.value = null;
  deleteModal.value = !deleteModal.value
};

const cancelDelete = () => {
  deleteModal.value = !deleteModal.value
}
const filteredItems = ref([...data]);
const handleFilterOption = (label) => {
  searchQuery.value = label;

};

watch(searchQuery, (newValue) => {
  filteredItems.value = data.filter(item => {
    return item.name.toLowerCase().includes(newValue.toLowerCase());
  });
  if (buttonToggle.value > 2) {

    searchQuery.value = ''
  }

});
const menu = ref(null);
const items = ref([
  {
    label: "Filter",
    icon: "octicon:filter-16",
  },
  {
    label: "Super Admin",
  },
  {
    label: "Administrator",
  },
  {
    label: "Records Manager",
  },
  {
    label: "Super User",
  },
  {
    label: "Director",
  },
  ,
  {
    label: "Division Head",
  },
  {
    label: "Unit Head",
  },
  {
    label: "Data Entry Personnel",
  },
]);



const toggle = (event) => {
  menu.value.toggle(event);

  console.log(queueMicrotask);
  buttonToggle.value = 2;
};
</script>

<style scoped>
.activeButton {
  background-color: white;
  border-radius: 5px;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>