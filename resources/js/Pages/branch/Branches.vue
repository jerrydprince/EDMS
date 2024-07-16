<template>
  <main class="py-4 lg:py-6 text-sm  ">
    <section
      class="flex px-4 pb-6 border-b lg:px-7 flex-col md:flex-row items-start md:items-center justify-between cursor-pointer">
      <p class="text-3xl text-gray-800 font-semibold">
        {{props.department.title}}
      </p>
      <div class="flex items-center gap-5 mt-4 md:mt-0">
        <button
          class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4 cursor-pointer">
          <Icon icon="gala:add" width="25" />
          <p>Create Workflow</p>
        </button>
          <Link :href="'/dashboard/archiving/'+department.id" class="flex gap-2 items-center text-white bg-[#3B82F6] rounded-lg py-2 px-4">
          <i class="pi pi-cloud-upload" style="font-size: 1rem"></i>
          <p>Upload Files</p>
          </Link>
      </div>
    </section>

    <section class="mt-10 px-4 lg:px-7">
      <div class="grid xl:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-8">
        <div class="border border-solid border-border_primary py-3 rounded-lg w-full">
          <div class="border-b pl-3">
            <p class="text-3xl font-semibold">{{ department.user_count }}</p>
            <p class="text-gray-800 mb-3 mt-2 font-medium text-base">Member(s)</p>
          </div>
          <div class="flex items-center justify-between px-3 pt-3">
            <div class="text-sm flex items-center">
              <div class="bg-green-100 rounded-lg p-1 mr-2">
                <Icon icon="iconamoon:trend-up-light" color="green" width="20" />
              </div>
              10+ This month
            </div>
            <div
              class="border border-solid w-10 h-10 border-border_primary rounded-full flex items-center justify-center">
              <Icon icon="humbleicons:users" width="20" />
            </div>
          </div>
        </div>
        <div class="border border-solid border-border_primary py-3 rounded-lg w-full">
          <div class="border-b pl-3">
            <p class="text-3xl font-semibold">{{department.archive_count}}</p>
            <p class="text-gray-800 mb-3 mt-2 font-medium text-base">
              Document(s)
            </p>
          </div>
          <div class="flex items-center justify-between px-3 pt-3">
            <div class="text-sm flex items-center">
              <div class="bg-red-100 rounded-lg p-1 mr-2">
                <Icon icon="iconamoon:trend-down-light" color="red" width="20" />
              </div>
              50+ This month
            </div>
            <div
              class="border border-solid w-10 h-10 border-border_primary rounded-full flex items-center justify-center">
              <Icon icon="tabler:file" width="20" />
            </div>
          </div>
        </div>
        <div class="border border-solid border-border_primary py-3 rounded-lg w-full">
          <div class="border-b pl-3">
            <p class="text-3xl font-semibold">3</p>
            <p class="text-gray-800 mb-3 mt-2 font-medium text-base">
              Completed Workflow
            </p>
          </div>
          <div class="flex items-center justify-between px-3 pt-3">
            <div class="text-sm flex items-center">
              <div class="bg-green-100 rounded-lg p-1 mr-2">
                <Icon icon="iconamoon:trend-up-light" color="green" width="20" />
              </div>
              10+ This month
            </div>
            <div
              class="border border-solid w-10 h-10 border-border_primary rounded-full flex items-center justify-center">
              <Icon icon="carbon:flow" width="20" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Folders -->
    <section class="mt-8">
      <div class="px-7 border-b flex items-center justify-between pb-2">
        <h1 class="font-medium text-xl flex items-center gap-1">
          Folders
          <Icon icon="icon-park:down" width="20" />
        </h1>

        <div class="flex items-center gap-5 text-sm">
          <button @click="foldersGridView = !foldersGridView"
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon v-if="foldersGridView" icon="lucide:list" width="20" />
            <Icon v-else icon="system-uicons:grid" width="20" />
            <p v-if="foldersGridView">List</p>
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
      <div v-if="department.folder && foldersGridView"
        class="grid xl:grid-cols-4 px-7 my-4 md:grid-cols-2 grid-cols-1 gap-8">

        <div v-for="folder in department.folder" class="rounded-md flex   items-center justify-between hover:bg-gray-100 p-3" >

          <Link href="/branches-folder">
            <div class=" border rounded-md  p-2 bg-gray-100 cursor-pointer  ">
              <img src="/assets/images/folder.svg" alt="">
            </div>
          </Link>
          <div>
            <h1 class="text-lg font-medium ">{{folder.name}}</h1>
            <div class="flex items-center gap-3 mt-1">
              <p>{{ folder?.file_count? folder.file_count: 0 }} File(s)</p>
              <div class="w-fit py-1 px-2 rounded-2xl bg-green-200 text-xs text-green-800">
                {{folder.is_open? "Open": "Closed"}}
              </div>
            </div>
          </div>

          <div class="cursor-pointer">
            <Icon icon="iconamoon:menu-kebab-vertical-bold" width="25" />
          </div>
        </div>
      </div>



      <!-- Folders List View -->
      <div v-if="folders.length > 0 && !foldersGridView" class="mt-8 px-7 text-sm">
        <DataTable v-model:selection="selectedFolder" paginator :rows="6" :value="folders" dataKey="id" :pt="{
          table: {
            class: 'w-full border border-border_primary border-solid mb-6',
          },
        }" tableStyle="min-width: 60rem; border-radius: 30px;">
          <Column selectionMode="multiple" headerStyle="width: 2rem; color: #667085;"></Column>
          <Column field="name" header="Folder Name" :pt="{
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
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>
          <!-- <Column field="size" header="Folder Size" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column> -->
          <Column field="date" header="Last Modified" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
          </Column>

          <Column field="actions" header="Actions" :pt="{
            bodyCell: { class: 'text-gray-700' },
          }" headerStyle="color: #667085; font-weight: 500;">
            <template #body="{ data }">
              <div class="flex items-center gap-4">
                <!-- <div @click="toggle" aria-haspopup="true" aria-controls="overlay_menu" class="flex">
                  <div class="flex items-center font-normal h-full border border-gray-300 rounded-lg">
                    <div>
                      <div class="h-full">
                        <Icon icon="majesticons:more-menu-vertical" width="30" />
                      </div>
                      <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true">
                        <template #item="{ item }">
                          <div v-if="data.category !== item.id" class="flex gap-2 px-3 py-2 items-center">
                            <i :class="item.icon" />
                            <p>{{ item.label }}</p>
                          </div>
                        </template>
                      </Menu>
                    </div>
                  </div>
                </div> -->

                <div class="cursor-pointer">
                  <Icon icon="uiw:delete" width="16" />
                </div>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </section>

    <!-- Documents -->
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
            <EmptyState class="m-auto" >
            <p class="mb-1 mt-3">No Records Yet</p>
            <p class="text-gray-500 font-light">
              Documents uploaded to this branch would show up here
            </p>
            <div
              class="mt-3 m-auto w-56 flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4 cursor-pointer">
              <Icon icon="mynaui:cloud-upload" width="25" />
             
              <Link href="/archiving">Upload Document</Link>
            </div>
            </EmptyState>
          </div>
        </div>

        <!-- list view table -->
        <div v-if="(files && files.length) > 0 && documentsGridView"
          class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8 ">
          <div v-for="file in files" class="bg-gray-200 p-2 rounded-md" >
            <div class="flex items-center justify-between">
              <div class="p-2 rounded-full bg-white w-fit">
                <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
              </div>
              <h1 class="truncate">{{file.name}}</h1>
              <div>
                <Icon icon="majesticons:more-menu-vertical" width="30" />
              </div>
            </div>

            <div class="mt-2">
              <img class="w-full rounded-md" :src="page.props.base+'/storage'+file.path" alt="Document image" />
            </div>
          </div>
        </div>

          <ListVue :files="files" :documentsGridView="documentsGridView" @docs="SetselectedDocument" 
          @changeFormat="setFormatModal" @lock="setLock" @fileRename="setFileRename" @downloadFile="downloadFile"
          @selectedArchive="setSelectedArchive" @branchDetails="setBranchDetails"/>
      </div>
    </section>

    <CreateFolderDialog v-model="createFolderModal" :url="'/dashboard/create-folder'" :department="props.department"/>
    <LockDocument v-model="LockDocumentModal" />
    <UnlockDocument v-model="UnlockDocumentModal" />
    <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
    <ChangeFormat v-model="ChangeFormatModal" :archive="selectedArchive" />
    <RenameFileDialog v-model="FileRenameModal" :archive="selectedArchive"/>
    <BranchDetails v-model="branchDetailsModal" :file="selectedArchive"/>

  </main>
