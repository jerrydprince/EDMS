<template>
    <main class="">
        <div class=" px-5 lg:px-7 lg:flex items-center justify-between pt-5  ">
            <div class="flex items-center justify-between  lg:w-[72%]">
                <div class=" flex items-center gap-2">
                    <!-- <Link :href="backbtn()"> -->
                        <a href="#" @click="backbtn"><i class="pi pi-arrow-left border p-2 rounded-md text-xs" ></i></a>
                    <!-- </Link> -->
                    <div class=" text-xl">
                        <p class="ml-4">{{ props.folder_name}}</p>
                    </div>
                </div>

                <button v-if="is_folder_open"
                    class="flex gap-2 items-center text-[#F04438] w-32  hover:bg-[#FFFBFA] hover:border hover:border-[#FECDCA] rounded-lg py-2 px-4"
                   >
                   <Link :href="'/dashboard/close-folder/'+folder_id" method="post" as="button"> <p>Close File</p></Link>
                </button>

                <button v-else
                    class="flex gap-2 items-center text-green-500 hover:bg-green-100  rounded-lg py-2 px-4"
                   >
                   <Link :href="'#'"> <p>Open File</p></Link>
                </button>


            </div>
            <div class=" flex items-center mt-5 lg:mt-0 gap-2   justify-between">
              
                <button v-if="props.is_folder_open"
                    class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4"
                    @click="createFolderModal = true">
                    <Icon icon="quill:folder-add" width="20" />
                    <p>Add File</p>
                </button>

                <div v-if="props.is_folder_open">
                    <Link :href="'/dashboard/archiving/'+page.props.branch+'/'+folder_id">
                    <button class="flex gap-2 items-center text-white bg-[#3B82F6] rounded-lg py-2 px-4">
                        <i class="pi pi-cloud-upload" style="font-size: 1rem"></i>
                        <p>Upload Documents</p>
                    </button>

                </Link>
                </div>
            </div>

        </div>
        <Divider class=" " />

        <section class=" lg:flex items-center justify-between px-5 lg:px-7">

            <div class="flex items-center justify-between  lg:w-[40%] text-sm">
                <div class="bg-gray-100 flex items-center rounded-md p-1">
                    <button class="bg-white shadow-md border-gray-300 rounded-lg py-1 px-4">
                        <p>All</p>
                    </button>
                    <button class="flex gap-1 items-center text-gray-400 rounded-lg py-1 px-4">
                        <p>Type</p>
                        <Icon icon="icon-park-outline:down" width="16" />
                    </button>
                </div>
                <button
                    class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4" @click="sortDocuments">
                    <svg v-if="!is_sorting"data-v-02777a1a="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 24 24" class="iconify iconify--tabler"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m6-8l-6-6m-6 6l6-6"></path></svg>
                    <svg v-else data-v-02777a1a="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" width="1em" height="1em" viewBox="0 0 24 24" class="iconify iconify--tabler"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m6-6l-6 6m-6-6l6 6"></path></svg>
                    <p>Name</p>
                </button>
                <button
                    class=" hidden lg:visible flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
                    <i class="pi pi-angle-down"></i>
                    <p>People</p>
                </button>
                <button @click="foldersGridView = !foldersGridView"
                    class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
                    <Icon v-if="foldersGridView" icon="lucide:list" width="20" />
                    <Icon v-else icon="system-uicons:grid" width="20" />
                    <p v-if="foldersGridView">List</p>
                    <p v-else>Grid</p>
                   
                </button>
            </div>

            <div class=" flex items-center border p-2 rounded-lg gap-2 text-sm mt-5 lg:mt-0">
                <i class="pi pi-search text-gray-300 " style="font-size: 1rem"></i>
                <input type="text"  v-model="search_text" placeholder="Search files" 
                    class="no-focus w-96 border-0 focus-visible:outline-none focus-within:outline-none focus:outline-none p-0 rounded-sm text-[#98A2B3]" />
            </div>
        </section>
        <Divider class=" " />

        <!-- ===============    File table    ============ -->

        
        <!-- <table class="w-full text-left mb-10" v-if="props.folder.subfolder && props.folder.subfolder.length > 0">
          <thead class=" bg-gray-50">
            <tr class="border text-[#667085]">
              <th class="p-3 pl-5 w-[70px]">
                <Checkbox class="mr-1" />
              </th>
              <th>File Name</th>
              <th>File Number</th>
              <th>File Type</th>
              <th>Last Modified</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border" v-for="(folder, key) in props.folder.subfolder">
              <td class="p-3 pl-5 py-3">
                <Checkbox :binary="true" class="mr-1" />

              </td>
              <td class="py-3">
                <div class="flex items-center font-medium gap-2">
                  <div class="p-2 rounded-full bg-gray-200">
                    <Link :href="'/dashboard/folder-view/' + branch_type + '/'+folder.my_id">
                    <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
                    </Link>
                  </div>
                  <Link :href="'/dashboard/folder-view/' + branch_type + '/' + folder.my_id">{{ folder.name }}</Link>
                </div>
              </td>
              <td class="py-3">{{ folder.number }}</td>
              <td>{{ folder?.file_type?.name }}</td>
              <td class="py-3">{{ formatDate(folder.updated_at) }}</td>
              <td class="py-3">
                <div class="card flex justify-content-center">
                  <div @click="toggle($event, key)" aria-haspopup="true" aria-controls="overlay_menu"
                    class="flex items-center font-normal h-full border border-gray-300 rounded-lg cursor-pointer">
                    <div class="h-full">
                      <Icon icon="majesticons:more-menu-vertical" width="30" />
                    </div>
                    <Menu ref="menu" id="overlay_menu" :model="items" :popup="true" />
                  </div>
                  <Link :href="'/dashboard/delete-folder/' + folder.id" class="px-3 text-red-500"
                    v-if="admin && admin.length > 0">delete</Link>
                </div>

              </td>
          
            </tr>
          </tbody>
        </table> -->
 

        <!-- =================== Files table   =============== -->

        <div v-if="files.length > 0 && !foldersGridView" class="pb-4">
            <h1 class=" text-lg font-semibold pl-7">Documents</h1>
            

            <ListVue :files="files" :documentsGridView="documentsGridView" @docs="SetselectedDocument" 
          @changeFormat="setFormatModal" @lock="setLock" @fileRename="setFileRename" @downloadFile="downloadFile"
          @selectedArchive="setSelectedArchive"  @branchDetails="setBranchDetails" @editMetadata="setEditmetadata"
          @addMetadata="setAddMetadata" :archives="props.archives" :search="search_text" :sort="sortOrder"/>
        </div>




        <!-- ============= Grid view   ======== -->

        <!-- =========== files  ======== -->
        <div v-if="files.length > 0 && foldersGridView" class=" mx-7 mt-16">

            <h1 class=" text-xl "> Documents</h1>
            <div class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8">


                <div v-for="file in filteredfiles" class="bg-gray-200 p-2 rounded-md relative" :key="i">
                     <div class="flex items-center justify-between">
                        <div class="p-2 rounded-full bg-white w-fit">
                            <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
                        </div>
                        <h1 class="truncate">{{ file?.name }}</h1>
                        <div class="flex gap-3 items-center">
                            <!-- <div @click="toggleMenu(i)" class="p-2 border cursor-pointer rounded-md">
                                <Icon icon="simple-line-icons:options-vertical" class="" />
                            </div> -->
                            <div @click="toggle2($event, file, key)" aria-haspopup="true" aria-controls="overlay_menu">
              <div class="flex items-center font-normal h-full">
                <div>
                  <div class="h-full">
                    <Icon icon="majesticons:more-menu-vertical" width="30" />
                  </div>
                  <TieredMenu ref="menu2" id="overlay_menu" :model="menuItems" popup>
                    <template #item="{ item, props, hasSubmenu }">

                      <a class="flex align-location-center" v-bind="props.action">
                        <!-- <span :class="item.icon" /> -->
                        <span v-html="item.icon"></span>
                        <span class="ml-2" @click="handleItemClick(item)">{{ item.label }}</span>
                        <!-- <Badge v-if="item.badge" class="ml-auto" :value="item.badge" /> -->
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
                        </div>

                        <!-- <div id="menu1" class="menu absolute right-5 z-50 " v-if="menuVisible && selectedDocumentId === i"
                            ref="modalRef">
                            <div class="w-[417px] h-[420px] relative " @click.stop
                                :class="isPositionChanged ? '-top-20' : 'top-52'">
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

                                <div class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
                                    :class="{ ' bottom-28': isSecondPosition }"
                                    :style="{ left: horizontalPosition ? '420px' : 'auto' }" v-if="submenu">
                                    <div class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2"
                                        v-for="(item, index) in submenuItem" :key="index" @click="handleMenuItem(item)">
                                        <Icon :icon="item.icon" />
                                        <p>{{ item.label }}</p>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div> 

                    

                    <div class="mt-2">
                        <img class="w-full rounded-md" :src="getFileExtension(file)" alt="Document image" />
                    </div>
                </div>
            </div>
        </div>
    </main>
    <CreateFolderDialog v-model="createFolderModal"  :url="'/dashboard/create-folder'" :folderId="folder_id" />
    <BranchDetails v-model="branchDetailsModal" :file="state.selectedArchive" :folder="props.folder" @updateDocument="handleUpdateDocument"/>
    <LockDocument v-model="LockDocumentModal" />
    <UnlockDocument v-model="UnlockDocumentModal" />
    <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
    <ChangeFormat v-model="ChangeFormatModal" :archive="state.selectedArchive"/>
    <PreviewFile v-if="togglePreview"  :togglePreviewFunction="togglePreviewState" :file="state.selectedArchive"/>
    <RenameFileDialog v-model="FileRenameModal" :archive="state.selectedArchive"/>
    <EditArchiveDocsMetadataDialog :file="state.selectedArchive" v-model="toggleMetadata" @formsubmited="FormSubmited"/>
    <AddArchiveDocsMetadataDialog :file="state.selectedArchive" v-model="toggleAddMetadata" @editMetadata="setEditmetadata" @formsubmited="AddFormSubmited"/>
