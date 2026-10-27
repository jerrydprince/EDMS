<style>
  .custom-table{
    border: 1px solid #D0D5DD;
  }

  .custom-table tbody tr{
    height: 72px;
  }

  .btn-primary {
    border-radius: 8px;
    border: 1px solid var(--Gray-300, #D0D5DD);
    background: var(--White, #FFF);
    box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05);
}
.btn {
    display: flex;
    padding: 8px 14px;
    justify-content: center;
    align-items: center;
    gap: 8px;
}
</style>
<template>
  <main class="p-4 lg:p-7">
    <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
      <p class="text-2xl md:text-3xl text-gray-800 font-semibold">
        Manage Metadata Fields
      </p>
      <div class="mt-4 md:mt-0 flex items-center gap-3">
        <Link href="/dashboard/governance-hub"
          class="flex gap-2 items-center bg-indigo-50 border border-indigo-200 text-indigo-700 font-semibold rounded-lg py-2 px-3 md:px-4 shadow-2xs hover:bg-indigo-100 transition-all cursor-pointer">
          <Icon icon="lucide:network" width="20" class="text-indigo-600" />
          <p class="text-sm md:text-base">Governance Hub</p>
        </Link>
        <div @click="createMetadataDialog"
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-3 md:px-4 cursor-pointer">
          <Icon icon="ion:add-outline" width="25" />
          <p class="text-sm md:text-base">Create Custom Metadata</p>
        </div>
      </div>
    </section>
    <section class="mt-4">
      <TabMenu v-model:activeIndex="active" :model="items" :pt="{
        root: { class: 'text-primary' },
        inkbar: { class: 'text-primary' },
        action: ({ props, context, state }) => ({
          class: `${context.index == state.d_activeIndex
              ? 'text-primary border-b-2 border-solid border-primary'
              : ''
            } p-1 md:p-3 pl-1 text-gray-500 -mb-[2px]`,
        }),
      }">
        <template #item="{ item, props }">
          <Link :href="item.link">
          <p v-bind="props.action" class="flex align-items-center gap-1 md:gap-2 text-sm md:text-base">
            <span class="font-normal">{{ item.label }}</span>
            <Icon :icon="item.icon" width="20" />
          </p>
          </Link>
        </template>
      </TabMenu>
      <!-- Metadata -->


      <div class="mt-8 text-sm md:text-base">

        <DataTable v-model:selection="selectedMetaData" paginator :rows="10" :value="metadatas" dataKey="id" :pt="{
          table: {
            class: 'w-full border border-border_primary border-solid mb-6',
          },
        }" tableStyle="min-width: 60rem; border-radius: 30px;">
          <Column selectionMode="multiple" headerStyle="width: 3rem; color: #667085;"></Column>
          <Column field="name" header="Metadata Field" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="string" header="String" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column header="Inherent Property" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="{ data }">
              <div class="flex justify-start items-center">
                <p :class="`px-7 py-2 rounded-full w-auto ${data.is_default === 0
                    ? 'text-purple_text bg-purple_light'
                    : 'text-success_text bg-success'
                  }`">
                  {{ data.is_default == 0 ? 'Variable' : 'Default' }}
                </p>
              </div>
            </template>
          </Column>
          <Column field="actions" header="Actions" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="{ data }">
              <div @click="toggle($event,data)" aria-haspopup="true" aria-controls="overlay_menu" class="flex">
                <div class="flex items-center pr-4 font-normal h-full border border-gray-300 rounded-lg">
                  <div class="">
                    <div class="h-full">
                      <Icon icon="majesticons:more-menu-vertical" width="30" />
                    </div>
                    <Menu ref="menu" id="overlay_menu" :model="menuItems" class="cursor-default" :popup="true">
                      <template #item="{ item, props }">
                        <div v-if="data.category !== item.value" class="flex gap-2 px-3 py-2 items-center cursor-default">
                          <i :class="item.icon" />
                          <p>{{ item.label }}</p>
                        </div>
                      </template>
                    </Menu>
                  </div>
                  <p>More</p>
                </div>
              </div>
            </template>
          </Column>
        </DataTable>



<!-- <div class="relative  sm:rounded-lg custom-table">
    <table class="w-full text-sm text-left rtl:text-right ">
        <thead class="bg-gray-100 ">
            <tr>
                <th scope="col" class="p-4">
                    <div class="flex items-center">
                        <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-all-search" class="sr-only">checkbox</label>
                    </div>
                </th>
                <th scope="col" class="px-6 py-3 font-normal">
                    Metadata field
                </th>
                <th scope="col" class="px-6 py-3 font-normal">
                    String
                </th>
                <th scope="col" class="px-6 py-3 font-normal">
                    Inherent Property
                </th>
                <th scope="col" class="px-6 py-3 font-normal">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            v-for="(data,ind) in metadatas" :key="ind">
                <td class="w-4 p-4">
                    <div class="flex items-center">
                        <input id="checkbox-table-search-1" type="checkbox" class="w-4 h-4  bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                    </div>
                </td>
                <td class="px-6 py-4 ">
                    {{data.name}}
                </td>
                <td class="px-6 py-4">
                    {{data.string}}
                </td>
                <td class="px-6 py-4">
                    {{data.is_default}}
                </td>
                <td class="px-6 py-4">
                    <Menu as="div" class="relative inline-block text-left">
                      <div>
                        <MenuButton class="inline-flex w-full justify-center gap-x-1.5 rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                          <svg width="4" height="16" viewBox="0 0 4 16" fill="none" xmlns="http://www.w3.org/2000/svg" >
                          <path d="M1.99999 8.8335C2.46023 8.8335 2.83332 8.4604 2.83332 8.00016C2.83332 7.53993 2.46023 7.16683 1.99999 7.16683C1.53975 7.16683 1.16666 7.53993 1.16666 8.00016C1.16666 8.4604 1.53975 8.8335 1.99999 8.8335Z" stroke="#344054" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M1.99999 3.00016C2.46023 3.00016 2.83332 2.62707 2.83332 2.16683C2.83332 1.70659 2.46023 1.3335 1.99999 1.3335C1.53975 1.3335 1.16666 1.70659 1.16666 2.16683C1.16666 2.62707 1.53975 3.00016 1.99999 3.00016Z" stroke="#344054" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M1.99999 14.6668C2.46023 14.6668 2.83332 14.2937 2.83332 13.8335C2.83332 13.3733 2.46023 13.0002 1.99999 13.0002C1.53975 13.0002 1.16666 13.3733 1.16666 13.8335C1.16666 14.2937 1.53975 14.6668 1.99999 14.6668Z" stroke="#344054" stroke-width="1.67" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          More
                        </MenuButton>
                      </div>

                      <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                        <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                          <div class="py-1">
                            <MenuItem v-slot="{ active }">
                              <a href="#" @click="updateModal(data)" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Edit</a>
                            </MenuItem>
                            <MenuItem v-slot="{ active }">
                              <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Delete</a>
                            </MenuItem>
                            <MenuItem v-slot="{ active }">
                              <a href="#" :class="[active ? 'bg-gray-100 text-gray-900' : 'text-gray-700', 'block px-4 py-2 text-sm']">Make default</a>
                            </MenuItem>
                            
                          </div>
                        </MenuItems>
                      </transition>
                    </Menu>

                    
                </td>


            </tr>
           
        </tbody>
    </table>
</div> -->


      </div>
    </section>
    

    <CreateMetadataDialog v-model="createMetadata" />
    <UpdateMetadataDialog v-model="updateMetadata" :data="singleMetadata"/>
  </main>
</template>
<script setup>
import Swal from 'sweetalert2';

import { ref, defineProps } from "vue";
import CreateMetadataDialog from "../../components/Modals/CreateMetadataDialog.vue";
import UpdateMetadataDialog from "../../components/Modals/UpdateMetadataDialog.vue";
import { router } from '@inertiajs/vue3'

import { Link } from '@inertiajs/vue3'
import { formToJSON } from "axios";
//import Menu from 'primevue/menu';
//import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
//import { ChevronDownIcon } from '@heroicons/vue/20/solid'

/**
 * ***********************************************data***********************************
 */
const active = ref(0);
const createMetadata = ref(false);
const updateMetadata = ref(false)
const menu = ref();
const singleMetadata = ref([])
const selectedMetaData = ref()
const menuItems = ref([
  { 
    label: "Edit", 
    icon: "pi pi-pencil", 
    value: "edit",
    command: ()=>{
      updateMetadata.value = true;
    } 
  },
  { 
    label: "Delete",
     icon: "pi pi-trash",
      value: "delete",
      command: ()=>{
        Swal.fire({
          title: 'Confirm',
          text: "Do you want to delete this?",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes',
          cancelButtonText: 'No'
        }).then((result) => {
          if (result.isConfirmed) {
          router.post('/dashboard/delete-metadata',singleMetadata.value);
        }
        });
      } 
  },
  { 
    label: singleMetadata.value.is_default==1?"Make Variable":"Make Default", 
    icon: "pi pi-info-circle", 
    value: "Variable" ,
    command: ()=>{
      router.post('/dashboard/set-default-metadata',{id: singleMetadata.value.id}, {page:()=>{
       router.reload({only:['metadatas']})
      }});
    }
  },
  // { 
  //   label: "Make Default", 
  //   icon: "pi pi-info-circle", 
  //   value: "Default",
  //   command: (val)=>{
  //     console.log()
  //     alert(' this guy just clicked me')
  //   }
  // },
]);

/**
 * ***********************************************Props***********************************
 */

const props = defineProps(["metadatas"]);

/**
 *
 * *****************************************functions ************************************
 */
const toggle = (event,data) => {
  menu.value.toggle(event);
  singleMetadata.value = data;
};


//const selectedProduct = ref([]);
const metaKey = ref(true);

const items = ref([
  {
    label: "Metadata",
    icon: "",
    link: "/dashboard/metadata-setup",
  },
  {
    label: "Document Type",
    icon: "material-symbols-light:info-outline",
    link: "/dashboard/setup-document-type",
  },
  {
    label: "File Type",
    icon: "",
    link: "/dashboard/file-type",
  }
]);

//table actions
const Actions = (val) => {
  // if (val == 'edit') {
  //   updateMetadata.value = true;
  // }
  // if(val == 'delete'){
  //   if (window.confirm("Do you want to delete this?")) {
  //     router.post('/dashboard/delete-metadata',singleMetadata.value);
  //   }
  // }
}

//create metadata
const createMetadataDialog = () => {
  singleMetadata.value = []
  createMetadata.value = true;
}

</script>
