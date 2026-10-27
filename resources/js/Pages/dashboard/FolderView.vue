<template>
  <main class="">
    <div class="px-5 lg:px-7 lg:flex items-center justify-between pt-5">
      <div class="flex items-center gap-2">
        <a href="#" @click="backbtn"><i class="pi pi-arrow-left border border-gray-300 p-2 rounded-md text-xs text-gray-700 hover:bg-gray-50 transition-colors"></i></a>
        <div class="text-xl font-semibold text-gray-800 flex items-center flex-wrap gap-2.5">
          <p class="ml-4">{{ props.folder_name || (props.folder ? props.folder.name : '') }}</p>
          <span v-if="props.folder?.number" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-slate-100 text-slate-700 border border-slate-200">
            No: {{ props.folder.number }}
          </span>
          <span v-if="props.folder?.trace_id" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-mono font-medium bg-emerald-50 text-emerald-800 border border-emerald-200" title="Archived from Workflow Task">
            <Icon icon="fluent:document-arrow-right-20-regular" width="13" class="text-emerald-600" />
            Trace ID: {{ props.folder.trace_id }}
          </span>
        </div>
      </div>

      <div class="flex items-center mt-5 lg:mt-0 gap-3 justify-end">
        <button v-if="is_folder_open && !page.props.roles.includes('record manager')" @click.prevent="toggleCurrentFolderState" class="flex gap-2 items-center text-[#F04438] bg-white border border-[#FECDCA] hover:bg-[#FFFBFA] rounded-lg py-2 px-4 text-sm font-medium transition-colors shadow-sm">
          <span>Close File</span>
        </button>

        <button v-else-if="!is_folder_open && !page.props.roles.includes('record manager')" @click.prevent="toggleCurrentFolderState" class="flex gap-2 items-center text-green-600 bg-white border border-green-200 hover:bg-green-50 rounded-lg py-2 px-4 text-sm font-medium transition-colors shadow-sm">
          <span>Open File</span>
        </button>

        <button v-if="props.is_folder_open" class="flex gap-2 items-center text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg py-2 px-4 text-sm font-medium transition-colors shadow-sm" @click="createFolderModal = true">
          <Icon icon="quill:folder-add" width="18" />
          <span>Add File</span>
        </button>

        <div v-if="props.is_folder_open">
          <Link :href="'/edms/archiving/' + props.branch_name + '/' + folder_id" v-if="page.props.roles.includes('record manager')" class="flex gap-2 items-center text-white bg-primary hover:bg-primary/90 rounded-lg py-2 px-4 text-sm font-medium transition-colors shadow-sm">
            <i class="pi pi-cloud-upload" style="font-size: 1rem"></i>
            <span>Upload Documents</span>
          </Link>
          <Link :href="'/dashboard/archiving/' + props.branch_name + '/' + folder_id+'?branch_id='+props.branch.id" v-else class="flex gap-2 items-center text-white bg-primary hover:bg-primary/90 rounded-lg py-2 px-4 text-sm font-medium transition-colors shadow-sm">
            <i class="pi pi-cloud-upload" style="font-size: 1rem"></i>
            <span>Upload Documents</span>
          </Link>
        </div>
      </div>
    </div>
    <Divider class="mt-4 mb-4" />

    <section class="lg:flex items-center justify-between px-5 lg:px-7">
      <div class="flex flex-wrap items-center gap-3 text-sm">
        <div class="bg-gray-100 flex items-center rounded-lg p-1">
          <button class="flex items-center rounded-md px-4 py-1.5 transition-colors"
          @click="setActive(1)"
            :class="{ 'bg-white shadow-sm text-gray-800 font-medium': buttonToggle === 1, 'text-gray-500 hover:text-gray-700': buttonToggle !== 1 }"
          >
            <p>All</p>
          </button>
          <button class="flex gap-1.5 items-center rounded-md px-4 py-1.5 transition-colors"
          :class="{ 'bg-white shadow-sm text-gray-800 font-medium': buttonToggle === 2, 'text-gray-500 hover:text-gray-700': buttonToggle !== 2 }"
              @click="toggleFileBtn">
            <p>Document Type</p>
            <Icon icon="icon-park-outline:down" width="14" />
          </button>
          <TieredMenu
              ref="menuBtn"
              id="overlay_tmenu"
              :model="itemsBtn"
              popup
              style="width: 200px"
            >
              <template #item="{ item, props }">
                <a
                  v-ripple
                  class="flex align-items-center gap-2 px-[15px] py-[8px]"
                  v-bind="props.action"
                  @click="handleFilterOption(item.label)"
                >
                  <div v-if="item.icon">
                    <Icon :icon="item.icon" />
                  </div>
                  <span class="">{{ item.label }}</span>
                </a>
              </template>
            </TieredMenu>
        </div>
        
        <button class="flex gap-2 items-center text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg py-1.5 px-3 transition-colors shadow-sm"
          @click="sortDocuments">
          <svg v-if="!is_sorting" data-v-02777a1a="" xmlns="http://www.w3.org/2000/svg"
            xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" width="1em" height="1em"
            viewBox="0 0 24 24" class="iconify iconify--tabler">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 5v14m6-8l-6-6m-6 6l6-6"></path>
          </svg>
          <svg v-else data-v-02777a1a="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 24 24" class="iconify iconify--tabler">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 5v14m6-6l-6 6m-6-6l6 6"></path>
          </svg>
          <p>Name</p>
        </button>

        <button @click="foldersGridView = !foldersGridView"
          class="flex gap-2 items-center text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg py-1.5 px-3 transition-colors shadow-sm">
          <Icon v-if="foldersGridView" icon="lucide:list" width="18" />
          <Icon v-else icon="system-uicons:grid" width="18" />
          <p v-if="foldersGridView">List</p>
          <p v-else>Grid</p>
        </button>
      </div>

      <div class="flex items-center border border-gray-300 bg-white rounded-lg px-3 py-1.5 gap-2 text-sm mt-4 lg:mt-0 shadow-sm w-full lg:w-80">
        <i class="pi pi-search text-gray-400" style="font-size: 1rem"></i>
        <input type="text" v-model="search_text" placeholder="Search files"
          class="w-full border-0 bg-transparent focus:outline-none focus:ring-0 text-gray-700 placeholder-gray-400 p-0" />
      </div>
    </section>
    <Divider class=" " />

    <!-- ===============    Folder table    ============ -->
 <!-- Folders View -->
 <div  class="mb-10 mt-8 text-sm" v-if="folder.subfolder && folder.subfolder.length > 0">
  <h1 class=" text-lg font-semibold pl-7">Files</h1>