</template>

<script setup>
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


const createFolderModal = ref(false);
const branchDetailsModal = ref(false);
const LockDocumentModal = ref(false)
const UnlockDocumentModal = ref(false)
const GenerateEncriptionModal = ref(false)
const foldersGridView = ref(false);
const ChangeFormatModal = ref(false);

const isPositionChanged = ref(false);
const isSecondPosition = ref(false)
const selectedDocumentId = ref(null);
const selectedFolderId = ref(null);
const horizontalPosition = ref(null);
const folderMenu = ref(false)
const toggleMetadata = ref(false)
const toggleAddMetadata = ref(false)

const menuVisible = ref(false);
const modalRef = ref(null)
const folderRef = ref(null)
const submenu = ref(false)
const page = usePage();
const props = defineProps(['archives', 'branch_id', 'is_folder_open', 'branch_type', 'folder_name', 'folder'])
const selectedArchiveId = ref();
//const state.selectedArchive = ref();
const menu = ref()
const emit = defineEmits(['fileRename', 'sortDocument'])
const folder_id = ref('');
const search_text = ref('')
const FileRenameModal = ref(false)
const togglePreview = ref(false)
const documentsGridView = ref(false);
const sortOrder = ref('')
const is_sorting = ref(false)

const state = reactive({
  selectedArchive: null
});

