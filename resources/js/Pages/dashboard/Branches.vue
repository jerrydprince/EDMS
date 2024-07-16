<template>
  <main>
    <div class=" flex items-center px-7 py-5 justify-between w-full">
      <p class="text-3xl text-gray-800 font-semibold">
        {{ props.branch.title }} {{ props.branch_name }}
      </p>

    </div>
    <Divider class="-mt-0.5" />


    <div class=" px-7 mt-10">
      <div class=" flex items-center w-full justify-between">
        <!-- <h1 class=" text-lg font-semibold">Files</h1> -->
        <div class="grow-[2] pr-4 w-[70%]">
          <input type="text" name="" v-model="search" class="border w-full border-gray-300 rounded-lg p-2" id="" placeholder="Search for files">
        </div>
        <div class=" flex items-center gap-5">
          <!-- <button @click="folderGrid = !folderGrid"
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon v-if="folderGrid" icon="lucide:list" width="20" />
            <Icon v-else icon="system-uicons:grid" width="20" />
            <p v-if="folderGrid">List</p>
            <p v-else>Grid</p>
          </button> -->

          <button @click="createFolderModal = true"
            class="flex gap-2 items-center text-gray-700 border-gray-300 border-solid border rounded-lg py-2 px-4">
            <Icon icon="quill:folder-add" width="20" />
            <p>Add File</p>
          </button>
        </div>
      </div>


      <!-- Folders View -->
      <div  class="mb-8 mt-8 text-sm">

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
            <tr class="border" v-for="(folder, key) in folders">
              <template v-if="!folder.is_sub_folder">
              <!-- <td class="p-3 pl-5 py-3">
                <Checkbox :binary="true" class="mr-1" />

              </td> -->
              <td class="py-3 pl-4">
                <div class="flex items-center font-medium gap-2">
                  <div class="p-2 rounded-full bg-gray-200">
                    <Link :href="'/data-entry/folder-view/' + branch_name + '/' + folder.id">
                    <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
                    </Link>
                  </div>
                  <Link :href="'/data-entry/folder-view/' + branch_name + '/' + folder.id">{{ folder.name }}</Link>
                </div>
              </td>
              <td class="py-3">{{ folder.number }}</td>
              <td>{{ folder.file_type }}</td>
              <td class="py-3">{{ folder.date }}</td>
              <td class="py-3">
                <div class="card flex justify-content-center">
                  <div @click="toggle($event, key)" aria-haspopup="true" aria-controls="overlay_menu"
                    class="flex items-center font-normal h-full border border-gray-300 rounded-lg cursor-pointer">
                    <div class="h-full">
                      <Icon icon="majesticons:more-menu-vertical" width="30" />
                    </div>
                    <Menu ref="menu" id="overlay_menu" :model="items" :popup="true" />
                  </div>
                  <Link :href="'/data-entry/delete-folder/' + folder.id" class="px-3 text-red-500"
                    v-if="admin && admin.length > 0">delete</Link>
                </div>

              </td>
            </template>
            </tr>
          </tbody>
        </table>
        <pagination :archives="pageFolders"/>
      </div>
    </div>
    <section class="mt-8">


    </section>

    <CreateFolderDialog v-model="createFolderModal" :url="'/data-entry/create-folder'" :folderId="''"/>
    <BranchDetails v-model="branchDetailsModal" :file="selectedArchive" />
    <LockDocument v-model="LockDocumentModal" />
    <UnlockDocument v-model="UnlockDocumentModal" />
    <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
    <ChangeFormat v-model="ChangeFormatModal" :archive="selectedArchive" />
    <PreviewFile v-if="togglePreview" :togglePreviewFunction="togglePreviewState" :file="selectedArchive" />
    <RenameFileDialog v-model="FileRenameModal" :archive="selectedArchive" />
  </main>
</template>
<script setup>
import { ref, defineProps, computed, watch, watchEffect } from 'vue';