<table class="w-full text-left">
  <thead class=" bg-gray-50">
    <tr class="border text-[#667085]">
      <!-- <th class="p-3 pl-5 w-[70px]">
        <Checkbox class="mr-1" />
      </th> -->
      <th class="py-5 pl-4">File Name</th>
      <th class="py-5">File Number</th>
      <th class="py-5">File Type</th>
      <th class="py-5">Last Modified</th>
      <th class="py-5">Actions</th>
    </tr>
  </thead>
  <tbody>
    <tr class="border" v-for="(folder, key) in folder.subfolder">
  
      <td class="py-3 pl-4">
        <div class="flex items-center font-medium gap-2">
          <div class="p-2 rounded-full bg-gray-200">
            <template v-if="page.props.roles.includes('record manager')">
              <Link  :href="'/edms/folder-view/' + branch_name + '/' + folder.my_id">
                <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
              </Link>
            </template>
            <template v-else>
               <Link  :href="'/dashboard/folder-view/' + branch_name + '/' + folder.my_id+'/'+props.branch.id">
              <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
              </Link>
            </template>

          </div>
          <Link v-if="page.props.roles.includes('record manager')" :href="'/edms/folder-view/' + branch_name + '/' + folder.my_id">{{ folder.name }}</Link>
          <Link v-else  :href="'/dashboard/folder-view/' + branch_name + '/' + folder.my_id+'/'+props.branch.id">{{ folder.name }}</Link>
        </div>
      </td>
      <td class="py-3">{{ folder.number }}</td>
      <td>{{ folder?.file_type?.name }}</td>
      <td class="py-3" v-if="folder.file_type">{{ moment(folder?.file_type?.updated_at).format('DD MM YY') }}</td>
      <td class="py-3">
        <div class="card flex items-center justify-content-center gap-3">
            <div class="flex items-center gap-3">
              <div class="cursor-pointer text-blue-500 hover:text-blue-600" @click.stop.prevent="shareFolder(folder)" title="Share File">
                <Icon icon="material-symbols-light:share-outline" width="22" />
              </div>
              <div class="cursor-pointer" :class="folder.is_open ? 'text-gray-500 hover:text-gray-700' : 'text-red-500 hover:text-red-600'" @click.stop.prevent="toggleSubFolderState(folder)" v-if="admin && admin.length > 0" :title="folder.is_open ? 'Close File' : 'Open File'">
                <Icon :icon="folder.is_open ? 'material-symbols-light:lock-open-outline' : 'material-symbols-light:lock-outline'" width="22" />
              </div>
              <div class="cursor-pointer text-red-500 hover:text-red-600" @click.stop.prevent="deleteArchive(folder.id)" v-if="admin && admin.length > 0" title="Delete File">
                <Icon icon="mdi:delete-outline" width="22" />
              </div>
            </div>
        </div>
      </td>
    </tr>
  </tbody>