const FormSubmited = ()=>{
    toggleMetadata.value = false;
    router.reload({ only: ['archives'] })
}

const AddFormSubmited = ()=>{
    toggleAddMetadata.value = false;
    router.reload({ only: ['archives'] })
}

const setEditmetadata = ()=>{
    toggleMetadata.value = true
}

const setAddMetadata = ()=>{
    toggleAddMetadata.value = true;
}

const backbtn = ()=>{
    return window.history.back()
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

const getFileExtension = (file)=>{
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

const setBranchDetails = (data)=>{
  branchDetailsModal.value = data
}

const SetselectedDocument = (data)=>{
  selectedDocument.value = data.value
}

const setFileRename = (data)=>{
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
          console.log(data)
        }
      }
    ]
  }
]);


const setSelectedArchive = (data)=>{
  state.selectedArchive = data.value
}

const setLock =(data)=>{
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
    const response = await axios.get('/download-file', {
      params: { id: selectedArchiveId.value },
      responseType: 'blob'
    });

    // Extract the filename from the Content-Disposition header
    const contentDisposition = response.headers['content-disposition'];
    const fileName = contentDisposition.split('filename=')[1].replace(/"/g, '').trim();;

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

const setFormatModal = (data)=>{
  ChangeFormatModal.value = data
}



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

const sortDocuments = ()=>{
  is_sorting.value = !is_sorting.value

  if(is_sorting.value){
     sortOrder.value = 'asc'
  }else{
    sortOrder.value = 'desc'
  }
 
}

const getArchiveDocsVale = (data, search)=>{
   
   if(data){
    for(let archive of data){
       
        if(archive.archive_keys.toLowerCase() == search){
            return [{archive: archive.archive_values, id: archive.id}]
        }
    }
   }
}

const handleUpdateDocument = ()=>{
  router.reload({ only: ['archives'] })
}

const files = computed(()=>{
  let newarr = [];

  if(props.archives.data){
    for(let file of props.archives.data){
      let arr = {
        name: getArchiveDocsVale(file.archive_docs, 'title')?getArchiveDocsVale(file.archive_docs, 'title')[0].archive: file?.file_path[0]?.file_name,
        size: file?.file_path[0]?.file_size,
        date: formatDate(file.updated_at),
        uploaded_by: file?.file_path[0]?.updated_by_name,
        //path: (file.file_path && file.file_path.length > 0)? file?.file_path[0]?.thumbnails : file?.file_path[0]?.path,
        path: (file.file_path && file.file_path.length > 0)? file?.file_path[0]?.path: '',
        files: file?.file_path,
        id: file.id,
        amount: getArchiveDocsVale(file.archive_docs, 'amount')? getArchiveDocsVale(file.archive_docs, 'amount')[0]:'',
        title: getArchiveDocsVale(file.archive_docs, 'title')?getArchiveDocsVale(file.archive_docs, 'title')[0]: file?.file_path[0]?.file_name,
        description:getArchiveDocsVale(file.archive_docs, 'description')? getArchiveDocsVale(file.archive_docs, 'description')[0]: '',
        archive_docs_id: getArchiveDocsVale(file.archive_docs, 'title')? getArchiveDocsVale(file.archive_docs, 'title')[0]:'',
        archive_docs: file.archive_docs,
        department:file.department? file.department: [],
        division: file.division? file.division: [],
        unit: file.unit? file.unit: []
      }
      newarr.push(arr);
    }
  }
  return newarr;
})


const filteredfiles = computed(()=>{
    
    if(search_text.value !== ''){
        let found = files.value.filter((val)=>{
            return val.name.includes(search_text.value)
        })
        return found
    }
        return files.value
    
})

onMounted(()=>{
    let url = new URL(window.location.href);
    let folderid = url.pathname.split('/').pop()
    folder_id.value = folderid;
})
</script>

<style  scoped>
.p-submenu-list {
    background: red;
}

.no-focus:focus{
    outline: none;
}
</style>