import CreateFolderDialog from "../../components/Modals/branches/CreateFolderDialog.vue";
import BranchDetails from "../../components/Modals/branches/BranchDetails.vue";
import LockDocument from "../../components/Modals/branches/LockDocument.vue";
import UnlockDocument from "../../components/Modals/branches/UnlockDocument.vue";
import GenerateEncriptionCode from "../../components/Modals/branches/GenerateEncriptionCode.vue";
import ChangeFormat from "../../components/Modals/branches/ChangeFormatModal.vue";
import PreviewFile from "../../components/Modals/branches/PreviewFile.vue";
import { onClickOutside } from "@vueuse/core";
import { Link, usePage, router } from '@inertiajs/vue3';
import ListVue from '../dashboard/partials/ListVue.vue';
import RenameFileDialog from "../../components/Modals/branches/RenameFileDialog.vue";
import pagination from './partials/pagination.vue';
import  debounce  from 'lodash.debounce';

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
const user = page.props.user;
const admin = page.props.roles.filter((data) => data == 'super admin')
//const allFolders = toRef(props, 'folder');

const props = defineProps(['branch', 'branch_name', 'folder']);
const fetchedFolders = ref([]);
const pageFolders = ref([]);

const userFolders = [];
const search = ref('')


const menu = ref();
const items = ref([
  {
    label: '',
    // items: [
    //   {
    //     label: 'Open File',
    //     icon: 'pi pi-folder-open'
    //   },
    //   {
    //     label: 'Close File',
    //     icon: 'pi pi-folder'
    //   },
    //   {
    //     label: 'Delete',
    //     icon: 'pi pi pi-trash',
    //     command: (data) => {
    //       console.log(data)
    //     }
    //   }
    // ]
  }
]);

const toggle = (event, key) => {
  console.log(menu.value)
  console.log(key)
  menu.value[key].toggle(event);
};

watchEffect(()=>{
  pageFolders.value = props.folder
})

const togglePreviewState = () => {
  togglePreview.value = !togglePreview.value;
};

const SetselectedDocument = (data) => {
  selectedDocument.value = data.value
}

const setSelectedArchive = (data) => {
  selectedArchive.value = data.value
}

const setFormatModal = (data) => {
  ChangeFormatModal.value = data
}

const setBranchDetails = (data) => {
  branchDetailsModal.value = data
}

const setFileRename = (data) => {
  FileRenameModal.value = data
}