</table>

</div>

    


    <!-- =================== Files table / Grid   =============== -->

    <div v-if="files.length > 0" class="pb-4 mt-8">
      <h1 class=" text-lg font-semibold pl-7 mb-4">Documents</h1>

      <ListVue :files="files" :documentsGridView="foldersGridView" @docs="SetselectedDocument"
        @changeFormat="setFormatModal" @lock="setLock" @fileRename="setFileRename" @downloadFile="downloadFile"
        @selectedArchive="setSelectedArchive" @branchDetails="setBranchDetails" @editMetadata="setEditmetadata"
        @addMetadata="setAddMetadata" :archives="props.archives" :search="search_text" :sort="sortOrder" 
        @fileActivity="toggleFileActivity=true" @fileVersion="toggleFileVersion=true" @lockDocs ="handleLockDocument"
        @share="handleShare" @checkOut="handleCheckOut" @checkIn="handleCheckIn" :branch_type="props.branch_type" :folder_id="props.folder_id" :branch_id="props.branch_id" :fileType="props.folder?.file_type" />
    </div>
  </main>
  <CreateFolderDialog v-model="createFolderModal" :url="'/edms/create-folder'" :folderId="folder_id" />
  <BranchDetails v-model="branchDetailsModal" :file="state.selectedArchive" :folder="props.folder"
    @updateDocument="handleUpdateDocument" />
  <LockDocument v-model="LockDocumentModal" :file="state.selectedArchive" />
  <UnlockDocument v-model="UnlockDocumentModal" />
  <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
  <ChangeFormat v-model="ChangeFormatModal" :archive="state.selectedArchive" />
  <PreviewFile v-if="togglePreview" :togglePreviewFunction="togglePreviewState" :file="filePreviewToggle" @toggleVersion="toggleFileVersion = true"/>

  <!-- <PreviewFile v-if="togglePreviewFile" :togglePreviewFunction="togglePreviewState2" :file="filePreviewToggle" /> -->
  <RenameFileDialog v-model="FileRenameModal" :archive="state.selectedArchive" />
  <EditArchiveDocsMetadataDialog :file="state.selectedArchive" v-model="toggleMetadata" @formsubmited="FormSubmited" />
  <AddArchiveDocsMetadataDialog :file="state.selectedArchive" v-model="toggleAddMetadata"
    @editMetadata="setEditmetadata" @formsubmited="AddFormSubmited" />
  
  <fileActivity v-model="toggleFileActivity" :selectedArchive="state.selectedArchive" />
  <fileVersion v-model="toggleFileVersion" :selectedArchive="state.selectedArchive" @previewFile="setSelectedFileState" 
    @fileVersionDeleted="handleFileVersionDeleted"/>
  <share v-model="toggleShare" :archive="state.selectedArchive" />
  <ShareFolderModal v-model="toggleShareFolder" :folder="selectedFolderToShare" />
  <DeleteArchive v-model="isdelte_archive" :data="selectedArchiveId" :url="deleteUrl" />
