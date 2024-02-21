<template>
    <main class="">
        <div class=" px-5 lg:px-7 lg:flex items-center justify-between pt-5  ">
          <div class="flex items-center justify-between  lg:w-[75%]">
            <div class=" flex items-center gap-2">
                <div class=" cursor-pointer">
                    <i class="pi pi-arrow-left border p-2 rounded-md text-xs" @click="goBack" ></i>
                </div>
                <div class=" text-xl">
                    <p>Architecture Plan Folder</p>
                </div>
            </div>

            <button
            class="flex gap-2 items-center text-[#F04438]  hover:bg-[#FFFBFA] hover:border hover:border-[#FECDCA] rounded-lg py-2 px-4"
            @click="openModal('close')"
          >
            <p>Close Folder</p>
          </button>
          
          
        </div>
        <div class=" flex items-center mt-5 lg:mt-0 gap-2   justify-between">
          <!-- <button
            class="flex gap-2 items-center text-[#039855]  hover:bg-[#ECFDF3] hover:border hover:border-[#039855] rounded-lg py-2 px-4"
            @click="openModal('open')"
          >
            <p>Open Folder</p>
          </button> -->

     
          <button
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"
            @click="createFolderModal = true"
          >
            <Icon icon="quill:folder-add" width="20" />
            <p>Add Folder</p>
          </button>

          <router-link to="/branches/document-upload">
          <button
            class="flex gap-2 items-center text-white bg-[#3B82F6] rounded-lg py-2 px-4"
          >
          <i class="pi pi-cloud-upload" style="font-size: 1rem"></i>
            <p>Upload Files</p>
          </button>

        </router-link>
      </div>
           
        </div>
        <Divider class=" " />

        <section class=" lg:flex items-center justify-between px-5 lg:px-7">
       
            <div class="flex items-center justify-between  lg:w-[40%] text-sm">
          <div class="bg-gray-100 flex items-center rounded-md p-1">
            <button
              class="bg-white shadow-md border-gray-300 rounded-lg py-1 px-4"
            >
              <p>All</p>
            </button>
            <button
              class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4"
            >
              <p>Type</p>
              <Icon icon="icon-park-outline:down" width="16" />
            </button>
          </div>
          <button
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"
          >
            <Icon icon="ion:arrow-up" width="20" />
            <p>Name</p>
          </button>
          <button
            class=" hidden lg:visible flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"
          >
          <i class="pi pi-angle-down" ></i>
            <p>People</p>
          </button>
          <button
          @click="foldersGridView = !foldersGridView"
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"
          >
          <Icon v-if ="foldersGridView" icon="system-uicons:grid" width="20" />
          <Icon   v-else  icon="lucide:list" width="20" />
            <p v-if="foldersGridView">Grid</p>
            <p v-else> List</p>
          </button>
        </div>
   
      <div class=" flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
          <i class="pi pi-search text-gray-300 " style="font-size: 1rem"></i>
          <Input type="text" v-model="value" placeholder="Search folders & documents" class=" w-96 border-0 outline-none text-[#98A2B3]" />
      </div>
        </section>
        <Divider class=" " />
         
    <!-- ===============    folder table    ============ -->

        <div   v-if="documents.length > 0 && !foldersGridView">
          <h1 class=" text-lg font-semibold pl-5 lg:pl-7">Folders</h1>
        <DataTable
          v-model:selection="selectedFolder"
        
          paginator
          :rows="6"
          :value="folders"
          dataKey="id"
          :pt="{
            table: {
              class: 'w-full border border-border_primary border-solid mb-6',
            },
          }"
          tableStyle="min-width: 60rem; border-radius: 30px;"
        >
          <Column
            selectionMode="multiple"
            headerStyle="width: 2rem; color: #667085;"
          ></Column>
          <Column
            field="name"
            header="Folder Name"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
            <template #body="slotProps">
              <div class="flex items-center font-medium gap-2">
                <div class="p-2 rounded-full bg-gray-200">
                  <Icon
                    icon="material-symbols-light:folder-outline"
                    width="20"
                    color="blue"
                  />
                </div>
                {{ slotProps.data.name }}
              </div>
            </template>
          </Column>
          <Column
            field="number"
            header="Folder Number"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="size"
            header="Folder Size"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="date"
            header="Last Modified"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>

          <Column
            field="actions"
            header="Actions"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
            <template #body="{ data }">
              <div class="flex items-center gap-4">
                <div
                  @click="toggle"
                  aria-haspopup="true"
                  aria-controls="overlay_menu"
                  class="flex"
                >
                  <div
                    class="flex items-center font-normal h-full border border-gray-300 rounded-lg"
                  >
                    <div>
                      <div class="h-full">
                        <Icon
                          icon="majesticons:more-menu-vertical"
                          width="30"
                        />
                      </div>
                      <Menu
                        ref="menu"
                        id="overlay_menu"
                        :model="menuItems"
                        :popup="true"
                      >
                        <template #item="{ item }">
                          <div
                            v-if="data.category !== item.value"
                            class="flex gap-2 px-3 py-2 items-center"
                          >
                            <i :class="item.icon" />
                            <p>{{ item.label }}</p>
                          </div>
                        </template>
                      </Menu>
                    </div>
                  </div>
                </div>

                <div class="cursor-pointer">
                  <Icon icon="uiw:delete" width="16" />
                </div>
              </div>
            </template>
          </Column>
        </DataTable>
    </div>

    <!-- =================== Files table   =============== -->

    <div   v-if="documents.length > 0 && !foldersGridView">
      <h1 class=" text-lg font-semibold pl-7">Files</h1>
        <DataTable
          v-model:selection="selectedFolder"
        
          paginator
          :rows="6"
          :value="files"
          dataKey="id"
          :pt="{
            table: {
              class: 'w-full border border-border_primary border-solid mb-6',
            },
          }"
          tableStyle="min-width: 60rem; border-radius: 30px;"
        >
          <Column
            selectionMode="multiple"
            headerStyle="width: 2rem; color: #667085;"
          ></Column>
          <Column
            field="name"
            header="Folder Name"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
            <template #body="slotProps">
              <div class="flex items-center font-medium gap-2">
                <div class="p-2 rounded-full bg-gray-200">
                  <Icon
                    icon="material-symbols-light:folder-outline"
                    width="20"
                    color="blue"
                  />
                </div>
                {{ slotProps.data.name }}
              </div>
            </template>
          </Column>
          <Column
            field="date"
            header="Date Uploaded"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="size"
            header="Folder Size"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="uploaded_by"
            header="Uploaded By"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>

          <Column
            field="actions"
            header="Actions"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
            <template #body="{ data }">
              <div class="flex items-center gap-4">
                <div
                  @click="toggle"
                  aria-haspopup="true"
                  aria-controls="overlay_menu"
                  class="flex"
                >
                  <div
                    class="flex items-center font-normal h-full border border-gray-300 rounded-lg"
                  >
                    <div>
                      <div class="h-full">
                        <Icon
                          icon="majesticons:more-menu-vertical"
                          width="30"
                        />
                      </div>
                      <Menu
                        ref="menu"
                        id="overlay_menu"
                        :model="menuItems"
                        :popup="true"
                      >
                        <template #item="{ item }">
                          <div
                            v-if="data.category !== item.value"
                            class="flex gap-2 px-3 py-2 items-center"
                          >
                            <i :class="item.icon" />
                            <p>{{ item.label }}</p>
                          </div>
                        </template>
                      </Menu>
                    </div>
                  </div>
                </div>

                <div class="cursor-pointer">
                  <Icon icon="uiw:delete" width="16" />
                </div>
              </div>
            </template>
          </Column>
        </DataTable>
    </div>




     <!-- ============= Grid view   ======== -->

        <div v-if="documents.length > 0 && foldersGridView">

          <h1 class=" text-lg font-semibold pl-7">Folders</h1>
            <div
          class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8 px-7"
        >
        
          <div v-for="i in 8" class="bg-gray-200 p-2 rounded-md" :key="i">
            <div class="flex items-center justify-between">
              <div class="p-2 rounded-full bg-white w-fit">
                <Icon
                  icon="material-symbols-light:folder-outline"
                  width="20"
                  color="blue"
                />
              </div>
              <h1>Communication.odf</h1>
              <div>
                <Icon icon="majesticons:more-menu-vertical" width="30" />
              </div>
            </div>

            <div class="mt-2">
              <img
                class="w-full rounded-md"
                src="@/assets/images/document.png"
                alt="Document image"
              />
            </div>
          </div>
        </div>
        </div>

       <!-- ===========  files ======== -->
        <div v-if="documents.length > 0 && foldersGridView" class=" mt-10">
          <h1 class=" text-lg font-semibold pl-7">File</h1>
            <div
          class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8 px-7"
        >
          <div v-for="i in 8" class="bg-gray-200 p-2 rounded-md" :key="i">
            <div class="flex items-center justify-between">
              <div class="p-2 rounded-full bg-white w-fit">
                <Icon
                  icon="material-symbols-light:folder-outline"
                  width="20"
                  color="blue"
                />
              </div>
              <h1>Communication.odf</h1>
              <div>
                <Icon icon="majesticons:more-menu-vertical" width="30" />
              </div>
            </div>

            <div class="mt-2">
              <img
                class="w-full rounded-md"
                src="@/assets/images/document.png"
                alt="Document image"
              />
            </div>
          </div>
        </div>
        </div>
    </main>
    <CreateFolderDialog v-model="createFolderModal" />
    <FolderDialog :action="action" v-model="FolderModal" />