const setLock = (data) => {
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

const FileItems = ref([
  {
    label: 'Options',
    items: [
      {
        label: 'Open File',
        icon: 'pi pi-refresh'
      },
      {
        label: 'Close Folder',
        icon: 'pi pi-upload'
      },
      {
        label: 'Delete',
        icon: ''
      }
    ]
  }
]);


const menuItems = ref([
  {
    label: 'Preview',
    icon: 'pi pi-eye',
    command: () => {
      togglePreview.value = true;
    }
  },
  {
    label: 'Rename',
    icon: 'pi pi-pencil',
    command: () => {
      FileRenameModal.value = true;
    }
  },
  {
    label: 'Download',
    icon: 'pi pi-download',
    command: () => {
      downloadFile()
    }
  },
  {
    label: 'Duplicate',
    icon: 'pi pi-clone',
    command: () => {
      router.post('/duplicate-file', { id: selectedArchiveId.value }, {
        onSuccess: page => {
          console.log(page)
        }
      })
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
        command: () => {
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



const toggle2 = (event, data) => {
  selectedArchiveId.value = data.id;
  selectedArchive.value = data
  menu2.value.toggle(event);
};

const selectedDocument = ref([]);

const getArchiveDocsVale = (data, search) => {

  if (data) {
    for (let archive of data) {
      console.log(archive.archive_keys)
      if (archive.archive_keys.toLowerCase() == search) {
        return archive.archive_values;
      }
    }
  }
}


// const files = computed(() => {
//   let newarr = [];

//   if (props.branch.archive) {
//     for (let file of props.branch.archive) {
//       let arr = {
//         name: file?.file_path[0]?.file_name,
//         size: file?.file_path[0]?.file_size,
//         date: formatDate(file.updated_at),
//         created: formatDate(file.created_at),
//         uploaded_by: file?.file_path[0]?.updated_by_name,
//         path: file?.file_path[0]?.thumbnails,
//         files: file?.file_path,
//         id: file.id,
//         amount: getArchiveDocsVale(file.archive_docs, 'amount'),
//         title: getArchiveDocsVale(file.archive_docs, 'title') ? getArchiveDocsVale(file.archive_docs, 'title') : file?.file_path[0]?.file_name,
//         description: getArchiveDocsVale(file.archive_docs, 'description')
//       }
//       newarr.push(arr);
//     }
//   }
//   return newarr;
// })

// const getFileType = (file) => {
//   let files = [];
//   for (let i = 0; i < file.length; i++) {
//     files.push(file[i].name);
//   }
//   return files.join()
// }

// const folderSearch = ()=>{
//   let newfolder = []

//     axios.get('/folder-search',{params:{search: search.value}}).then((response)=>{
//       let val = response.data.data
      
//       for (let folder of val) {
//         let arr = {
//             name: folder.name,
//             number: folder.number,
//             date: formatDate(folder.updated_at),
//             f_z: folder.folder_size ? folder.folder_size.split('')[0] : 0,
//             folder_size: folder.folder_size ? folder.folder_size : 0,
//             file_type: folder.file_type?.name,
//             id: folder.id,
//             is_sub_folder: folder.is_sub_folder,
//             sub_folder: folder.is_sub_folder? folder.subfolder.path: [],
//             type: folder.type
//           }
//           newfolder.push(arr);
//         }
//     })

//     return newfolder;
// }


// const folders = computed(() => {
//   let newfolder = []
//   if (props.folder && props.folder.data && search.value =='') {
//     for (let folder of props.folder.data) {
//       let arr = {
//         name: folder.name,
//         number: folder.number,
//         date: formatDate(folder.updated_at),
//         f_z: folder.folder_size ? folder.folder_size.split('')[0] : 0,
//         folder_size: folder.folder_size ? folder.folder_size : 0,
//         file_type: folder.file_type?.name,
//         id: folder.id,
//         is_sub_folder: folder.is_sub_folder,
//         sub_folder: folder.is_sub_folder? folder.subfolder.path: [],
//         type: folder.type
//       }
//       newfolder.push(arr);
//     }
//   }
//   if(search.value !=''){
//     // return newfolder.filter((item => {
//     //   return (item.name.toLowerCase().includes(search.value.toLowerCase().trim())||
//     //     item.number.toLowerCase().includes(search.value.toLowerCase().trim())||
//     //     item.file_type.toLowerCase().includes(search.value.toLowerCase().trim())||
//     //     item.date.toLowerCase().includes(search.value.toLowerCase().trim()))
//     // }))
//     return folderSearch()
//   }else{
//     return newfolder;
//   }
  
// })

const fetchFolders = async () => {
      try {
        //const response = await axios.get('/data-entry/branches', { params: { search: search.value } });
        let response = []
          await router.get('/data-entry/branches', { search: search.value },{
            onSuccess: page =>{
              response = page
              
                const newFolders = page.props.folder.data.map(folder => ({
                name: folder.name,
                number: folder.number,
                date: formatDate(folder.updated_at),
                f_z: folder.folder_size ? folder.folder_size.split('')[0] : 0,
                folder_size: folder.folder_size ? folder.folder_size : 0,
                file_type: folder.file_type?.name,
                id: folder.id,
                is_sub_folder: folder.is_sub_folder,
                sub_folder: folder.is_sub_folder ? folder.subfolder.path : [],
                type: folder.type
              }));

              fetchedFolders.value = newFolders;
            },
            preserveState: true, // This prevents a full page reload
            only: ['folder'] // Specify the data you want to update
          } );
     
      } catch (error) {
        console.error('Error fetching folders:', error);
      }
    };

    const debouncedFetchFolders = debounce(fetchFolders, 300);

    // Watch for changes in the search input and debounce the API call
    watch(search, (newSearch) => {
      if (newSearch !== '') {
        debouncedFetchFolders();
      }
    });

    const folders = computed(() => {
      if (search.value === '' && props.folder && props.folder.data) {
        return props.folder.data.map(folder => ({
          name: folder.name,
          number: folder.number,
          date: formatDate(folder.updated_at),
          f_z: folder.folder_size ? folder.folder_size.split('')[0] : 0,
          folder_size: folder.folder_size ? folder.folder_size : 0,
          file_type: folder.file_type?.name,
          id: folder.id,
          is_sub_folder: folder.is_sub_folder,
          sub_folder: folder.is_sub_folder ? folder.subfolder.path : [],
          type: folder.type
        }));
      } else {
        return fetchedFolders.value;
      }
    });


</script>

<style></style>