</template>

<script setup>
import Swal from 'sweetalert2';

import { ref, defineProps, computed, defineEmits, onMounted, watch, reactive } from "vue";
//import Emptystate from "../../../assets/Emptystate.vue";
import CreateFolderDialog from "../../components/Modals/branches/CreateFolderDialog.vue";
import BranchDetails from "../../components/Modals/branches/BranchDetails.vue";
import LockDocument from "../../components/Modals/branches/LockDocument.vue";
import UnlockDocument from "../../components/Modals/branches/UnlockDocument.vue";
import GenerateEncriptionCode from "../../components/Modals/branches/GenerateEncriptionCode.vue";
import ChangeFormat from "../../components/Modals/branches/ChangeFormatModal.vue";
import { onClickOutside } from '@vueuse/core'
import { Link, usePage, router } from '@inertiajs/vue3';
import PreviewFile from "../../components/Modals/branches/PreviewFile.vue";

import ListVue from '../dashboard/partials/ListVue.vue';
import RenameFileDialog from "../../components/Modals/branches/RenameFileDialog.vue";
import EditArchiveDocsMetadataDialog from "../../components/Modals/EditArchiveDocsMetadataDialog.vue";
import AddArchiveDocsMetadataDialog from "../../components/Modals/AddArchiveDocsMetadataDialog.vue";
import fileActivity from "../../components/Modals/FileActivityModal.vue";
import fileVersion from "../../components/Modals/VersionModal.vue";
import share from "../../components/Modals/branches/Share.vue";
import ShareFolderModal from "../../components/Modals/branches/ShareFolder.vue";
import DeleteArchive from "../../components/DeleteArchive.vue";
import moment from "moment";

const createFolderModal = ref(false);
const branchDetailsModal = ref(false);
const LockDocumentModal = ref(false)
const UnlockDocumentModal = ref(false)
const GenerateEncriptionModal = ref(false)
const foldersGridView = ref(false);
const ChangeFormatModal = ref(false);
const toggleFileVersion = ref(false);

const isPositionChanged = ref(false);
const isSecondPosition = ref(false)
const selectedDocumentId = ref(null);
const selectedFolderId = ref(null);
const horizontalPosition = ref(null);
const folderMenu = ref(false)
const toggleMetadata = ref(false)
const toggleAddMetadata = ref(false)
const toggleFileActivity = ref(false);

const menuVisible = ref(false);
const modalRef = ref(null)
const folderRef = ref(null)
const submenu = ref(false)
const page = usePage();
const props = defineProps(['archives', 'branch_id', 'is_folder_open', 'branch_type', 'folder_name', 'folder', 'folder_id', 'branch_type','branch', 'branch_name'])
const selectedArchiveId = ref(null);
const menu = ref()
const emit = defineEmits(['fileRename', 'sortDocument'])
const folder_id = ref('');
const search_text = ref('')
const FileRenameModal = ref(false)
const togglePreview = ref(false)
const togglePreviewFile = ref(false)
const documentsGridView = ref(false);
const sortOrder = ref('')
const is_sorting = ref(false)
const filePreviewToggle = ref([])
const toggleShare = ref(false);
const toggleShareFolder = ref(false);
const selectedFolderToShare = ref(null);
const deleteUrl = ref(null);
const isdelte_archive = ref(false);
const buttonToggle = ref(1);
const menuBtn = ref()
const admin = page.props.roles ? page.props.roles.filter((data) => data == 'super admin' || data == 'admin') : [];

