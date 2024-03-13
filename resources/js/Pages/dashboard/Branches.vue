<template>
  <main>
    <div class=" flex items-center px-7 py-5 justify-between w-full">
      <p class="text-3xl text-gray-800 font-semibold">
        {{ department.title }} Department
      </p>
      <Link :href="'/dashboard/archiving/'+department.id">
      <button class="flex gap-2 items-center text-white bg-[#3B82F6] rounded-lg py-2 px-4">
        <i class="pi pi-cloud-upload" style="font-size: 1rem"></i>
        <p>Upload Files</p>
      </button>
      </Link>
    </div>
    <Divider class="-mt-0.5" />
    <!-- <div>
      <div class="mx-7   bg-[#FCFCFD] border-dashed flex flex-col items-center py-7 rounded-xl border-[3px] mt-10">
        <i class="pi pi-cloud-upload py-2 px-3 text-2xl"></i>
        <div class="text-center">
          <h1 class="font-semibold">Choose a file or drag & drop it here</h1>
          <p class="text-sm leading-7 mb-7 text-[#475467]">
            JPEG, PNG, PDF, MP3 & other formats
          </p>
        </div>

        <button class="bg-white border-gray-300 border p-3 rounded-lg relative">
          <input type="file" class="opacity-0 absolute w-full" />Browse File
        </button>
      </div>
    </div> -->

    <div class=" px-7 mt-10">
      <div class=" flex items-center w-full justify-between">
        <h1 class=" text-lg font-semibold">Folder</h1>
        <div class=" flex items-center gap-5">
          <button @click="folderGrid = !folderGrid"
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon v-if="folderGrid" icon="lucide:list" width="20" />
            <Icon v-else icon="system-uicons:grid" width="20" />
            <p v-if="folderGrid">List</p>
            <p v-else>Grid</p>
          </button>

          <button @click="createFolderModal = true"
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon icon="quill:folder-add" width="20" />
            <p>Add Folder</p>
          </button>
        </div>
      </div>
      <!--Folders Grid View -->
      <div v-if="folderGrid" class="grid xl:grid-cols-4 mt-2  mb-4 md:grid-cols-2 grid-cols-1 gap-8">
        <div v-for="folder in department.folder" class="rounded-md flex items-center justify-between hover:bg-gray-100 p-3" :key="i">
          <Link :href="'/dashboard/folder-view/'+folder.id">
          <div class="border rounded-md p-2 bg-gray-100 cursor-pointer">
            <img src="@/assets/images/folder.svg" alt="" />
          </div>
          </Link>
          <div>
            <h1 class="text-lg font-medium">{{ folder.name }}</h1>
            <div class="flex items-center gap-3 mt-1">
              <p>{{folder.file_count}} Files</p>
              <div class="w-fit py-1 px-2 rounded-2xl bg-green-200 text-xs text-green-800">
                Open
              </div>
            </div>
          </div>

          <div class="cursor-pointer">
            <Icon icon="iconamoon:menu-kebab-vertical-bold" width="25" />
          </div>
        </div>
      </div>

      <!-- Folders List View -->
      <div v-else class="mb-8  text-sm">
        <DataTable paginator :rows="6" :value="folders" dataKey="id" :pt="{
          table: {
            class:
              'w-full border border-border_primary border-solid mb-6 mt-2',
          },
        }" tableStyle="min-width: 60rem; border-radius: 30px;">
          <Column selectionMode="multiple" headerStyle="width: 2rem; color: #667085;"></Column>
          <Column field="name" header="File Name" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="slotProps">
              <div class="flex items-center font-medium gap-2">
                <div class="p-2 rounded-full bg-gray-200">
                  <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
                </div>
                {{ slotProps.data.name }}
              </div>
            </template>
          </Column>
          <Column field="number" header="Folder Number" :pt="{
            bodyCell: { class: 'text-gray-500' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="folder_size" header="Folder Size" :pt="{
            bodyCell: { class: 'text-gray-500' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="date" header="Last Modified" :pt="{
            bodyCell: { class: 'text-gray-500' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>

          <Column field="actions" header="Actions" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="slotProps">
              <div>
                <div id="menu1" class="menu absolute right-36 z-50"
                  v-if="folderMenu && selectedFolderId === slotProps.data.id" ref="folderRef">
                  <div class="w-[417px] h-[420px] relative" :class="{ ' bottom-72': isPositionChanged }">
                    <div
                      class="dropmenu w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute right-0 shadow-lg cursor-pointer">
                      <div class="flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2"
                        v-for="(item, index) in menuItem" :key="index" @click="handleMenuItem(item)">
                        <div class="flex items-center gap-2">
                          <Icon :icon="item.icon" />
                          <p>{{ item.label }}</p>
                        </div>
                        <Icon :icon="item.arrow" />
                      </div>
                    </div>

                    <div
                      class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
                      :class="{ ' bottom-28': isSecondPosition }" v-if="submenu">
                      <div class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2"
                        v-for="(item, index) in submenuItem" :key="index" @click="handleMenuItem(item)">
                        <Icon :icon="item.icon" />
                        <p>{{ item.label }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="flex gap-3 items-center">
                  <div @click="toggleFolderMenu(slotProps.data.id)" class="p-2 border cursor-pointer rounded-md">
                    <Icon icon="simple-line-icons:options-vertical" />
                  </div>
                  <div class="cursor-pointer" @click="handleButtonClick">
                    <Icon icon="uiw:delete" width="16" />
                  </div>
                </div>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
    <section class="mt-8">
      <div class="px-7 flex items-center justify-between pb-2">
        <h1 class="font-medium text-xl">Documents</h1>

        <div class="flex items-center gap-5 text-sm">
          <div class="bg-gray-100 flex items-center rounded-md p-1">
            <button class="bg-white shadow-md border-gray-300 rounded-lg py-1 px-4">
              <p>All</p>
            </button>
            <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4">
              <p>Type</p>
              <Icon icon="icon-park-outline:down" width="16" />
            </button>
          </div>
          <button class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon icon="ion:arrow-up" width="20" />
            <p>Name</p>
          </button>
          <button @click="documentsGridView = !documentsGridView"
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon v-if="documentsGridView" icon="lucide:list" width="20" />
            <Icon v-else icon="system-uicons:grid" width="20" />
            <p v-if="documentsGridView">List</p>
            <p v-else>Grid</p>
          </button>
        </div>
      </div>

      <div class="px-7 text-sm">
        <div v-if="files.length === 0"
          class="border border-solid border-gray-300 rounded-lg mt-3 flex items-center justify-center w-full h-[35rem]">
          <div class="text-center m-auto">
            <Emptystate class="m-auto" />
            <p class="mb-1 mt-3">No Records Yet</p>
            <p class="text-gray-500 font-light">
              Documents uploaded to this branch would show up here
            </p>
            <div
              class="mt-3 m-auto w-56 flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4 cursor-pointer">
              <Icon icon="mynaui:cloud-upload" width="25" />
              <Link href="/dashboard/archiving">Upload Document</Link>
            </div>
          </div>
        </div>

        <!-- ======================   Document Grid   ================ -->
        <div v-if="department?.archive?.length > 0 && documentsGridView"
          class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8">
          <div v-for="item in department.archive" class="bg-gray-200 p-2 rounded-md relative" :key="i">
            <div class="flex items-center justify-between">
              <div class="p-2 rounded-full bg-white w-fit">
                <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
              </div>
              <h1 class="truncate">{{ item.file_path[0].file_name }}</h1>
              <div class="flex gap-3 items-center">
                <div @click="toggleMenu(i)" class="p-2 border cursor-pointer rounded-md">
                  <Icon icon="simple-line-icons:options-vertical" class="" />
                </div>
              </div>

              <div id="menu1" class="menu absolute right-5 z-50 " v-if="menuVisible && selectedDocumentId === i"
                ref="modalRef">
                <div class="w-[417px] h-[420px] relative " @click.stop :class="isPositionChanged ? '-top-20' : 'top-52'">
                  <div
                    class="dropmenu w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute right-0 shadow-lg cursor-pointer">
                    <div class="flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2"
                      v-for="(item, index) in menuItem" :key="index" @click="handleMenuItem(item)">
                      <div class="flex items-center gap-2">
                        <Icon :icon="item.icon" />
                        <p>{{ item.label }}</p>
                      </div>
                      <Icon :icon="item.arrow" />
                    </div>
                  </div>

                  <div
                    class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
                    :class="{ ' bottom-28': isSecondPosition }" :style="{ left: horizontalPosition ? '420px' : 'auto' }"
                    v-if="submenu">
                    <div class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2" v-for="(item, index) in submenuItem"
                      :key="index" @click="handleMenuItem(item)">
                      <Icon :icon="item.icon" />
                      <p>{{ item.label }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-2">
              <img class="w-full rounded-md" :src="page.props.base+'/storage'+item.file_path[0].path" alt="Document image" />
            </div>
          </div>
        </div>
      </div>

      <!-- ======================   Document list   ================ -->
      <!-- <div v-if="files && !documentsGridView" class=" px-7">
        <DataTable v-model:selection="selectedDocument" paginator :rows="20" :value="files" dataKey="id" :pt="{
          table: {
            class:
              'w-full border border-border_primary border-solid mb-6 mt-2',
          },
        }" tableStyle="min-width: 60rem; border-radius: 30px;">
          <Column selectionMode="multiple" headerStyle="width: 2rem; color: #667085;"></Column>
          <Column field="name" header="File Name" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="slotProps">
              <div class="flex items-center font-medium gap-2">
                <div class="p-2 rounded-full bg-gray-200">
                  <Icon icon="vscode-icons:file-type-powerpoint" width="25" />
                </div>

                {{ slotProps.data.name }}
              </div>
            </template>
          </Column>
          <Column field="date" header="Date Uploaded" :pt="{
            bodyCell: { class: 'text-gray-500' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="size" header="File Size" :pt="{
            bodyCell: { class: 'text-gray-500' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <Column field="uploaded_by" header="Uploaded By" :pt="{
            bodyCell: { class: 'text-gray-500' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>

          <Column field="actions" header="Actions" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="{ data }">

              <div class="flex items-center gap-4">
                <div @click="toggle2($event, data)" aria-haspopup="true" aria-controls="overlay_menu">
                  <div class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                    <div>
                      <div class="h-full">
                        <Icon icon="majesticons:more-menu-vertical" width="30" />
                      </div>
                      <TieredMenu ref="menu2" id="overlay_menu" :model="menuItems" :popup="true">
                        <template #item="{ item, props, hasSubmenu }" >

                          <a class="flex align-location-center" v-bind="props.action">
                            <span :class="item.icon" />
                            <span class="ml-2" @click="handleItemClick(item)">{{ item.label }}</span>
                           
                            <i v-if="hasSubmenu" class="pi pi-angle-right ml-auto text-primary"></i>
                          </a>
                          <div v-if="hasSubmenu" class="submenu">
                            <TieredMenu :model="item.submenuItems" :popup="true"></TieredMenu>
                          </div>
                        </template>
                      </TieredMenu>
                    </div>
                  </div>
                </div>

                <div class="cursor-pointer" @click="deleteArchive(data)">
                  <Icon icon="uiw:delete" width="16" />
                </div>
              </div>

            </template>
          </Column>
        </DataTable>
      </div> -->

      <ListVue :files="files" :documentsGridView="documentsGridView" @docs="SetselectedDocument" 
          @changeFormat="setFormatModal" @lock="setLock" @fileRename="setFileRename" @downloadFile="downloadFile"
          @selectedArchive="setSelectedArchive" @branchDetails="setBranchDetails"/>
    </section>

    <CreateFolderDialog v-model="createFolderModal" :url="'/dashboard/create-folder'" />
    <BranchDetails v-model="branchDetailsModal" :file="selectedArchive"/>
    <LockDocument v-model="LockDocumentModal" />
    <UnlockDocument v-model="UnlockDocumentModal" />
    <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
    <ChangeFormat v-model="ChangeFormatModal" />
    <PreviewFile v-if="togglePreview"  :togglePreviewFunction="togglePreviewState" :file="selectedArchive"/>
    <RenameFileDialog v-model="FileRenameModal" :archive="selectedArchive"/>
  </main>
</template>
<script setup>
import { ref, defineProps, computed } from 'vue';

import CreateFolderDialog from "@/components/Modals/branches/CreateFolderDialog.vue";
import BranchDetails from "@/components/Modals/branches/BranchDetails.vue";
import LockDocument from "@/components/Modals/branches/LockDocument.vue";
import UnlockDocument from "@/components/Modals/branches/UnlockDocument.vue";
import GenerateEncriptionCode from "@/components/Modals/branches/GenerateEncriptionCode.vue";
import ChangeFormat from "@/components/Modals/branches/ChangeFormatModal.vue";
import PreviewFile from "@/components/Modals/branches/PreviewFile.vue";
import { onClickOutside } from "@vueuse/core";
import { Link, usePage } from '@inertiajs/vue3';
import ListVue from '../dashboard/partials/ListVue.vue';
import RenameFileDialog from "../../components/Modals/branches/RenameFileDialog.vue";

const folderGrid = ref(false)
const selectedDocumentId = ref(null);
const createFolderModal = ref(false);
const branchDetailsModal = ref(false);
const LockDocumentModal = ref(false);
const UnlockDocumentModal = ref(false);
const GenerateEncriptionModal = ref(false);
const documentsGridView = ref(false);
const ChangeFormatModal = ref(false);
const isPositionChanged = ref(false);
const isSecondPosition = ref(false);
const selectedFolderId = ref(null);
const togglePreview = ref(false);
const FileRenameModal = ref(false);

const folderMenu = ref(false);

const menuVisible = ref(false);
const modalRef = ref(null);
const folderRef = ref(null);
const submenu = ref(false);
const page = usePage()
const selectedArchiveId = ref();
const selectedArchive = ref();
const menu2 = ref();

const props = defineProps(['department']);

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const SetselectedDocument = (data)=>{
  selectedDocument.value = data.value
}

const setSelectedArchive = (data)=>{
  selectedArchive.value = data.value
}

const setFormatModal = (data)=>{
  ChangeFormatModal.value = data
}

const setBranchDetails = (data)=>{
  branchDetailsModal.value = data
}

const setFileRename = (data)=>{
 console.log(data)
  FileRenameModal.value = data
}

const setLock =(data)=>{
  LockDocumentModal.value = data
}

const downloadFile = async () => {
  try {
    const response = await axios.get('/download-file', {
      params: { id: selectedArchiveId.value },
      responseType: 'blob'
    });

    // Extract the filename from the Content-Disposition header
    const contentDisposition = response.headers['content-disposition'];
    const fileName = contentDisposition.split('filename=')[1];

    // Determine the content type based on the file extension
    const contentType = fileName.endsWith('.pdf') ? 'application/pdf' :
                        fileName.endsWith('.jpg') || fileName.endsWith('.jpeg') ? 'image/jpeg' :
                        fileName.endsWith('.png') ? 'image/png' :
                        'application/octet-stream'; // Default to binary data

    // Create a blob object from the binary data
    const blob = new Blob([response.data], { type: contentType });

    // Create a URL for the blob object
    const url = window.URL.createObjectURL(blob);

    // Create a link element and simulate a click to trigger the file download
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileName); // Set the filename for the downloaded file
    document.body.appendChild(link);
    link.click();

    // Cleanup by revoking the object URL
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error downloading file:', error);
  }
};


const menuItems = ref([
  {
    label: 'Preview',
    icon: 'pi pi-eye',
    command: ()=>{
      togglePreview.value = true;
    }
  },
  {
    label: 'Rename',
    icon: 'pi pi-pencil',
    command: () =>{
      FileRenameModal.value = true;
    }
  },
  {
    label: 'Download',
    icon: 'pi pi-download',
    command: ()=>{
       downloadFile()
    }
  },
  {
    label: 'Duplicate',
    icon: 'pi pi-clone',
    command: () => {
      router.post('/duplicate-file',{id: selectedArchiveId.value},{onSuccess: page =>{
        console.log(page)
      }})
    }
  },
  {
    label: 'Share',
    icon: 'pi pi-share-alt',
  },
  {
    label: 'Change Format',
    icon: "ph:swap",
  },
  {
    label: 'File Information',
    icon: 'pi pi-info-circle',
    items: [
      {
        label: "Details",
        icon: 'pi pi-info-circle',
        command: ()=>{
          branchDetailsModal.value = true;
        }
      },
      {
        label: "Activity",
      },
      {
        label: "File Versions",
      },
      {
        label: "Lock",
        icon: 'pi pi-lock'
      }
    ]
  },
]);

onClickOutside(modalRef, (event) => {
  (menuVisible.value = false), (submenu.value = false);
});

const formatDate =(inputDate)=> {
    var date = new Date(inputDate);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    var day = date.getDate();
    var month = months[date.getMonth()];
    var year = date.getFullYear();

    day = day < 10 ? '0' + day : day;

    var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);

    return formattedDate;
}

onClickOutside(folderRef, (event) => {
  folderMenu.value = false;
  submenu.value = false;
});

const handleItemClick = (item) => {

if (item.label === 'Lock') {
  LockDocumentModal.value = true
}
if (item.label === 'Change Format') {
  ChangeFormatModal.value = true
}
}

const close = () => {
  folderMenu.value = false
  menuVisible.value = false,
    submenu.value = false
}
// const folders = ref([
//   {
//     id: "1001",
//     name: "Communication.pdf",
//     number: "GfrTR",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
//   {
//     id: "1002",
//     name: "Communication.pdf",
//     number: "GfrTR",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
//   {
//     id: "1003",
//     name: "Communication.pdf",
//     number: "GfrTR",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
// ]);

const menuItem = ref([
  {
    icon: "icon-park-outline:preview-open",
    label: "Preview",
  },
  {
    icon: "ic:outline-drive-file-rename-outline",
    label: "Rename",
  },
  {
    icon: "material-symbols:download-sharp",
    label: "Download",
  },
  {
    icon: "pajamas:duplicate",
    label: "Duplicate",
  },
  {
    icon: "material-symbols-light:share-outline",
    label: "Share",
  },
  {
    icon: "ph:swap",
    label: "Change Format",
  },
  {
    icon: "lucide:lock",
    label: "Encription Code",
  },
  {
    icon: "ri:information-line",
    label: "File Information",
    arrow: "flowbite:angle-right-outline",
  },
]);

const submenuItem = ref([
  {
    icon: "ri:information-line",
    label: "Details",
  },
  {
    icon: "iconoir:activity",
    label: "Activity",
  },
  {
    icon: "pajamas:duplicate",
    label: "File Version",
  },
  {
    icon: "lucide:lock",
    label: "Lock",
  },
]);

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
const horizontalPosition = ref(null);
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

// const menuItems = ref([
//   {
//     id: "1001",
//     name: "Communication.pdf",
//     number: "GfrTR",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
//   {
//     id: "1002",
//     name: "Communication.pdf",
//     number: "GfrTR",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
//   {
//     id: "1003",
//     name: "Communication.pdf",
//     number: "GfrTR",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
// ]);

// const documents = ref([
//   {
//     id: "1001",
//     name: "Communications.pdf",
//     uploaded_by: "Dave Miye",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
//   {
//     id: "1002",
//     name: "Communication.pdf",
//     uploaded_by: "Dave Miye",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
//   {
//     id: "1003",
//     name: "Communication.pdf",
//     uploaded_by: "Dave Miye",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
//   {
//     id: "1004",
//     name: "Communication.pdf",
//     uploaded_by: "Dave Miye",
//     size: "150 KB",
//     date: "16 Nov 23",
//   },
// ]);

const menu = ref();
// const items = ref([
//   {
//     label: "File",
//     icon: "pi pi-file",
//     items: [
//       {
//         label: "New",
//         icon: "pi pi-plus",
//         items: [
//           {
//             label: "Document",
//             icon: "pi pi-file",
//           },
//           {
//             label: "Image",
//             icon: "pi pi-image",
//           },
//           {
//             label: "Video",
//             icon: "pi pi-video",
//           },
//         ],
//       },
//       {
//         label: "Open",
//         icon: "pi pi-folder-open",
//       },
//       {
//         label: "Print",
//         icon: "pi pi-print",
//       },
//     ],
//   },
//   {
//     label: "Edit",
//     icon: "pi pi-file-edit",
//     items: [
//       {
//         label: "Copy",
//         icon: "pi pi-copy",
//       },
//       {
//         label: "Delete",
//         icon: "pi pi-times",
//       },
//     ],
//   },
//   {
//     label: "Search",
//     icon: "pi pi-search",
//   },
//   {
//     separator: true,
//   },
//   {
//     label: "Share",
//     icon: "pi pi-share-alt",
//     items: [
//       {
//         label: "Slack",
//         icon: "pi pi-slack",
//       },
//       {
//         label: "Whatsapp",
//         icon: "pi pi-whatsapp",
//       },
//     ],
//   },
// ]);

const toggle = (event) => {
  menu.value.toggle(event);
};

const toggle2 = (event,data) => {
  selectedArchiveId.value = data.id;
  selectedArchive.value = data
  menu2.value.toggle(event);
};

const selectedDocument = ref([]);


const files = computed(()=>{
  let newarr = [];

  if(props.department.archive){
    for(let file of props.department.archive){
      let arr = {
        name: file?.file_path[0]?.file_name,
        size: file?.file_path[0]?.file_size,
        date: formatDate(file.updated_at),
        created: formatDate(file.created_at),
        uploaded_by: file?.file_path[0]?.updated_by_name,
        path: file?.file_path[0]?.thumbnails,
        files: file?.file_path,
        id: file.id
      }
      newarr.push(arr);
    }
  }
  return newarr;
})

const folders = computed(()=>{
let newfolder = []
if(props.department?.folder){
  for(let folder of props.department.folder){
    let arr = {
      name: folder.name,
      number: folder.number,
      date: formatDate(folder.updated_at),
      folder_size: folder.folder_size? folder.folder_size: 0,
    }
    newfolder.push(arr);
  }
}
return newfolder
})

const singleFile = (data) =>{
  console.log(data)
}

</script>

<style></style>