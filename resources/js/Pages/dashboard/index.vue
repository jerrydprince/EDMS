<template>
  <main class="px-7">
    <div class="py-7">
      <h1 class="font-semibold text-3xl text-[#1D2939]">
        Get Started with Archiving.
      </h1>
      <p class="text-gray-500">
        Efficiently archive and organize documents, ensuring seamless retrieval
        and compliance within the system.
      </p>
    </div>

    <div class="flex justify-between">
      <div class="w-[47%] border border-solid rounded-lg">
        <div class="flex gap-5 items-center border-b px-5 py-3">
          <div class="p-2 border rounded-full">
            <Icon icon="mdi:cloud-key-outline" width="30" />
          </div>
          <h1 class="font-semibold text-2xl text-[#1D2939]">
            Archiving Steps:
          </h1>
        </div>

        <div class="px-5 pt-5 pb-7">
          <div v-for="(step, index) in steps" :key="index">
            <div class="flex items-start gap-5">
              <div class="flex flex-col items-center">
                <div class="border border-[#EBF3FE] border-solid rounded-2xl p-0.5">
                  <button class="text-white bg-primary px-4 rounded-2xl py-0.5">
                    {{ index + 1 }}
                  </button>
                </div>
                <div class="w-[1px] h-10 bg-[#EBF3FE]" v-if="index < 3"></div>
              </div>
              <div class="text-[#475467]">
                <h1 class="font-medium text-lg">{{ step.name }}:</h1>
                <p class="opacity-70">{{ step.text }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="w-[47%] border border-solid rounded-lg">
        <div class="flex gap-5 items-center border-b px-5 py-3">
          <div class="p-2 border rounded-full">
            <Icon icon="charm:circle-tick" width="30" />
          </div>
          <h1 class="font-semibold text-2xl text-[#1D2939]">
            Your pending tasks
          </h1>
        </div>

        <div class=" px-5">
          <div class="flex items-center justify-between pt-5">
            <div>
              <h1 class=" font-semibold text-4xl text-[#1D2939]">{{ Tasks.length }}</h1>
              <p class=" font-semibold opacity-60 text-[#1D2939] mt-3">Pending Tasks</p>
            </div>
            <div class=" flex flex-col ">
              <button class=" bg-slate-200 p-2 rounded-full" @click="scroll(-20)">
                <Icon icon="flowbite:angle-up-outline" />
              </button>
              <button class=" bg-slate-200 p-2 rounded-full mt-5" @click="scroll(20)">
                <Icon icon="flowbite:angle-down-solid" />
              </button>
            </div>
          </div>
          <div class=" mt-5 border rounded-lg p-5 h-44 overflow-y-auto hide_scroll " ref="scrollContainer">
            <div v-for="(task, index) in Tasks" :key="index">
              <div class=" flex items-center justify-between mb-3">
                <div class=" flex gap-5 items-center">
                  <div class=" bg-gray-300 p-2 rounded-full">
                    <Icon icon="carbon:warning-filled" width="20" class=" rotate-180 text-white" />
                  </div>
                  <p class=" w-80 opacity-80">{{ task.text }} <span class=" text-primary underline">{{ task.divsion
                  }}</span></p>
                </div>
                <button class=" text-primary" @click="resolveModal = true">Resolve</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="  flex items-center pl-5 pt-5 relative justify-between border overflow-hidden rounded-xl   my-10">
      <div class=" flex items-center gap-10">
        <img src="@/assets/images/tutorial.svg" alt="" class=" pb-5 w-80">
        <div>
          <h1 class=" text-3xl">Archiving Tutorial</h1>
          <p class=" text-[#667085] opacity-50">Watch a quick video guide to streamline your archiving process.</p>
        </div>
      </div>
      <div class=" w-72 h-80 absolute right-0 bottom-0  gradient">

      </div>
    </div>

    <div class=" mb-10">
      <div class=" flex items-center w-full justify-between">
        <h1 class=" text-3xl  text-[#1D2939] mb-3">Recent Documents</h1>
        <div class="flex items-center gap-5 text-sm" v-if="documents.length !== 0">
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
            <p v-if="!documentsGridView">Grid</p>
            <p v-else>List</p>
          </button>
        </div>
      </div>
      <div class=" border border-solid rounded-xl" v-if="documents.length === 0">
        <EmptyState />
      </div>

      <div v-else>
        <div v-if="documentsGridView">
          <div>
            <!-- <DataTable v-model:selection="selectedDocument" paginator :rows="20" :value="files" dataKey="id" :pt="{
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

              <Column field="location" header="Location" :pt="{
                bodyCell: { class: 'text-gray-500' },
              }" headerStyle="color: #667085; font-weight: 500;" style="width: 200px;">
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
                <template #body="slotProps">

                  <div id="menu1" class="menu absolute right-28 z-50"
                    v-if="menuVisible && selectedDocumentId === slotProps.data.id" ref="modalRef">
                    <div class="w-[417px] h-[420px]  relative" :class="{ ' bottom-72': isPositionChanged }"
                      @click="close">
                      <div
                        class="dropmenu w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute right-0 shadow-lg cursor-pointer">
                        <div class="flex justify-between items-center hover:bg-[#F9FAFB] px-3 my-2"
                          v-for="(item, index) in menuItem" :key="index" @click.stop @click="handleMenuItem(item)">
                          <div class="flex items-center gap-2">
                            <Icon :icon="item.icon" />
                            <p>{{ item.label }}</p>
                          </div>
                          <Icon :icon="item.arrow" />
                        </div>
                      </div>

                      <div
                        class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
                        :class="{ ' bottom-28': isSecondPosition }" v-if="submenu" @click="close">
                        <div class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2"
                          v-for="(item, index) in submenuItem" :key="index" @click="handleMenuItem(item)">
                          <Icon :icon="item.icon" />
                          <p>{{ item.label }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex gap-3 items-center">
                    <div @click="toggleMenu(slotProps.data.id)" class="p-2 border cursor-pointer rounded-md">
                      <Icon icon="simple-line-icons:options-vertical" />
                    </div>
                    <div class="cursor-pointer" @click="handleButtonClick">
                      <Icon icon="uiw:delete" width="16" />
                    </div>
                  </div>

                </template>
              </Column>
            </DataTable> -->

            <ListVue :files="files" :documentsGridView="!documentsGridView" @docs="SetselectedDocument" 
          @changeFormat="setFormatModal" @lock="setLock" @fileRename="setFileRename" @downloadFile="downloadFile"
          @selectedArchive="setSelectedArchive" @branchDetails="setBranchDetails"/>

          </div>
        </div>
        <div v-else>
          <div class="grid xl:grid-cols-4 my-4 md:grid-cols-2 grid-cols-1 gap-8" v-if="userDepartment">
            <div v-for="dept in userDepartment.archive" class="bg-gray-200 p-2 rounded-md relative" :key="i">
              <div class="flex items-center justify-between">
                <div class="p-2 rounded-full bg-white w-fit">
                  <Icon icon="material-symbols-light:folder-outline" width="20" color="blue" />
                </div>
                <h1 class="truncate">{{dept.file_path[0].file_name}}</h1>
                <div class="flex gap-3 items-center">
                  <div @click="toggleMenu(i)" class="p-2 border cursor-pointer rounded-md">
                    <Icon icon="simple-line-icons:options-vertical" class="" />
                  </div>
                </div>

                <div id="menu1" class="menu absolute right-5 z-50 " v-if="menuVisible && selectedDocumentId === i"
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

                    <div
                      class="w-52 border rounded-lg text-[#1D2939] border-gray-300 text-lg bg-white absolute bottom-0 left-0 shadow-lg cursor-pointer"
                      :class="{ ' bottom-28': isSecondPosition }" :style="{ left: horizontalPosition ? '420px' : 'auto' }"
                      v-if="submenu">
                      <div class="flex items-center gap-2 hover:bg-[#F9FAFB] px-3 my-2"
                        v-for="(item, index) in submenuItem" :key="index" @click="handleMenuItem(item)">
                        <Icon :icon="item.icon" />
                        <p>{{ item.label }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-2">
                <img class="w-full rounded-md" :src="page.props.base+'/storage'+dept.file_path[0]?.path" alt="Document image" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <BranchDetails v-model="branchDetailsModal" />
  <LockDocument v-model="LockDocumentModal" />
  <UnlockDocument v-model="UnlockDocumentModal" />
  <GenerateEncriptionCode v-model="GenerateEncriptionModal" />
  <ChangeFormat v-model="ChangeFormatModal" />
  <ResolveTask v-model="resolveModal" />
  <RenameFileDialog v-model="FileRenameModal" :archive="selectedArchive"/>
</template>
  
<script setup>
import { ref, defineProps, computed } from "vue";
import EmptyState from "../../components/EmptyState.vue";
import ResolveTask from "../../components/Modals/ResolveTask.vue";
import BranchDetails from "@/components/Modals/branches/BranchDetails.vue";
import LockDocument from "@/components/Modals/branches/LockDocument.vue";
import UnlockDocument from "@/components/Modals/branches/UnlockDocument.vue";
import GenerateEncriptionCode from "@/components/Modals/branches/GenerateEncriptionCode.vue";
import ChangeFormat from "@/components/Modals/branches/ChangeFormatModal.vue";
import { onClickOutside } from "@vueuse/core";
import { usePage } from '@inertiajs/vue3';
import ListVue from '../dashboard/partials/ListVue.vue';
import RenameFileDialog from "../../components/Modals/branches/RenameFileDialog.vue";


const documentsGridView = ref(true)
const selectedDocumentId = ref(null);
const menuVisible = ref(false);
const submenu = ref(false);
const isPositionChanged = ref(false);
const isSecondPosition = ref(false);
const modalRef = ref(null);
const resolveModal = ref(false);
const branchDetailsModal = ref(false);
const LockDocumentModal = ref(false);
const UnlockDocumentModal = ref(false);
const GenerateEncriptionModal = ref(false);
const ChangeFormatModal = ref(false);
const scrollContainer = ref(null);
const horizontalPosition = ref(null);

const FileRenameModal = ref(false);
const selectedArchiveId = ref();
const selectedArchive = ref();
const props = defineProps(['userDepartment'])
const page = usePage();

onClickOutside(modalRef, (event) => {
  (menuVisible.value = false), (submenu.value = false);
});

const close = () => {
  menuVisible.value = false,
    submenu.value = false
}

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
  isPositionChanged.value = clientYPercentage >= 45;
  horizontalPosition.value = clientXPercentage <= 75;
  isSecondPosition.value = clientYPercentage >= 83 && clientYPercentage <= 100;
};
const Tasks = [
  {
    text: "Documents awaiting archiving in the",
    divsion: "Finance Division.",
  },
  {
    text: "Documents pending archiving in the ",
    divsion: "Human Resources Unit.",
  },
  {
    text: "Documents to be archived in the ",
    divsion: "Marketing Unit.",
  },
  {
    text: "Documents awaiting archiving in the",
    divsion: "Finance Division.",
  },
  {
    text: "Documents awaiting archiving in the",
    divsion: "Finance Division.",
  },
  {
    text: "Documents awaiting archiving in the",
    divsion: "Finance Division.",
  },
];

const scroll = (offset) => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollTop += offset;
  }
};