</template>

<script setup>
import { ref, defineProps, computed } from "vue";
import { Link, router, usePage } from '@inertiajs/vue3'
import EmptyState from "../../components/EmptyState.vue";
import CreateFolderDialog from "../../components/Modals/branches/CreateFolderDialog.vue";
import BranchDetails from "../../components/Modals/branches/BranchDetails.vue";
import LockDocument from "../../components/Modals/branches/LockDocument.vue";
import UnlockDocument from "../../components/Modals/branches/UnlockDocument.vue";
import GenerateEncriptionCode from "../../components/Modals/branches/GenerateEncriptionCode.vue";
import ChangeFormat from "../../components/Modals/branches/ChangeFormatModal.vue"
import RenameFileDialog from "../../components/Modals/branches/RenameFileDialog.vue";
import ListVue from '../dashboard/partials/ListVue.vue';
import axios from "axios";

const createFolderModal = ref(false);
const branchDetailsModal = ref(false);
const LockDocumentModal = ref(false)
const UnlockDocumentModal = ref(false)
const GenerateEncriptionModal = ref(false)
const foldersGridView = ref(true);
const documentsGridView = ref(false);
const ChangeFormatModal = ref(false);
const menu = ref(null);
const menu2 = ref();
const selectedArchiveId = ref();
const selectedArchive = ref();
const FileRenameModal = ref(false);
const page = usePage()

const props = defineProps(['department'])

const menuItems = ref([
  {
    label: 'Preview',
    icon: 'pi pi-eye',
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



const toggle = (event) => {
  menu.value.toggle(event);
};
const toggle2 = (event,data) => {
  selectedArchiveId.value = data.id;
  selectedArchive.value = data
  menu2.value.toggle(event);
};
const selectItem = (data) => {
  branchDetailsModal.value = true;
};


const selectedFolder = ref([]);

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
]);
const selectedDocument = ref([]);

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

const deleteArchive = (data)=>{
  let id = data.id
  router.get('/delete-archive',{id:id, only: ['department']}, {onSuccess:page=>{
    //console.log(page)
  }, preserveScroll: (page) => console.log(page)})
}

const files = computed(()=>{
  let newarr = [];

  if(props.department.archive){
    for(let file of props.department.archive){
      let arr = {
        name: file?.file_path[0]?.file_name,
        size: file?.file_path[0]?.file_size,
        date: formatDate(file.updated_at),
        uploaded_by: file?.file_path[0]?.updated_by_name,
        path: file?.file_path[0]?.thumbnails,
        id: file.id,
        files: file?.file_path,
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
          date: formatDate(folder.updated_at)
        }
        newfolder.push(arr);
      }
    }
    return newfolder
})
</script>

<style  scoped>
.p-submenu-list {
  background: red;
}
</style>