const deleteArchive = (id) => {
    deleteUrl.value = '/edms/delete-folder/' + id;
    selectedArchiveId.value = id;
    isdelte_archive.value = true;
}

const shareFolder = (folder) => {
    selectedFolderToShare.value = folder;
    toggleShareFolder.value = true;
}

const toggleCurrentFolderState = () => {
    if (props.is_folder_open) {
      Swal.fire({
        title: 'Confirm',
        text: "Are you sure you want to close this file? Users won't be able to upload documents into it anymore.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
      }).then((result) => {
        if (result.isConfirmed) {
        router.post('/edms/close-folder/' + props.folder_id, {}, { onSuccess: () => {} });
      }
      });
    } else {
      Swal.fire({
        title: 'Confirm',
        text: "Are you sure you want to open this file? Users will be able to upload documents into it again.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
      }).then((result) => {
        if (result.isConfirmed) {
        router.post('/edms/open-folder/' + props.folder_id, {}, { onSuccess: () => {} });
      }
      });
    }
}

const toggleSubFolderState = (folder) => {
    if (folder.is_open) {
      Swal.fire({
        title: 'Confirm',
        text: "Are you sure you want to close this file? Users won't be able to upload documents into it anymore.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
      }).then((result) => {
        if (result.isConfirmed) {
        router.post('/edms/close-folder/' + folder.id, {}, { onSuccess: () => {} });
      }
      });
    } else {
      Swal.fire({
        title: 'Confirm',
        text: "Are you sure you want to open this file? Users will be able to upload documents into it again.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
      }).then((result) => {
        if (result.isConfirmed) {
        router.post('/edms/open-folder/' + folder.id, {}, { onSuccess: () => {} });
      }
      });
    }
}

const state = reactive({
  selectedArchive: null
});

const handleFileVersionDeleted = ()=>{
  router.reload({only: ['archives']})
}

const setActive = (buttonNumber) => {
  buttonToggle.value = buttonNumber;
};

const toggleFileBtn = (event) => {
  menuBtn.value.toggle(event);
  buttonToggle.value = 2;
};

const FormSubmited = () => {
  toggleMetadata.value = false;
  router.reload({ only: ['archives'] })
}

const AddFormSubmited = () => {
  toggleAddMetadata.value = false;
  router.reload({ only: ['archives'] })
}

const handleShare = ()=>{
  toggleShare.value = true;
}

const setEditmetadata = () => {
  toggleMetadata.value = true
}

const setAddMetadata = () => {
  toggleAddMetadata.value = true;
}

const backbtn = () => {
  if (page.props.roles.includes('record manager')) {
    return router.get('/edms/branches');
  } else {
    let branchType = (props.branch_type || props.branch_name || 'department').toLowerCase();
    let deptId = props.folder?.department_id || 0;
    let divId = props.folder?.division_id || 0;
    let unitId = props.folder?.unit_id || 0;
    return router.get(`/dashboard/branches/${branchType}/${deptId}/${divId}/${unitId}`);
  }
}

const setSelectedFileState = (data)=>{
  filePreviewToggle.value = data.value
  togglePreview.value = true;
  toggleFileVersion.value = false;
}

const handleLockDocument = ()=>{
  LockDocumentModal.value = true;
}

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

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const togglePreviewState2 = () => {
  togglePreviewFile.value = !togglePreviewFile.value;
};

const getFileExtension = (file) => {
  // const f_path = file.path? file.path : file?.name
  // const parts = f_path.split('.');

  // const extension = parts.pop();

  // if(extension == 'jpg' || extension == 'jpeg' || extension == 'gif' || extension == 'png'){
  //     return page.props.base+'/storage'+file.path
  // }

  // if(extension == 'pdf'){
  //     return page.props.base+'/images/pdf.svg'
  // }

  // if(extension == 'doc' || extension == 'doctx'){
  //     return page.props.base+'/images/word.svg'
  // }

  // if(extension == 'xlx' || extension == 'xlsx'){
  //     return page.props.base+'/images/excel.svg'
  // }

}

