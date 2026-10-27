<template>
    <main class="p-4 lg:p-7">
      <section class="flex flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
        <p class="text-2xl md:text-3xl text-gray-800 font-semibold">
          Setup File Type
        </p>
        <div @click="createFileType = true"
          class="mt-4 md:mt-0 flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-1 py-2 px-3 md:px-4 cursor-pointer">
          <Icon icon="ion:add-outline" width="25" />
          <p class="text-sm md:text-base">Add File Type</p>
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
              } p-1 pr-0 md:pr-3 md:p-3 pl-1 text-gray-500 -mb-[2px]`,
          }),
        }">
          <template #item="{ item, props }">
            <Link :href="item.link">
            <p v-bind="props.action" class="flex align-items-center gap-1 md:gap-2 text-sm md:text-base">
              <span class="font-normal text-nowrap">{{ item.label }}</span>
              <Icon :icon="item.icon" width="20" />
            </p>
            </Link>
          </template>
        </TabMenu>
        <!-- File Type -->
        <div class="mt-8 text-sm md:text-base">
          <DataTable v-model:selection="documentName" paginator :rows="10" :value="props.filetypes" dataKey="id" :pt="{
            table: {
              class: 'w-full border border-border_primary border-solid mb-6',
            },
          }" tableStyle="min-width: 60rem; border-radius: 30px;">
            <Column selectionMode="multiple" headerStyle="width: 3rem; color: #667085;"></Column>
            <Column field="name" header="Document Type" :pt="{
              bodyCell: { class: 'text-gray-700' },
            }" headerStyle="color: #667085; font-weight: 500;">
            </Column>
            <Column field="updated_at_formatted" header="Last Updated" :pt="{
              bodyCell: { class: 'text-gray-700' },
            }" headerStyle="color: #667085; font-weight: 500;">
            </Column>
            <Column field="actions" header="Actions" :pt="{
              bodyCell: { class: 'text-gray-700' },
            }" headerStyle="color: #667085; font-weight: 500;">
              <template #body="{ data }">
                <div class="flex items-center gap-3">
                  <div class="flex cursor-pointer">
                   
                  </div>
                  <div class="flex">
                    <div @click="toggle($event, data)" aria-haspopup="true" aria-controls="overlay_menu"
                      class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                      <div class="h-full">
                        <Icon icon="majesticons:more-menu-vertical" width="30" />
                      </div>
                      <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true" />
                    </div>
                  </div>
                </div>
              </template>
            </Column>
          </DataTable>
        </div>
      </section>
      <CreateFileTypeDialog v-model="createFileType"/>
     
      <updateFileTypeDialog v-model="updateFileType"
         :singleFileType="singleFileType" />
    </main>
  </template>
  
  <script setup>
  import { ref, defineProps, watch, reactive } from "vue";
  import CreateFileTypeDialog from "../../components/Modals/CreateFileTypeDialog.vue";
  import updateFileTypeDialog from "../../components/Modals/UpdateFileTypeDialog.vue";
  import { Link } from '@inertiajs/vue3'
  import { router } from '@inertiajs/vue3'
  
  const active = ref(2);
  const createFileType = ref(false);
  const updateFileType = ref(false)

  const props = defineProps(['filetypes'])
  const singleFileType = ref([]);
  
  const menu = ref();
  const form = reactive({
    id: ''
  })
  const menuItems = ref([
    { 
      label: "Edit", 
      icon: "pi pi-pencil" ,
      command: ()=>{
        updateFileType.value = true
      }
    },
    // { 
    //   label: "Duplicate", 
    //   icon: "pi pi-clone",
    //   command: ()=>{
    //     router.post('/dashboard/duplicate-document-type', form)
    //   }
    // },
    { 
      label: "Delete",
       icon: "pi pi-trash", 
       command: ()=>{
        router.get('/dashboard/delete-file-type/', form)
      }
      },
  ]);
  
  const toggle = (event, data) => {
    menu.value.toggle(event);
    singleFileType.value = data;
    form.id = singleFileType.value.id
  };
  
  
  const documentName = ref([]);
  
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
  
  
  const Actions = (data) => {
  
   console.log(data)
  }
  </script>
  