const steps = [
  {
    name: "Select Branch",
    text: "Choose the destination branch for document archiving.",
  },
  {
    name: "Upload Documents",
    text: "Easily upload files for processing.",
  },
  {
    name: "Input Metadata and Tags",
    text: "Add essential details to enhance document organization.",
  },
  {
    name: "Finalize Upload",
    text: "Complete the process by confirming and uploading documents.",
  },
];

const documents = ref([
  {
    id: "1001",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
    location: 'Finance Department'
  },
  {
    id: "1002",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
    location: 'Finance Department'
  },
  {
    id: "1003",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
    location: 'Finance Department'
  },
  {
    id: "1004",
    name: "Communication.pdf",
    uploaded_by: "Dave Miye",
    size: "150 KB",
    date: "16 Nov 23",
    location: 'Finance Department'
  },
]);
const selectedDocument = ref([]);

const files = computed(()=>{
  let newarr = [];

  if(props.userDepartment?.archive){
    for(let file of props.userDepartment.archive){
      let arr = {
        name: file?.file_path[0]?.file_name,
        size: file?.file_path[0]?.file_size,
        date: formatDate(file.updated_at),
        created: formatDate(file.created_at),
        uploaded_by: file?.file_path[0]?.updated_by_name,
        path: file?.file_path[0]?.thumbnails,
        files: file?.file_path,
        id: file.id,
        location: page.props.userDepartment.title+" Department"
      }
      newarr.push(arr);
    }
  }
  return newarr;
})

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
</script>
  
<style scoped>
.gradient {

  background: linear-gradient(217deg, rgba(255, 255, 255, 0.8), rgba(255, 0, 0, 0) 80%),

    linear-gradient(336deg, rgba(13, 153, 255), rgba(0, 0, 255, 0) 30%);
}
</style>