const itemsBtn = ref([
  {
    label: "Filter",
    icon: "octicon:filter-16",
  },
  // {
  //   label: "Super Admin",
  // },
  // {
  //   label: "Administrator",
  // },
  // {
  //   label: "Records Manager",
  // },
  // {
  //   label: "Super User",
  // },
  // {
  //   label: "Director",
  // },
  // ,
  // {
  //   label: "Division Head",
  // },
  // {
  //   label: "Unit Head",
  // },
  // {
  //   label: "Data Entry Personnel",
  // },
]);

const setBranchDetails = (data) => {
  branchDetailsModal.value = data
}

const SetselectedDocument = (data) => {
  selectedDocument.value = data.value
}

const setFileRename = (data) => {
  FileRenameModal.value = data
}

const toggle = (event, key) => {
  menu.value[key].toggle(event);
};

const items = ref([
  {
    label: '',
    items: [
      {
        label: 'Open File',
        icon: 'pi pi-folder-open'
      },
      {
        label: 'Close File',
        icon: 'pi pi-folder'
      },
      {
        label: 'Delete',
        icon: 'pi pi pi-trash',
        command: (data) => {
         
        }
      }
    ]
  }
]);


const setSelectedArchive = (data) => {
  state.selectedArchive = data.value;
  selectedArchiveId.value = data.value?.id;
}

const setLock = (data) => {
  LockDocumentModal.value = data
}

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

const downloadFile = async () => {
  try {
    const archiveId = selectedArchiveId.value || state.selectedArchive?.id;
    const fpId = state.selectedArchive?.file_path_id;
    let url = (page.props.base || '') + '/download-file?id=' + archiveId;
    if (fpId) {
      url += '&file_path_id=' + fpId;
    }
    window.location.href = url;
  } catch (error) {
    console.error('Error downloading file:', error);
  }
};

const setFormatModal = (data) => {
  ChangeFormatModal.value = data
}



const selectedDocument = ref([]);

const formatDate = (inputDate) => {
  var date = new Date(inputDate);
  var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

  var day = date.getDate();
  var month = months[date.getMonth()];
  var year = date.getFullYear();

  day = day < 10 ? '0' + day : day;

  var formattedDate = day + ' ' + month + ' ' + year.toString().substr(-2);

  return formattedDate;
}

const sortDocuments = () => {
  is_sorting.value = !is_sorting.value

  if (is_sorting.value) {
    sortOrder.value = 'asc'
  } else {
    sortOrder.value = 'desc'
  }

}

const getArchiveDocsVale = (data, search) => {

  if (data) {
    for (let archive of data) {

      if (archive.archive_keys.toLowerCase().includes(search)) {
        return [{ archive: archive.archive_values, id: archive.id }]
      }
    }
  }
}

const handleUpdateDocument = () => {
  router.reload({ only: ['archives'] })
  branchDetailsModal.value = false;
}

const handleCheckOut = (data) => {
  router.post('/checkout-document', { id: data.id }, {
    preserveScroll: true,
    onSuccess: () => {
      let url = (page.props.base || '') + '/download-file?id=' + data.id;
      if (data.file_path_id) {
        url += '&file_path_id=' + data.file_path_id;
      }
      window.location.href = url;
    }
  });
};

const handleCheckIn = (data) => {
  router.post('/checkin-document', { id: data.id }, {
    preserveScroll: true,
    onSuccess: () => {
      // You can add a success notification here if you have one
    }
  });
};