</template>

<script setup>
   import { ref } from "vue";
   import CreateFolderDialog from "@/components/Modals/branches/CreateFolderDialog.vue";
   import FolderDialog from "@/components/Modals/branches/FolderDialog.vue";
   import { useRouter } from 'vue-router';



   const foldersGridView = ref(false);
   const createFolderModal = ref(false);
   const FolderModal = ref(false);
   const action = ref('')

   const openModal = (data)=>{
      action.value = data
      FolderModal.value = true
   }


   const router = useRouter();

const goBack = () => {
  // Use Vue Router's go method with -1 to go back one step in the history
  router.go(-1);
};



    const folders = ref([
  {
    id: "1",
    name: "Communication.pdf",
    number: "GfrTR",
    size: "150 KB",
    date: "16 Nov 23",
  },
  {
    id: "2",
    name: "Communication.pdf",
    number: "GfrTR",
    size: "150 KB",
    date: "16 Nov 23",
  },
  {
    id: "3",
    name: "Communication.pdf",
    number: "GfrTR",
    size: "150 KB",
    date: "16 Nov 23",
  },
]);

const files = ref([
  {
    id: "1",
    name: "Communication.pdf",
    date: "15 Nov 23",
    size: "150 KB",
    uploaded_by: "Dave Miye",
  },
  {
    id: "2",
    name: "Communication.pdf",
    date: "15 Nov 23",
    size: "150 KB",
    uploaded_by: "Dave Miye",
  },
  {
    id: "3",
    name: "Communication.pdf",
    date: "15 Nov 23",
    size: "150 KB",
    uploaded_by: "Dave Miye",
  },
]);

const documents = ref([
  {
    id: "1000",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    uploaded_by: "Dave Miye",
  },
  {
    id: "1000",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    uploaded_by: "Dave Miye",
  },
  {
    id: "1000",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    uploaded_by: "Dave Miye",
  },
]);
const selectedFolder = ref([]);

</script>

