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
          <Icon v-if ="foldersGridView" icon="lucide:list"  width="20" />
          <Icon   v-else   icon="system-uicons:grid" width="20" />
          <p v-if="foldersGridView">List</p>
          <p v-else>Grid</p>
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
          paginator
          :rows="6"
        
          :value="folders"
          dataKey="id"
          :pt="{
            table: {
              class:
                'w-full border border-border_primary border-solid mb-6 mt-2',
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
            header="File Name"
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
              bodyCell: { class: 'text-gray-500' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="size"
            header="Folder Size"
            :pt="{
              bodyCell: { class: 'text-gray-500' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="date"
            header="Last Modified"
            :pt="{
              bodyCell: { class: 'text-gray-500' },
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
          <template #body="slotProps">
         
           <div >
            <div id="menu1" class="menu  absolute right-36 z-50   " v-if="folderMenu && selectedFolderId === slotProps.data.id" ref="folderRef" >
              <div class=" w-[417px] h-[420px] relative " :class="{ ' bottom-72': isPositionChanged }" @click="close"  >
                <div class=" dropmenu  w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white  absolute right-0 shadow-lg  cursor-pointer" @click="close" >
          <div class=" flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2" v-for="(item,index) in menuItem" :key="index" @click="handleMenuItem(item)" >
          <div class=" flex items-center gap-2  " >
            <Icon :icon="item.icon"  />
            <p>{{ item.label }}</p>
          </div>
          <Icon :icon="item.arrow" />
        </div>
       </div>

       <div class="   w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white  absolute bottom-0 left-0 shadow-lg cursor-pointer" :class="{ ' bottom-28': isSecondPosition }" v-if="submenu" @click="close" > 
          <div class=" flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2" v-for="(item,index) in submenuItem " :key="index" @click="handleMenuItem(item)">
            <Icon :icon="item.icon" />
            <p>{{ item.label }}</p>
          </div>
       </div>
      </div>
    </div>
          <div class=" flex gap-3 items-center">  
            <div  @click="toggleFolderMenu(slotProps.data.id)" class=" p-2 border cursor-pointer rounded-md ">
            <Icon icon="simple-line-icons:options-vertical" />
          </div>
           <div class="cursor-pointer  " @click="handleButtonClick">
              <Icon icon="uiw:delete" width="16" />
            </div>
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
          v-model:selection="selectedDocument"
          paginator
          :rows="3"
        
          :value="documents"
          dataKey="id"
          :pt="{
            table: {
              class:
                'w-full border border-border_primary border-solid mb-6 mt-2',
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
            header="File Name"
            :pt="{
              bodyCell: { class: 'text-gray-700' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
            <template #body="slotProps">
              <div class="flex items-center font-medium gap-2">
                <div class="p-2 rounded-full bg-gray-200">
                  <Icon icon="vscode-icons:file-type-powerpoint" width="25"/>
                </div>
                {{ slotProps.data.name }}
              </div>
            </template>
          </Column>
          <Column
            field="date"
            header="Date Uploaded"
            :pt="{
              bodyCell: { class: 'text-gray-500' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="size"
            header="File Size"
            :pt="{
              bodyCell: { class: 'text-gray-500' },
            }"
            headerStyle="color: #667085; font-weight: 500;"
          >
          </Column>
          <Column
            field="uploaded_by"
            header="Uploaded By"
            :pt="{
              bodyCell: { class: 'text-gray-500' },
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
          <template #body="slotProps">
         
           <div >
            <div id="menu1" class="menu  absolute right-36 z-50   " v-if="menuVisible && selectedDocumentId === slotProps.data.id" ref="modalRef" >
              <div class=" w-[417px] h-[420px] relative " :class="{ ' bottom-72': isPositionChanged }" @click="close"  >
                <div class=" dropmenu  w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white  absolute right-0 shadow-lg  cursor-pointer" @click="close" >
          <div class=" flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2" v-for="(item,index) in menuItem" :key="index" @click="handleMenuItem(item)" >
          <div class=" flex items-center gap-2  " >
            <Icon :icon="item.icon"  />
            <p>{{ item.label }}</p>
          </div>
          <Icon :icon="item.arrow" />
        </div>
       </div>

       <div class="   w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white  absolute bottom-0 left-0 shadow-lg cursor-pointer" :class="{ ' bottom-28': isSecondPosition }" v-if="submenu" @click="close" > 
          <div class=" flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2" v-for="(item,index) in submenuItem " :key="index" @click="handleMenuItem(item)">
            <Icon :icon="item.icon" />
            <p>{{ item.label }}</p>
          </div>
       </div>

      </div>
    </div>
          <div class=" flex gap-3 items-center">  
            <div  @click="toggleMenu(slotProps.data.id)" class=" p-2 border cursor-pointer rounded-md ">
            <Icon icon="simple-line-icons:options-vertical" />
          </div>
           <div class="cursor-pointer  " @click="handleButtonClick">
              <Icon icon="uiw:delete" width="16" />
            </div>
          </div>
        </div>
            
            </template>
          </Column>
        </DataTable>
    </div>




     <!-- ============= Grid view   ======== -->


     <!-- =================  Folder  =============== -->
     <div  v-if="documents.length > 0 && foldersGridView" class=" mx-7 mt-5">

      <h1 class=" text-xl "> Folders</h1>
     <div
         
          class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8"
        >

        
          <div
            v-for="i in 8"
            class="bg-gray-200 p-2 rounded-md relative"
            :key="i"
          >
            <div class="flex items-center justify-between">
              <div class="p-2 rounded-full bg-white w-fit">
                <Icon
                  icon="material-symbols-light:folder-outline"
                  width="20"
                  color="blue"
                />
              </div>
              <h1>Communication.odf</h1>
              <div class="flex gap-3 items-center">
                <div
                  @click="toggleMenu(i)"
                  class="p-2 border cursor-pointer rounded-md"
                >
                  <Icon icon="simple-line-icons:options-vertical" class="" />
                </div>
              </div>

              <div
                id="menu1"
                class="menu absolute right-5 z-50 "
                v-if="menuVisible && selectedDocumentId === i"
                ref="modalRef"
              >
                <div
                  class="w-[417px] h-[420px] relative "
                  @click.stop
                  :class="isPositionChanged ? '-top-20' : 'top-52'"
                >
                  <div
                    class="dropmenu w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute right-0 shadow-lg cursor-pointer"
                  >
                    <div
                      class="flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2"
                      v-for="(item, index) in menuItem"
                      :key="index"
                      @click="handleMenuItem(item)"
                    >
                      <div class="flex items-center gap-2">
                        <Icon :icon="item.icon" />
                        <p>{{ item.label }}</p>
                      </div>
                      <Icon :icon="item.arrow" />
                    </div>
                  </div>

                  <div
                    class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
                    :class="{ ' bottom-28': isSecondPosition }"
                    :style="{ left: horizontalPosition ? '420px' : 'auto' }"
                    v-if="submenu"
                  >
                    <div
                      class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2"
                      v-for="(item, index) in submenuItem"
                      :key="index"
                      @click="handleMenuItem(item)"
                    >
                      <Icon :icon="item.icon" />
                      <p>{{ item.label }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-2">
              <img
                class="w-full rounded-md"
                src="@/assets/images/document.svg"
                alt="Document image"
              />
            </div>
          </div>
        </div>
      </div>
       <!-- =========== files  ======== -->
       <div  v-if="documents.length > 0 && foldersGridView" class=" mx-7 mt-16">

<h1 class=" text-xl "> Files</h1>
<div
   
    class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8"
  >

  
    <div
      v-for="i in 8"
      class="bg-gray-200 p-2 rounded-md relative"
      :key="i"
    >
      <div class="flex items-center justify-between">
        <div class="p-2 rounded-full bg-white w-fit">
          <Icon
            icon="material-symbols-light:folder-outline"
            width="20"
            color="blue"
          />
        </div>
        <h1>Communication.odf</h1>
        <div class="flex gap-3 items-center">
          <div
            @click="toggleMenu(i)"
            class="p-2 border cursor-pointer rounded-md"
          >
            <Icon icon="simple-line-icons:options-vertical" class="" />
          </div>
        </div>

        <div
          id="menu1"
          class="menu absolute right-5 z-50 "
          v-if="menuVisible && selectedDocumentId === i"
          ref="modalRef"
        >
          <div
            class="w-[417px] h-[420px] relative "
            @click.stop
            :class="isPositionChanged ? '-top-20' : 'top-52'"
          >
            <div
              class="dropmenu w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute right-0 shadow-lg cursor-pointer"
            >
              <div
                class="flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2"
                v-for="(item, index) in menuItem"
                :key="index"
                @click="handleMenuItem(item)"
              >
                <div class="flex items-center gap-2">
                  <Icon :icon="item.icon" />
                  <p>{{ item.label }}</p>
                </div>
                <Icon :icon="item.arrow" />
              </div>
            </div>

            <div
              class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
              :class="{ ' bottom-28': isSecondPosition }"
              :style="{ left: horizontalPosition ? '420px' : 'auto' }"
              v-if="submenu"
            >
              <div
                class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2"
                v-for="(item, index) in submenuItem"
                :key="index"
                @click="handleMenuItem(item)"
              >
                <Icon :icon="item.icon" />
                <p>{{ item.label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-2">
        <img
          class="w-full rounded-md"
          src="@/assets/images/document.svg"
          alt="Document image"
        />
      </div>
    </div>
  </div>
</div>
    </main>
    <CreateFolderDialog v-model="createFolderModal" />
    <BranchDetails v-model="branchDetailsModal" />
    <LockDocument v-model="LockDocumentModal"/>
    <UnlockDocument v-model="UnlockDocumentModal"/>
    <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
     <ChangeFormat v-model="ChangeFormatModal"/>
</template>

<script setup>
  import { ref } from "vue";
import Emptystate from "@/assets/Emptystate.vue";
import { useRouter } from 'vue-router';
import CreateFolderDialog from "@/components/Modals/branches/CreateFolderDialog.vue";
import BranchDetails from "@/components/Modals/branches/BranchDetails.vue";
import LockDocument from "@/components/Modals/branches/LockDocument.vue";
import UnlockDocument from "@/components/Modals/branches/UnlockDocument.vue";
import GenerateEncriptionCode from "@/components/Modals/branches/GenerateEncriptionCode.vue";
import ChangeFormat from "@/components/Modals/branches/ChangeFormatModal.vue";
import { onClickOutside } from '@vueuse/core'
const router = useRouter();

const goBack = () => {
  router.go(-1);
};
const createFolderModal = ref(false);
const branchDetailsModal = ref(false);
const LockDocumentModal = ref(false)
const UnlockDocumentModal = ref(false)
const GenerateEncriptionModal = ref(false)
const foldersGridView = ref(true);
const ChangeFormatModal = ref(false);

const isPositionChanged = ref(false);
const isSecondPosition = ref(false)
const selectedDocumentId = ref(null); 
const selectedFolderId = ref(null); 
const horizontalPosition = ref(null);
const folderMenu = ref(false)

const menuVisible = ref(false);
const modalRef = ref(null)
const folderRef = ref(null)
const submenu = ref(false)

onClickOutside(
  modalRef,
  (event) => {
    menuVisible.value = false,
    
    submenu.value = false
  },
)

onClickOutside(
  folderRef,
  (event) => {
    folderMenu.value = false
    submenu.value = false
  },
)
const menuItem = ref([
  {
    icon: "icon-park-outline:preview-open",
    label: "Preview"
  },
  {
    icon: "ic:outline-drive-file-rename-outline",
    label: "Rename"
  },
  {
    icon: "material-symbols:download-sharp",
    label: "Download"
  },
  {
    icon: "pajamas:duplicate",
    label: "Duplicate"
  },
  {
    icon: "material-symbols-light:share-outline",
    label: "Share"
  },
  {
    icon: "ph:swap",
    label: "Change Format"
  },
  {
    icon: "lucide:lock",
    label: "Encription Code"
  },
  {
    icon: "ri:information-line",
    label: "File Information",
    arrow: "flowbite:angle-right-outline"
  }
])

const submenuItem = ref([
{
    icon: "ri:information-line",
    label: "Details"
  },
  {
    icon: "iconoir:activity",
    label: "Activity"
  },
  {
    icon: "pajamas:duplicate",
    label: "File Version"
  },
  {
    icon: "lucide:lock",
    label: "Lock"
  },
])

const handleMenuItem = (item) => {
  if (item.label === "File Information") {
    menuVisible.value = true
    submenu.value = !submenu.value;
  }

  if (item.label === "Lock") {
    LockDocumentModal.value = true;
  }
  if (item.label === "Encription Code") {
    GenerateEncriptionModal.value = true;
  }
  if (item.label === "Details") {
    branchDetailsModal.value = true;
  }
  if (item.label === "Change Format") {
    ChangeFormatModal.value = true;
  }
};
 const close = ()=>{
  folderMenu.value = false
  menuVisible.value = false,
  submenu.value = false
 }



 const toggleFolderMenu = (documentId) => {
  folderMenu.value = !folderMenu.value && selectedFolderId.value === documentId;
  selectedFolderId.value = documentId;
  const { clientY } = event;
  const { clientX } = event;

  const viewportHeight = window.innerHeight;
  const viewportWidth = window.innerWidth;
  const clientYPercentage = (clientY / viewportHeight) * 100;
  const clientXPercentage = (clientX / viewportWidth) * 100;
  horizontalPosition.value = clientXPercentage <= 75;
  isPositionChanged.value = clientYPercentage >= 45;
  isSecondPosition.value = clientYPercentage >= 83 && clientYPercentage <= 100;

  console.log(isPositionChanged.value);
};


const toggleMenu = (documentId) => {
  menuVisible.value =
    !menuVisible.value && selectedDocumentId.value === documentId;
  selectedDocumentId.value = documentId;
  const { clientY } = event;
  const { clientX } = event;
  const viewportHeight = window.innerHeight;
  const viewportWidth = window.innerWidth;
  const clientYPercentage = (clientY / viewportHeight) * 100;
  const clientXPercentage = (clientX / viewportWidth) * 100;
  horizontalPosition.value = clientXPercentage <= 75;
  isPositionChanged.value = clientYPercentage >= 45;
  isSecondPosition.value = clientYPercentage >= 83 && clientYPercentage <= 100;
};



  





const folders = ref([
  {
    id: "1001",
    name: "Communication.pdf",
    number: "GfrTR",
    size: "150 KB",
    date: "16 Nov 23",
  },
  {
    id: "1002",
    name: "Communication.pdf",
    number: "GfrTR",
    size: "150 KB",
    date: "16 Nov 23",
  },
  {
    id: "1003",
    name: "Communication.pdf",
    number: "GfrTR",
    size: "150 KB",
    date: "16 Nov 23",
  },
]);


const documents = ref([
  {
    id: "1001",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
  },
  {
    id: "1002",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
  },
  {
    id: "1003",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
  },
  {
    id: "1004",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
  }
  
]);
const selectedDocument = ref([]);
</script>

<style  scoped>
.p-submenu-list{
  background: red;
}




</style>