const files = computed(() => {
  let newarr = [];
  
  const fileTypeHeadings = props.folder?.file_type?.headings || ["Title", "Description", "Amount"];

  if (props.archives.data) {
    for (let file of props.archives.data) {
      if (file.file_path && file.file_path.length > 0) {
        file.file_path.forEach((fp, fpIndex) => {
          let f_path = fp.path || fp.file_name || '';
          let resultUrl = 'vscode-icons:default-file';
          
          if (f_path) {
              const parts = f_path.split('.');
              const extension = parts.pop().toLowerCase();

              if (['jpg', 'jpeg', 'gif', 'png', 'webp', 'svg'].includes(extension)) {
                  resultUrl = 'vscode-icons:file-type-image';
              } else if (extension === 'pdf') {
                  resultUrl = 'vscode-icons:file-type-pdf2';
              } else if (['doc', 'docx'].includes(extension)) {
                  resultUrl = 'vscode-icons:file-type-word';
              } else if (['xls', 'xlsx', 'csv'].includes(extension)) {
                  resultUrl = 'vscode-icons:file-type-excel';
              }
          }

          let arr = {
            name: fp.file_name || file?.name || ('Document ' + (fpIndex + 1)),
            size: fp.file_size,
            date: formatDate(fp.updated_at || file.updated_at),
            uploaded_by: fp.updated_by_name || (file.department?.title || 'System'),
            path: fp.path,
            files: [fp],
            all_files: file.file_path,
            file_path_id: fp.id,
            id: file.id,
            archive_docs: file.archive_docs,
            department: file.department ? file.department : null,
            division: file.division ? file.division : null,
            unit: file.unit ? file.unit : null,
            file_activity: file.file_activity,
            file_version: file.version,
            is_locked: file.is_locked,
            is_checked_out: file.is_checked_out,
            checked_out_by: file.checked_out_by,
            dynamic_data: {},
            icon_url: resultUrl
          };

          // Automatically map headings to archive_docs values or file attributes
          fileTypeHeadings.forEach((heading, hIdx) => {
              if (heading) {
                  const key = heading.toLowerCase().replace(/ /g, '_');
                  let matchedMeta = getArchiveDocsVale(file.archive_docs, key);
                  
                  if (matchedMeta && key.includes('amount')) {
                      let val = matchedMeta[0].archive;
                      if (val) {
                          let num = parseFloat(val.toString().replace(/,/g, ''));
                          if (!isNaN(num)) {
                              val = num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                          }
                      }
                      arr.dynamic_data[heading] = val;
                  } else if (matchedMeta && fpIndex === 0) {
                      arr.dynamic_data[heading] = matchedMeta[0].archive;
                  } else if (hIdx === 0 || key.includes('title') || key.includes('name')) {
                      arr.dynamic_data[heading] = arr.name;
                  } else if (key.includes('size')) {
                      arr.dynamic_data[heading] = arr.size;
                  } else if (key.includes('date')) {
                      arr.dynamic_data[heading] = arr.date;
                  } else if (matchedMeta) {
                      arr.dynamic_data[heading] = matchedMeta[0].archive;
                  } else {
                      arr.dynamic_data[heading] = '';
                  }
                  
                  arr[key] = arr.dynamic_data[heading];
              }
          });

          newarr.push(arr);
        });
      } else {
        // Fallback for archives without file_path
        let arr = {
          name: file.name || 'Untitled Document',
          size: '0 KB',
          date: formatDate(file.updated_at),
          uploaded_by: file.uploaded_by,
          path: '',
          files: [],
          id: file.id,
          archive_docs: file.archive_docs,
          dynamic_data: {},
          icon_url: 'vscode-icons:default-file'
        };
        newarr.push(arr);
      }
    }
  }
  return newarr;
});


const filteredfiles = computed(() => {

  if (search_text.value !== '') {
    // let found = files.value.filter((val) => {
    //   return val.name.includes(search_text.value)
    // })
    // return found
    
      
  }
  return files.value

})

watch(() => props.folder_id, (newVal) => { folder_id.value = newVal }, { immediate: true })
</script>

<style scoped>
.p-submenu-list {
  background: red;
}

.no-focus:focus {
  outline: none;
}

.activeButton {
  background-color: white;
  border-radius: 5px;
  box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
}
</style>

